<?php
namespace App\Controllers;

use App\Models\Agency;
use App\Models\Lease;
use App\Models\Owner;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\User;
use App\Utils\Auth;
use App\Utils\Logger;
use App\Utils\Helpers;
use App\Utils\Flash;
use Exception;
use PDOException;
use TCPDF;

class PaymentController
{
    protected $auth;
    protected $logger;
    protected $helpers;
    protected $flash;

    public function __construct(Auth $auth, Logger $logger, Helpers $helpers, Flash $flash)
    {
        $this->auth = $auth;
        $this->logger = $logger;
        $this->helpers = $helpers;
        $this->flash = $flash;
    }

    /**
     * Vérifie si l’utilisateur peut gérer un bail (réutilisé pour les paiements)
     * @param int $leaseId
     * @return bool
     */
    private function canManageLease($leaseId): bool
    {
        $user = $this->auth->user();
        if (!$user) {
            return false;
        }

        $lease = Lease::find($leaseId);
        if (!$lease) {
            return false;
        }

        $role = $user['role'] ?? 'guest';
        $userId = $user['id'];
        $agencyId = $user['agency_id'];

        if ($role === 'superadmin') {
            return true;
        }

        if ($role === 'admin' && $lease->getAgencyId() === $agencyId) {
            return true;
        }

        if ($role === 'agent' && $lease->getAgentId() === $userId && $lease->getAgencyId() === $agencyId) {
            return true;
        }

        if ($role === 'proprietaire') {
            $apartment = $lease->apartment();
            $owner = Owner::findByUserId($userId);
            return $apartment && $owner && $apartment->getOwnerId() === $owner->getId();
        }

        if ($role === 'locataire') {
            $tenant = Tenant::findByUserId($userId);
            return $tenant && $lease->getTenantId() === $tenant->getId();
        }

        if ($role === 'acheteur') {
            return Lease::hasLeasesForBuyer($userId);
        }

        return false;
    }

    /**
     * Affiche la liste des paiements
     */
public function index()
{
    if (!$this->auth->check()) {
        $this->flash->flash('error', 'Vous devez être connecté pour accéder à cette page.');
        $this->helpers->redirect('/auth/login');
        return;
    }

    $user = $this->auth->user();
    $role = $user['role'] ?? 'guest';
    $userId = $user['id'];
    $agencyId = $user['agency_id'];
    $search = trim($_GET['search'] ?? '');
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = 10;
    $offset = ($page - 1) * $limit;
    $selectedStatuses = isset($_GET['statuses']) && is_array($_GET['statuses']) ? $_GET['statuses'] : [];

    if (!in_array($role, ['superadmin', 'admin', 'agent'])) {
        $this->flash->flash('error', 'Accès non autorisé.');
        $this->helpers->redirect('/leases');
        return;
    }

    try {
        $payments = [];
        $totalPayments = 0;

        if ($role === 'superadmin') {
            $payments = Payment::getWithFilters($search, $limit, $offset, $selectedStatuses);
            $totalPayments = Payment::countWithFilters($search, $selectedStatuses);
        } elseif ($role === 'admin' && $agencyId) {
            $payments = Payment::findByAgencyIdWithFilters($agencyId, $search, $limit, $offset, $selectedStatuses);
            $totalPayments = Payment::countByAgencyIdWithFilters($agencyId, $search, $selectedStatuses);
        } elseif ($role === 'agent' && $agencyId) {
            $payments = Payment::findByAgentIdWithFilters($userId, $agencyId, $search, $limit, $offset, $selectedStatuses);
            $totalPayments = Payment::countByAgentIdWithFilters($userId, $agencyId, $search, $selectedStatuses);
        }

        $totalPages = ceil($totalPayments / $limit);
        $title = 'Liste des paiements';
        $csrf_token = $this->helpers->csrf_token('payments.delete');
        $content_view = 'admin/payments/index.php';
        require_once dirname(__DIR__, 1) . '/Views/layouts/admin_layout.php';
    } catch (PDOException $e) {
        $this->logger->error("Erreur lors de la récupération des paiements : " . $e->getMessage());
        $this->flash->flash('error', 'Une erreur est survenue lors du chargement des paiements.');
        $this->helpers->redirect('/leases');
    }
}

    /**
     * Affiche le formulaire de création d’un paiement
     */
public function create()
{
    if (!$this->auth->check()) {
        $this->flash->flash('error', 'Vous devez être connecté pour accéder à cette page.');
        $this->helpers->redirect('/auth/login');
        return;
    }

    $user = $this->auth->user();
    $role = $user['role'] ?? 'guest';
    $userId = $user['id'];
    $agencyId = $user['agency_id'];

    if (!in_array($role, ['superadmin', 'admin', 'agent'])) {
        $this->flash->flash('error', 'Accès non autorisé.');
        $this->helpers->redirect('/leases');
        return;
    }

    try {
        $leases = [];
        $agencies = [];
        $agents = [];

        if ($role === 'superadmin') {
            $leases = Lease::get('', 100, 0);
            $agencies = Agency::get();
            $agents = User::findByRole('agent') ?? [];
        } elseif ($role === 'admin' && $agencyId) {
            $leases = Lease::findByAgencyId($agencyId, '', 100, 0);
            $agents = User::findByRoleAndAgency('agent', $agencyId) ?? [];
        } elseif ($role === 'agent' && $agencyId) {
            $leases = Lease::findByAgentId($userId, $agencyId, '', 100, 0);
            $agents = []; // No agents needed for agent role
        }

        $form_data = $this->flash->get('form_data') ?? [];
        $csrf_token = $this->helpers->csrf_token('payments.store');
        $title = 'Enregistrer un paiement';
        $content_view = 'admin/payments/create.php';
        require_once dirname(__DIR__, 1) . '/Views/layouts/admin_layout.php';
    } catch (PDOException $e) {
        $this->logger->error("Erreur lors du chargement des données pour création de paiement : " . $e->getMessage());
        $this->flash->flash('error', 'Une erreur est survenue lors du chargement des données.');
        $this->helpers->redirect('/leases');
    }
}

    /**
     * Enregistre un nouveau paiement et génère une quittance PDF
     */
    public function store()
    {
        if (!$this->auth->check()) {
            $this->flash->flash('error', 'Vous devez être connecté pour accéder à cette page.');
            $this->helpers->redirect('/auth/login');
            return;
        }

        $user = $this->auth->user();
        $role = $user['role'] ?? 'guest';
        if (!in_array($role, ['superadmin', 'admin', 'agent'])) {
            $this->flash->flash('error', 'Accès non autorisé.');
            $this->helpers->redirect('/leases');
            return;
        }

        $data = [
            'lease_id' => (int)($_POST['lease_id'] ?? 0),
            'agent_id' => ($role === 'agent' || $role === 'admin') ? $user['id'] : (int)($_POST['agent_id'] ?? $user['id']),
            'agency_id' => ($role === 'superadmin') ? (int)($_POST['agency_id'] ?? $user['agency_id'] ?? null) : ($user['agency_id'] ?? null),
            'amount' => (float)($_POST['amount'] ?? 0),
            'payment_date' => trim($_POST['payment_date'] ?? ''),
            'due_date' => trim($_POST['due_date'] ?? ''),
            'type' => trim($_POST['type'] ?? ''),
            'mode' => trim($_POST['mode'] ?? ''),
            'status' => trim($_POST['status'] ?? 'pending'),
        ];

        $errors = [];

        if (!$data['lease_id'] || !Lease::find($data['lease_id'])) {
            $errors[] = 'Bail invalide.';
        } elseif (!$this->canManageLease($data['lease_id'])) {
            $errors[] = 'Vous n’êtes pas autorisé à enregistrer un paiement pour ce bail.';
        }
        if ($role === 'superadmin' && !$data['agency_id']) {
            $errors[] = 'Agence requise pour les superadmins.';
        } elseif ($role !== 'superadmin' && $data['agency_id'] !== $user['agency_id']) {
            $errors[] = 'Vous ne pouvez enregistrer un paiement que pour votre agence.';
        }
        if ($data['agent_id'] && !User::find($data['agent_id'])) {
            $errors[] = 'Agent invalide.';
        }
        if ($data['amount'] <= 0) {
            $errors[] = 'Le montant doit être positif.';
        }
        if (empty($data['payment_date']) || !strtotime($data['payment_date'])) {
            $errors[] = 'Date de paiement invalide.';
        }
        if (empty($data['due_date']) || !strtotime($data['due_date'])) {
            $errors[] = 'Date d’échéance invalide.';
        }
        $validTypes = ['loyer', 'charges', 'dépôt', 'autre'];
        if (!in_array($data['type'], $validTypes)) {
            $errors[] = 'Type de paiement invalide.';
        }
        $validModes = ['cash', 'mobile', 'carte'];
        if (!in_array($data['mode'], $validModes)) {
            $errors[] = 'Mode de paiement invalide.';
        }
        $validStatuses = ['en_attente', 'payer', 'en_retard', 'annulé'];
        if (!in_array($data['status'], $validStatuses)) {
            $errors[] = 'Statut de paiement invalide.';
        }

        if (!empty($errors)) {
            $this->flash->flash('error', implode('<br>', $errors));
            $this->flash->flash('form_data', implode(', ', $data));
            $this->helpers->redirect('/payments/create');
            return;
        }

        try {
            // Créer le paiement
            $payment = Payment::create($data);

            // Générer la quittance PDF
            $quittancePath = $this->generateQuittancePDF($payment);
            if ($quittancePath) {
                Payment::update($payment->getId(), ['quittance_path' => $quittancePath]);
            }

            $this->flash->flash('success', 'Paiement enregistré avec succès.');
            $this->helpers->redirect('/payments');
        } catch (PDOException $e) {
            $this->logger->error("Erreur lors de l’enregistrement du paiement : " . $e->getMessage());
            $this->flash->flash('error', 'Une erreur est survenue lors de l’enregistrement du paiement.');
            $this->flash->flash('form_data', implode(', ', $data));
            $this->helpers->redirect('/payments/create');
        }
    }

    /**
     * Affiche le formulaire de modification d’un paiement
     * @param int $paymentId
     */
public function edit($paymentId)
{
    if (!$this->auth->check()) {
        $this->flash->flash('error', 'Vous devez être connecté pour accéder à cette page.');
        $this->helpers->redirect('/auth/login');
        return;
    }

    $user = $this->auth->user();
    $role = $user['role'] ?? 'guest';
    $userId = $user['id'];
    $agencyId = $user['agency_id'];

    if (!in_array($role, ['superadmin', 'admin', 'agent'])) {
        $this->flash->flash('error', 'Accès non autorisé.');
        $this->helpers->redirect('/leases');
        return;
    }

    try {
        $payment = Payment::find($paymentId);
        if (!$payment) {
            $this->flash->flash('error', 'Paiement introuvable.');
            $this->helpers->redirect('/payments');
            return;
        }

        if (!$this->canManageLease($payment->getLeaseId())) {
            $this->flash->flash('error', 'Accès non autorisé à ce paiement.');
            $this->helpers->redirect('/payments');
            return;
        }

        $leases = [];
        $agencies = [];
        $agents = [];

        if ($role === 'superadmin') {
            $leases = Lease::get('', 100, 0);
            $agencies = Agency::get();
            $agents = User::findByRole('agent') ?? [];
        } elseif ($role === 'admin' && $agencyId) {
            $leases = Lease::findByAgencyId($agencyId, '', 100, 0);
            $agents = User::findByRoleAndAgency('agent', $agencyId) ?? [];
        } elseif ($role === 'agent' && $agencyId) {
            $leases = Lease::findByAgentId($userId, $agencyId, '', 100, 0);
            $agents = [];
        }

        $form_data = $this->flash->get('form_data') ?? [];
        $csrf_token = $this->helpers->csrf_token('payments.update');
        $title = 'Modifier un paiement';
        $content_view = 'admin/payments/edit.php';
        require_once dirname(__DIR__, 1) . '/Views/layouts/admin_layout.php';
    } catch (PDOException $e) {
        $this->logger->error("Erreur lors du chargement des données pour modification du paiement : " . $e->getMessage());
        $this->flash->flash('error', 'Une erreur est survenue lors du chargement des données.');
        $this->helpers->redirect('/payments');
    }
}

    /**
     * Met à jour un paiement existant
     * @param int $paymentId
     */
    public function update($paymentId)
    {
        if (!$this->auth->check()) {
            $this->flash->flash('error', 'Vous devez être connecté pour accéder à cette page.');
            $this->helpers->redirect('/auth/login');
            return;
        }

        $user = $this->auth->user();
        $role = $user['role'] ?? 'guest';
        if (!in_array($role, ['superadmin', 'admin', 'agent'])) {
            $this->flash->flash('error', 'Accès non autorisé.');
            $this->helpers->redirect('/leases');
            return;
        }

        $payment = Payment::find($paymentId);
        if (!$payment) {
            $this->flash->flash('error', 'Paiement introuvable.');
            $this->helpers->redirect('/payments');
            return;
        }

        if (!$this->canManageLease($payment->getLeaseId())) {
            $this->flash->flash('error', 'Accès non autorisé à ce paiement.');
            $this->helpers->redirect('/payments');
            return;
        }

        $data = [
            'lease_id' => (int)($_POST['lease_id'] ?? $payment->getLeaseId()),
            'agent_id' => ($role === 'agent' || $role === 'admin') ? $user['id'] : (int)($_POST['agent_id'] ?? $payment->getAgentId() ?? $user['id']),
            'agency_id' => ($role === 'superadmin') ? (int)($_POST['agency_id'] ?? $payment->getAgencyId() ?? $user['agency_id'] ?? null) : ($user['agency_id'] ?? $payment->getAgencyId()),
            'amount' => (float)($_POST['amount'] ?? $payment->getAmount()),
            'payment_date' => trim($_POST['payment_date'] ?? $payment->getPaymentDate()),
            'due_date' => trim($_POST['due_date'] ?? $payment->getDueDate()),
            'type' => trim($_POST['type'] ?? $payment->getType()),
            'mode' => trim($_POST['mode'] ?? $payment->getMode()),
            'status' => trim($_POST['status'] ?? $payment->getStatus()),
        ];

        $errors = [];

        if (!$data['lease_id'] || !Lease::find($data['lease_id'])) {
            $errors[] = 'Bail invalide.';
        } elseif (!$this->canManageLease($data['lease_id'])) {
            $errors[] = 'Vous n’êtes pas autorisé à modifier ce paiement.';
        }
        if ($role === 'superadmin' && !$data['agency_id']) {
            $errors[] = 'Agence requise pour les superadmins.';
        } elseif ($role !== 'superadmin' && $data['agency_id'] !== $user['agency_id']) {
            $errors[] = 'Vous ne pouvez modifier un paiement que pour votre agence.';
        }
        if ($data['agent_id'] && !User::find($data['agent_id'])) {
            $errors[] = 'Agent invalide.';
        }
        if ($data['amount'] <= 0) {
            $errors[] = 'Le montant doit être positif.';
        }
        if (empty($data['payment_date']) || !strtotime($data['payment_date'])) {
            $errors[] = 'Date de paiement invalide.';
        }
        if (empty($data['due_date']) || !strtotime($data['due_date'])) {
            $errors[] = 'Date d’échéance invalide.';
        }
        $validTypes = ['rent', 'charges', 'deposit'];
        if (!in_array($data['type'], $validTypes)) {
            $errors[] = 'Type de paiement invalide.';
        }
        $validModes = ['cash', 'mobile', 'carte'];
        if (!in_array($data['mode'], $validModes)) {
            $errors[] = 'Mode de paiement invalide.';
        }
        $validStatuses = ['pending', 'paid', 'late', 'canceled'];
        if (!in_array($data['status'], $validStatuses)) {
            $errors[] = 'Statut de paiement invalide.';
        }

        if (!empty($errors)) {
            $this->flash->flash('error', implode('<br>', $errors));
            $this->flash->flash('form_data', implode(', ', $data));
            $this->helpers->redirect("/payments/edit/$paymentId");
            return;
        }

        try {
            // Mettre à jour le paiement
            Payment::update($paymentId, $data);

            // Régénérer la quittance PDF si nécessaire
            $quittancePath = $this->generateQuittancePDF($payment);
            if ($quittancePath) {
                Payment::update($paymentId, ['quittance_path' => $quittancePath]);
            }

            $this->flash->flash('success', 'Paiement modifié avec succès.');
            $this->helpers->redirect('/payments');
        } catch (PDOException $e) {
            $this->logger->error("Erreur lors de la modification du paiement ID $paymentId : " . $e->getMessage());
            $this->flash->flash('error', 'Une erreur est survenue lors de la modification du paiement.');
            $this->flash->flash('form_data', implode(', ', $data));
            $this->helpers->redirect("/payments/edit/$paymentId");
        }
    }

    /**
     * Supprime un paiement
     * @param int $paymentId
     */
    public function delete($paymentId)
    {
        if (!$this->auth->check()) {
            $this->flash->flash('error', 'Vous devez être connecté pour accéder à cette page.');
            $this->helpers->redirect('/auth/login');
            return;
        }

        $user = $this->auth->user();
        $role = $user['role'] ?? 'guest';
        if (!in_array($role, ['superadmin', 'admin', 'agent'])) {
            $this->flash->flash('error', 'Accès non autorisé.');
            $this->helpers->redirect('/leases');
            return;
        }

        try {
            $payment = Payment::find($paymentId);
            if (!$payment) {
                $this->flash->flash('error', 'Paiement introuvable.');
                $this->helpers->redirect('/payments');
                return;
            }

            if (!$this->canManageLease($payment->getLeaseId())) {
                $this->flash->flash('error', 'Accès non autorisé à ce paiement.');
                $this->helpers->redirect('/payments');
                return;
            }

            if (Payment::delete($paymentId)) {
                $this->flash->flash('success', 'Paiement supprimé avec succès.');
            } else {
                $this->flash->flash('error', 'Impossible de supprimer le paiement.');
            }
            $this->helpers->redirect('/payments');
        } catch (PDOException $e) {
            $this->logger->error("Erreur lors de la suppression du paiement ID $paymentId : " . $e->getMessage());
            $this->flash->flash('error', 'Une erreur est survenue lors de la suppression du paiement.');
            $this->helpers->redirect('/payments');
        }
    }

    /**
     * Génère un PDF de quittance pour un paiement
     * @param Payment $payment
     * @return string|null Chemin du fichier PDF généré
     */
    private function generateQuittancePDF(Payment $payment)
    {
        try {
            $lease = $payment->lease();
            $tenant = $lease ? $lease->tenant() : null;
            $apartment = $lease ? $lease->apartment() : null;
            $agency = $payment->getAgencyId() ? Agency::find($payment->getAgencyId()) : null;
            $agent = $payment->getAgentId() ? User::find($payment->getAgentId()) : null;

            $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

            $pdf->SetCreator(PDF_CREATOR);
            $pdf->SetAuthor('ImmoApp');
            $pdf->SetTitle('Quittance de Paiement #' . $payment->getId());
            $pdf->SetSubject('Quittance de paiement');
            $pdf->SetKeywords('quittance, paiement, immobilier');

            $pdf->SetMargins(15, 30, 15);
            $pdf->SetHeaderMargin(10);
            $pdf->SetFooterMargin(10);

            $headerText = $agency ? htmlspecialchars($agency->getName()) . "\n" .
                htmlspecialchars($agency->getAddress() ?? 'Adresse non définie') . "\n" .
                htmlspecialchars($agency->getPhone() ?? 'Téléphone non défini') . "\n" .
                htmlspecialchars($agency->getEmail() ?? 'Email non défini') . "\n" : 'Agence non définie' . "\n";
            $pdf->SetHeaderData('', 0, 'ImmoApp', $headerText);
            $pdf->setHeaderFont(['dejavusans', '', 10]);
            $pdf->setFooterFont(['dejavusans', '', 8]);

            $pdf->SetAutoPageBreak(TRUE, 15);
            $pdf->SetFont('dejavusans', '', 11);
            $pdf->AddPage();

            $html = '
            <style>
                h1 { color: #1F2937; text-align: center; font-size: 20pt; margin-bottom: 10px; }
                h2 { color: #1F2937; text-align: center; font-size: 16pt; margin-bottom: 20px; }
                h3 { color: #FBBF24; background-color: #1F2937; padding: 5px; font-size: 14pt; margin-top: 20px; }
                table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                th, td { border: 1px solid #6B7280; padding: 8px; font-size: 10pt; }
                th { background-color: #FBBF24; color: #1F2937; font-weight: bold; }
                tr:nth-child(even) { background-color: #F9FAFB; }
                .signature { margin-top: 40px; font-size: 10pt; color: #3B82F6; }
                .footer-note { font-size: 8pt; color: #6B7280; text-align: center; margin-top: 20px; }
            </style>
            <h1>Quittance de Paiement</h1>
            <h2>Paiement #' . htmlspecialchars($payment->getId()) . '</h2>
            <p style="text-align: right; font-size: 10pt; color: #6B7280;">Généré le ' . date('d/m/Y H:i') . '</p>
            <h3>Détails du Paiement</h3>
            <table>
                <tr>
                    <th>ID</th>
                    <td>' . htmlspecialchars($payment->getId()) . '</td>
                </tr>
                <tr>
                    <th>Montant</th>
                    <td>' . number_format($payment->getAmount(), 2) . ' €</td>
                </tr>
                <tr>
                    <th>Date de paiement</th>
                    <td>' . date('d/m/Y', strtotime($payment->getPaymentDate())) . '</td>
                </tr>
                <tr>
                    <th>Date d’échéance</th>
                    <td>' . date('d/m/Y', strtotime($payment->getDueDate())) . '</td>
                </tr>
                <tr>
                    <th>Type</th>
                    <td>' . htmlspecialchars(ucfirst($payment->getType())) . '</td>
                </tr>
                <tr>
                    <th>Mode</th>
                    <td>' . htmlspecialchars(ucfirst($payment->getMode())) . '</td>
                </tr>
                <tr>
                    <th>Statut</th>
                    <td>' . htmlspecialchars(ucfirst($payment->getStatus())) . '</td>
                </tr>
            </table>
            <h3>Informations Associées</h3>
            <table>
                <tr>
                    <th>Bail</th>
                    <td>' . ($lease ? 'Bail #' . htmlspecialchars($lease->getId()) : 'Non défini') . '</td>
                </tr>
                <tr>
                    <th>Locataire</th>
                    <td>' . ($tenant ? htmlspecialchars($tenant->user()->getFirstName() . ' ' . $tenant->user()->getLastName()) : 'Non défini') . '</td>
                </tr>
                <tr>
                    <th>Appartement</th>
                    <td>' . ($apartment ? htmlspecialchars($apartment->getNumber() . ' (Bâtiment: ' . $apartment->building()->getName() . ')') : 'Non défini') . '</td>
                </tr>';

            if ($agency) {
                $html .= '
                <tr>
                    <th>Agence</th>
                    <td>' . htmlspecialchars($agency->getName()) . '</td>
                </tr>';
            }
            if ($agent) {
                $html .= '
                <tr>
                    <th>Agent</th>
                    <td>' . htmlspecialchars($agent->getFirstName() . ' ' . $agent->getLastName()) . '</td>
                </tr>';
            }

            $html .= '
            </table>
            <div class="signature">
                <p>Signature de l\'agence : _____________________________</p>
            </div>
            <div class="footer-note">
                <p>Document généré par ImmoApp - Tous droits réservés</p>
            </div>';

            $pdf->writeHTML($html, true, false, true, false, '');

            $quittanceDir = dirname(__DIR__, 2) . '/public/storage/quittances/';
            if (!is_dir($quittanceDir)) {
                mkdir($quittanceDir, 0755, true);
            }
            $quittancePath = 'quittance_' . $payment->getId() . '_' . time() . '.pdf';
            $fullPath = $quittanceDir . $quittancePath;

            $pdf->Output($fullPath, 'F');

            return $quittancePath;
        } catch (Exception $e) {
            $this->logger->error("Erreur lors de la génération de la quittance PDF pour le paiement ID {$payment->getId()} : " . $e->getMessage());
            return null;
        }
    }

    /**
     * Télécharge la quittance en PDF
     * @param int $paymentId
     */
    public function downloadQuittance($paymentId)
    {
        if (!$this->auth->check()) {
            $this->flash->flash('error', 'Vous devez être connecté pour accéder à cette page.');
            $this->helpers->redirect('/auth/login');
            return;
        }

        $user = $this->auth->user();
        $role = $user['role'] ?? 'guest';
        if (!in_array($role, ['superadmin', 'admin', 'agent', 'locataire', 'acheteur'])) {
            $this->flash->flash('error', 'Accès non autorisé.');
            $this->helpers->redirect('/leases');
            return;
        }

        try {
            $payment = Payment::find($paymentId);
            if (!$payment) {
                $this->flash->flash('error', 'Paiement introuvable.');
                $this->helpers->redirect('/leases');
                return;
            }

            if (!$this->canManageLease($payment->getLeaseId())) {
                $this->flash->flash('error', 'Accès non autorisé à cette quittance.');
                $this->helpers->redirect('/leases');
                return;
            }

            $quittancePath = $payment->getQuittancePath();
            $fullPath = dirname(__DIR__, 2) . '/public/storage/quittances/' . $quittancePath;

            if (!$quittancePath || !file_exists($fullPath)) {
                // Régénérer la quittance si elle n'existe pas
                $quittancePath = $this->generateQuittancePDF($payment);
                if ($quittancePath) {
                    Payment::update($payment->getId(), ['quittance_path' => $quittancePath]);
                    $fullPath = dirname(__DIR__, 2) . '/public/storage/quittances/' . $quittancePath;
                } else {
                    $this->flash->flash('error', 'Impossible de générer la quittance.');
                    $this->helpers->redirect('/leases');
                    return;
                }
            }

            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . basename($quittancePath) . '"');
            readfile($fullPath);
            exit;
        } catch (PDOException $e) {
            $this->logger->error("Erreur lors du téléchargement de la quittance ID $paymentId : " . $e->getMessage());
            $this->flash->flash('error', 'Une erreur est survenue lors du téléchargement de la quittance.');
            $this->helpers->redirect('/leases');
        }
    }
}