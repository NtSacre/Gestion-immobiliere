<?php
namespace App\Controllers;

use App\Models\Agency;
use App\Models\Lease;
use App\Models\Apartment;
use App\Models\Owner;
use App\Models\Tenant;
use App\Models\Payment;
use App\Models\User;
use App\Utils\Audit;
use App\Utils\Auth;
use App\Utils\Logger;
use App\Utils\Helpers;
use App\Utils\Flash;
use Exception;
use PDOException;
use TCPDF;

class LeaseController
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
     * Vérifie si l’utilisateur peut gérer un bail
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
     * Affiche la liste des baux
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

        $leases = [];
        $totalLeases = 0;

        try {
            if ($role === 'superadmin') {
                $leases = Lease::get($search, $limit, $offset);
                $totalLeases = Lease::count($search);
            } elseif ($role === 'admin' && $agencyId) {
                $leases = Lease::findByAgencyId($agencyId, $search, $limit, $offset);
                $totalLeases = Lease::countByAgency($agencyId, $search);
            } elseif ($role === 'agent' && $agencyId) {
                $leases = Lease::findByAgentId($userId, $agencyId, $search, $limit, $offset);
                $totalLeases = Lease::countByAgency($agencyId, $search);
            } elseif ($role === 'proprietaire') {
                $owner = Owner::findByUserId($userId);
                if ($owner) {
                    $leases = Lease::findByOwnerId($owner->getId(), $search, $limit, $offset);
                    $totalLeases = Lease::countByOwner($owner->getId(), $search);
                }
            } elseif ($role === 'locataire') {
                $tenant = Tenant::findByUserId($userId);
                if ($tenant) {
                    $leases = Lease::findByTenantId($tenant->getId());
                    $totalLeases = Lease::countActiveByTenant($tenant->getId());
                }
            } elseif ($role === 'acheteur' && Lease::hasLeasesForBuyer($userId)) {
                $tenant = Tenant::findByUserId($userId);
                if ($tenant) {
                    $leases = Lease::findByTenantId($tenant->getId());
                    $totalLeases = Lease::countActiveByTenant($tenant->getId());
                }
            } else {
                $this->flash->flash('error', 'Rôle non autorisé.');
                $this->helpers->redirect('/dashboard');
                return;
            }

            $totalPages = ceil($totalLeases / $limit);
            $title = 'Liste des baux';
            $content_view = 'admin/leases/index.php';
            require_once dirname(__DIR__, 1) . '/Views/layouts/admin_layout.php';
        } catch (PDOException $e) {
            $this->logger->error("Erreur lors de la récupération des baux : " . $e->getMessage());
            $this->flash->flash('error', 'Une erreur est survenue lors du chargement des baux.');
            $this->helpers->redirect('/leases');
        }
    }

    /**
     * Affiche le formulaire de création d’un bail
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
        if (!in_array($role, ['superadmin', 'admin', 'agent'])) {
            $this->flash->flash('error', 'Accès non autorisé.');
            $this->helpers->redirect('/leases');
            return;
        }

        try {
            $apartments = [];
            if ($role === 'superadmin') {
                $apartments = Apartment::getAll('', 100, 0, ['available']);
            } elseif ($role === 'admin' || $role === 'agent') {
                $apartments = Apartment::findByAgencyId($user['agency_id'], '', 100, 0, ['disponible']);
            }

            $tenants = Tenant::findAvailable();
            $agencies = $role === 'superadmin' ? Agency::all() : [];
            $agents = $role === 'superadmin' ? User::findByRole('agent') : [];
            $form_data = $this->flash->get('form_data') ?? [];
            $csrf_token = $this->helpers->csrf_token('leases.store');
            $title = 'Créer un bail';
            $content_view = 'admin/leases/create.php';
            require_once dirname(__DIR__, 1) . '/Views/layouts/admin_layout.php';
        } catch (PDOException $e) {
            $this->logger->error("Erreur lors du chargement des données : " . $e->getMessage());
            $this->flash->flash('error', 'Une erreur est survenue lors du chargement des données.');
            $this->helpers->redirect('/leases');
        }
    }

    /**
     * Enregistre un nouveau bail
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

        $apartmentId = (int)($_POST['apartment_id'] ?? null);
        $tenantId = (int)($_POST['tenant_id'] ?? null);

        $agencyId = match ($role) {
            'agent', 'admin' => $user['agency_id'] ?? null,
            'superadmin'     => $_POST['agency_id'] ?? ($user['agency_id'] ?? null),
            default          => null,
        };

        $agentId = match ($role) {
            'agent'          => $user['id'],
            'admin', 'superadmin' => $_POST['agent_id'] != null ? $_POST['agent_id'] : $user['id'],
            default          => null,
        };

        $data = [
            'apartment_id'      => $apartmentId,
            'tenant_id'         => $tenantId,
            'agency_id'         => $agencyId,
            'agent_id'          => $agentId,
            'start_date'        => trim($_POST['start_date'] ?? ''),
            'end_date'          => trim($_POST['end_date'] ?? ''),
            'rent_amount'       => isset($_POST['rent_amount']) ? (float)$_POST['rent_amount'] : null,
            'charges_amount'    => isset($_POST['charges_amount']) ? (float)$_POST['charges_amount'] : null,
            'deposit_amount'    => isset($_POST['deposit_amount']) ? (float)$_POST['deposit_amount'] : null,
            'payment_frequency' => strtolower(trim($_POST['payment_frequency']) ?? 'mensuel'),
            'is_active'         => isset($_POST['is_active']) ? 1 : 0,
        ];

        $tenant = Tenant::find($data['tenant_id']);
        $apartment = Apartment::find($data['apartment_id']);
        $agents = \App\Models\User::getAll('', 100, 0, ['agent']);
        $errors = [];

        if (!$data['apartment_id'] || !$apartment) {
            $errors[] = 'L’appartement sélectionné est invalide.';
        }
        if (!$data['tenant_id'] || !$tenant) {
            $errors[] = 'Le locataire sélectionné est invalide.';
        }
        if ($role === 'superadmin' && (!$data['agency_id'] || !\App\Models\Agency::find($data['agency_id']))) {
            $errors[] = 'L’agence sélectionnée est invalide.';
        }
        if ($role !== 'superadmin' && (!isset($user['agency_id']) || !\App\Models\Agency::find($user['agency_id']))) {
            $errors[] = 'Votre compte n’est pas associé à une agence valide.';
        }
        if ($role !== 'superadmin' && $data['agency_id'] !== $user['agency_id']) {
            $errors[] = 'Vous ne pouvez associer ce bail qu’à votre agence.';
        }
        if ($data['agent_id'] === null || !\App\Models\User::find($data['agent_id'])) {
            $errors[] = 'L’agent sélectionné est invalide.';
        }
        if (empty($data['start_date'])) {
            $errors[] = 'La date de début est obligatoire.';
        }
        if (!empty($data['end_date']) && strtotime($data['end_date']) < strtotime($data['start_date'])) {
            $errors[] = 'La date de fin ne peut pas être antérieure à la date de début.';
        }
        if (empty($data['rent_amount']) || $data['rent_amount'] <= 0) {
            $errors[] = 'Le loyer doit être positif.';
        } elseif ($apartment && $data['rent_amount'] != $apartment->getRentAmount()) {
            $errors[] = 'Attention : le loyer saisi (' . $data['rent_amount'] . '€) diffère du loyer de l’appartement (' . $apartment->getRentAmount() . '€).';
        }
        if (empty($data['charges_amount']) || $data['charges_amount'] < 0) {
            $errors[] = 'Les charges ne peuvent pas être négatives.';
        } elseif ($apartment && $data['charges_amount'] != $apartment->getChargesAmount()) {
            $errors[] = 'Attention : les charges saisies (' . $data['charges_amount'] . '€) diffèrent des charges de l’appartement (' . $apartment->getChargesAmount() . '€).';
        }
        if (empty($data['deposit_amount']) || $data['deposit_amount'] < 0) {
            $errors[] = 'Le dépôt ne peut pas être négatif.';
        }
        if (!in_array($data['payment_frequency'], ['mensuel', 'trimestriel'])) {
            $errors[] = 'La fréquence de paiement est invalide.';
        }
        if ($tenant && $tenant->hasActiveLease()) {
            $errors[] = 'Ce locataire a déjà un bail actif.';
        }
      

        if (!empty($errors)) {
            $this->flash->flash('error', implode('<br>', $errors));
            $this->flash->flash('form_data', implode('', $data));
            $this->helpers->redirect('/leases/create');
            return;
        }

        try {
           $leases= Lease::create($data);
            // Audit de la création du bail
            Audit::log('create', 'leases', $leases->getId(), $data['agency_id'], null, $data);
            $this->flash->flash('success', 'Bail créé avec succès.');
            $this->helpers->redirect('/leases');
        } catch (PDOException $e) {
            $this->logger->error("Erreur lors de la création du bail : " . $e->getMessage());
            $this->flash->flash('error', 'Une erreur est survenue lors de la création du bail.');
            $this->flash->flash('form_data', implode('', $data));
            $this->helpers->redirect('/leases/create');
        }
    }

    /**
     * Affiche les détails d’un bail
     * @param int $id
     */
    public function show($id)
    {
        if (!$this->auth->check()) {
            $this->flash->flash('error', 'Vous devez être connecté pour accéder à cette page.');
            $this->helpers->redirect('/auth/login');
            return;
        }
        $user = $this->auth->user();

        if (!$this->canManageLease($id)) {
            $this->flash->flash('error', 'Accès non autorisé.');
            $this->helpers->redirect('/leases');
            return;
        }

        try {
            $lease = Lease::find($id);
            if (!$lease) {
                $this->flash->flash('error', 'Bail introuvable.');
                $this->helpers->redirect('/leases');
                return;
            }

            $apartment = $lease->apartment();
            $tenant = $lease->tenant();
            $payments = Lease::getPaymentsByLease($id);
            $csrf_token = $this->helpers->csrf_token('leases.delete');
            $title = 'Détails du bail';
            $content_view = 'admin/leases/show.php';
            require_once dirname(__DIR__, 1) . '/Views/layouts/admin_layout.php';
        } catch (PDOException $e) {
            $this->logger->error("Erreur lors du chargement du bail : " . $e->getMessage());
            $this->flash->flash('error', 'Une erreur est survenue lors du chargement du bail.');
            $this->helpers->redirect('/leases');
        }
    }

    /**
     * Affiche le formulaire d’édition d’un bail
     * @param int $id
     */
    public function edit($id)
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
            $lease = Lease::find($id);
            if (!$lease) {
                $this->flash->flash('error', 'Bail introuvable.');
                $this->helpers->redirect('/leases');
                return;
            }

            if ($role === 'agent' && $lease->getAgentId() !== $user['id']) {
                $this->flash->flash('error', 'Vous ne pouvez modifier que vos propres baux.');
                $this->helpers->redirect('/leases');
                return;
            }

            $apartments = Apartment::getAll('', 100, 0, ['disponible']);
            $tenants = Tenant::get();
            $agencies = \App\Models\Agency::get();
            $agents = \App\Models\User::getAll('', 100, 0, ['agent']);
            $form_data = $this->flash->get('form_data') ?? [];
            $csrf_token = $this->helpers->csrf_token('leases.update');
            $title = 'Modifier un bail';
            $content_view = 'admin/leases/edit.php';
            require_once dirname(__DIR__, 1) . '/Views/layouts/admin_layout.php';
        } catch (PDOException $e) {
            $this->logger->error("Erreur lors du chargement des données : " . $e->getMessage());
            $this->flash->flash('error', 'Une erreur est survenue lors du chargement des données.');
            $this->helpers->redirect('/leases');
        }
    }

    /**
     * Met à jour un bail
     * @param int $id
     */
    public function update($id)
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

        $lease = Lease::find($id);
        if (!$lease) {
            $this->flash->flash('error', 'Bail introuvable.');
            $this->helpers->redirect('/leases');
            return;
        }

        if ($role === 'agent' && $lease->getAgentId() !== $user['id']) {
            $this->flash->flash('error', 'Vous ne pouvez modifier que vos propres baux.');
            $this->helpers->redirect('/leases');
            return;
        }

        $resolvedAgentId = match ($role) {
            'agent' => $user['id'],
            'admin', 'superadmin' => isset($_POST['agent_id']) && !empty($_POST['agent_id'])
                ? (int)$_POST['agent_id']
                : $user['id'],
            default => null
        };

        $resolvedAgencyId = match ($role) {
            'superadmin' => isset($_POST['agency_id']) && !empty($_POST['agency_id'])
                ? (int)$_POST['agency_id']
                : ($user['agency_id'] ?? null),
            'admin', 'agent' => $user['agency_id'] ?? null,
            default => null
        };

        $data = [
            'apartment_id' => (int)($_POST['apartment_id'] ?? 0),
            'tenant_id' => (int)($_POST['tenant_id'] ?? 0),
            'agent_id' => $resolvedAgentId,
            'agency_id' => $resolvedAgencyId,
            'start_date' => trim($_POST['start_date'] ?? ''),
            'end_date' => trim($_POST['end_date'] ?? ''),
            'rent_amount' => !empty($_POST['rent_amount']) ? (float)$_POST['rent_amount'] : null,
            'charges_amount' => !empty($_POST['charges_amount']) ? (float)$_POST['charges_amount'] : null,
            'deposit_amount' => !empty($_POST['deposit_amount']) ? (float)$_POST['deposit_amount'] : null,
            'payment_frequency' => trim($_POST['payment_frequency'] ?? $lease->getPaymentFrequency()),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
        ];

        $tenant = Tenant::find($data['tenant_id']);
        $apartment = Apartment::find($data['apartment_id']);
        $agents = \App\Models\User::getAll('', 100, 0, ['agent']);
        $errors = [];

        if (!$data['apartment_id'] || !$apartment) {
            $errors[] = 'L’appartement sélectionné est invalide.';
        }
        if (!$data['tenant_id'] || !$tenant) {
            $errors[] = 'Le locataire sélectionné est invalide.';
        }
        if ($role === 'superadmin' && (!$data['agency_id'] || !\App\Models\Agency::find($data['agency_id']))) {
            $errors[] = 'L’agence sélectionnée est invalide.';
        }
        if ($role !== 'superadmin' && (!isset($user['agency_id']) || !\App\Models\Agency::find($user['agency_id']))) {
            $errors[] = 'Votre compte n’est pas associé à une agence valide.';
        }
        if ($role !== 'superadmin' && $data['agency_id'] !== $user['agency_id']) {
            $errors[] = 'Vous ne pouvez associer ce bail qu’à votre agence.';
        }
        if ($data['agent_id'] === null || !\App\Models\User::find($data['agent_id'])) {
            $errors[] = 'L’agent sélectionné est invalide.';
        }
        if (empty($data['start_date'])) {
            $errors[] = 'La date de début est obligatoire.';
        }
        if (!empty($data['end_date']) && strtotime($data['end_date']) < strtotime($data['start_date'])) {
            $errors[] = 'La date de fin ne peut pas être antérieure à la date de début.';
        }
        if (empty($data['rent_amount']) || $data['rent_amount'] <= 0) {
            $errors[] = 'Le loyer doit être positif.';
        } elseif ($apartment && $data['rent_amount'] != $apartment->getRentAmount()) {
            $errors[] = 'Attention : le loyer saisi (' . $data['rent_amount'] . '€) diffère du loyer de l’appartement (' . $apartment->getRentAmount() . '€).';
        }
        if (empty($data['charges_amount']) || $data['charges_amount'] < 0) {
            $errors[] = 'Les charges ne peuvent pas être négatives.';
        } elseif ($apartment && $data['charges_amount'] != $apartment->getChargesAmount()) {
            $errors[] = 'Attention : les charges saisies (' . $data['charges_amount'] . '€) diffèrent des charges de l’appartement (' . $apartment->getChargesAmount() . '€).';
        }
        if (empty($data['deposit_amount']) || $data['deposit_amount'] < 0) {
            $errors[] = 'Le dépôt ne peut pas être négatif.';
        }
        if (!in_array($data['payment_frequency'], ['monthly', 'quarterly'])) {
            $errors[] = 'La fréquence de paiement est invalide.';
        }
        if ($tenant && $tenant->hasActiveLease() && $data['tenant_id'] !== $lease->getTenantId()) {
            $errors[] = 'Ce locataire a déjà un bail actif.';
        }

        if (!empty($errors)) {
            $this->flash->flash('error', implode('<br>', $errors));
            $this->flash->flash('form_data', implode('', $data));
            $this->helpers->redirect("/leases/edit/$id");
            return;
        }

        try {
            Lease::update($id, $data);
            // Audit de la mise à jour du bail
            Audit::log('update', 'leases', $id, $data['agency_id'], $lease->toArray(), $data);
            $this->flash->flash('success', 'Bail mis à jour avec succès.');
            $this->helpers->redirect('/leases');
        } catch (PDOException $e) {
            $this->logger->error("Erreur lors de la mise à jour du bail ID $id : " . $e->getMessage());
            $this->flash->flash('error', 'Une erreur est survenue lors de la mise à jour du bail.');
            $this->flash->flash('form_data', implode('', $data));
            $this->helpers->redirect("/leases/edit/$id");
        }
    }

    /**
     * Supprime un bail
     * @param int $id
     */
    public function destroy($id)
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

        $lease = Lease::find($id);
        if (!$lease) {
            $this->flash->flash('error', 'Bail introuvable.');
            $this->helpers->redirect('/leases');
            return;
        }

        try {
            Lease::delete($id);
            $this->flash->flash('success', 'Bail supprimé avec succès.');
            $this->helpers->redirect('/leases');
        } catch (PDOException $e) {
            $this->logger->error("Erreur lors de la suppression du bail ID $id : " . $e->getMessage());
            $this->flash->flash('error', 'Une erreur est survenue lors de la suppression du bail.');
            $this->helpers->redirect('/leases');
        }
    }

    /**
     * Télécharge une quittance
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
            if (!$payment || !$payment->getQuittancePath()) {
                $this->flash->flash('error', 'Quittance introuvable.');
                $this->helpers->redirect('/leases');
                return;
            }

            if (!$this->canManageLease($payment->getLeaseId())) {
                $this->flash->flash('error', 'Accès non autorisé à cette quittance.');
                $this->helpers->redirect('/leases');
                return;
            }

            $filePath = dirname(__DIR__, 2) . '/public/storage/quittances/' . $payment->getQuittancePath();
            if (file_exists($filePath)) {
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . basename($payment->getQuittancePath()) . '"');
                readfile($filePath);
                exit;
            } else {
                $this->flash->flash('error', 'Fichier de quittance introuvable.');
                $this->helpers->redirect('/leases');
            }
        } catch (PDOException $e) {
            $this->logger->error("Erreur lors du téléchargement de la quittance ID $paymentId : " . $e->getMessage());
            $this->flash->flash('error', 'Une erreur est survenue lors du téléchargement de la quittance.');
            $this->helpers->redirect('/leases');
        }
    }

    /**
     * Télécharge le bail en PDF
     * @param int $id
     */
/**
 * Télécharge le bail en PDF
 * @param int $id
 */
public function downloadPDF($id)
{
    if (!$this->auth->check()) {
        $this->flash->flash('error', 'Vous devez être connecté pour accéder à cette page.');
        $this->helpers->redirect('/auth/login');
        return;
    }

    if (!$this->canManageLease($id)) {
        $this->flash->flash('error', 'Accès non autorisé.');
        $this->helpers->redirect('/leases');
        return;
    }

    try {
        $lease = Lease::find($id);
        if (!$lease) {
            $this->flash->flash('error', 'Bail introuvable.');
            $this->helpers->redirect('/leases');
            return;
        }

        $apartment = $lease->apartment();
        $tenant = $lease->tenant();
        $agency = $lease->getAgencyId() ? \App\Models\Agency::find($lease->getAgencyId()) : null;
        $agent = $lease->getAgentId() ? \App\Models\User::find($lease->getAgentId()) : null;
        $payments = Lease::getPaymentsByLease($id);

        // Initialiser TCPDF
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Configurer les métadonnées
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('ImmoApp');
        $pdf->SetTitle('Contrat de Location #' . $lease->getId());
        $pdf->SetSubject('Détails du bail');
        $pdf->SetKeywords('bail, location, immobilier');

        // Définir les marges
        $pdf->SetMargins(15, 30, 15);
        $pdf->SetHeaderMargin(10);
        $pdf->SetFooterMargin(10);

        // Configurer l'en-tête avec les détails de l'agence
        $headerText = $agency ? htmlspecialchars($agency->getName()) . "\n" .
            htmlspecialchars($agency->getAddress() ?? 'Adresse non définie') . "\n" .
            htmlspecialchars($agency->getPhone() ?? 'Téléphone non défini') . "\n" .
            htmlspecialchars($agency->getEmail() ?? 'Email non défini'). "\n" : 'Agence non définie' . "\n";
        $pdf->SetHeaderData('', 0, 'ImmoApp', $headerText);
        $pdf->setHeaderFont(['dejavusans', '', 10]);
        $pdf->setFooterFont(['dejavusans', '', 8]);

        // Activer l'auto page break
        $pdf->SetAutoPageBreak(TRUE, 15);

        // Définir la police
        $pdf->SetFont('dejavusans', '', 11);

        // Ajouter une page
        $pdf->AddPage();

        // Générer le contenu HTML
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
        <h1>Contrat de Location</h1>
        <h2>Bail #' . htmlspecialchars($lease->getId()) . '</h2>
        <p style="text-align: right; font-size: 10pt; color: #6B7280;">Généré le ' . date('d/m/Y H:i') . '</p>
        <h3>Informations Générales</h3>
        <table>
            <tr>
                <th>Appartement</th>
                <td>' . ($apartment ? htmlspecialchars($apartment->getNumber() . ' (Bâtiment: ' . $apartment->building()->getName() . ')') : 'Non défini') . '</td>
            </tr>
            <tr>
                <th>Locataire</th>
                <td>' . ($tenant ? htmlspecialchars($tenant->user()->getFirstName() . ' ' . $tenant->user()->getLastName()) : 'Non défini') . '</td>
            </tr>
            <tr>
                <th>Date de début</th>
                <td>' . date('d/m/Y', strtotime($lease->getStartDate())) . '</td>
            </tr>
            <tr>
                <th>Date de fin</th>
                <td>' . ($lease->getEndDate() ? date('d/m/Y', strtotime($lease->getEndDate())) : 'Non définie') . '</td>
            </tr>
            <tr>
                <th>Loyer</th>
                <td>' . number_format($lease->getRentAmount(), 2) . ' €</td>
            </tr>
            <tr>
                <th>Charges</th>
                <td>' . number_format($lease->getChargesAmount(), 2) . ' €</td>
            </tr>
            <tr>
                <th>Dépôt</th>
                <td>' . number_format($lease->getDepositAmount(), 2) . ' €</td>
            </tr>
            <tr>
                <th>Fréquence de paiement</th>
                <td>' . ($lease->getPaymentFrequency() === 'monthly' ? 'Mensuel' : 'Trimestriel') . '</td>
            </tr>
            <tr>
                <th>Statut</th>
                <td>' . ($lease->getIsActive() ? 'Actif' : 'Terminé') . '</td>
            </tr>
        </table>
        <h3>Associations</h3>
        <table>';

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
        <h3>Paiements Associés</h3>';

        if (empty($payments)) {
            $html .= '<p style="color: #6B7280;">Aucun paiement associé à ce bail.</p>';
        } else {
            $html .= '
            <table>
                <tr>
                    <th>ID</th>
                    <th>Date de paiement</th>
                    <th>Montant</th>
                    <th>Type</th>
                </tr>';
            foreach ($payments as $payment) {
                $html .= '
                <tr>
                    <td>' . htmlspecialchars($payment['id']) . '</td>
                    <td>' . date('d/m/Y', strtotime($payment['payment_date'])) . '</td>
                    <td>' . number_format($payment['amount'], 2) . ' €</td>
                    <td>' . htmlspecialchars(ucfirst($payment['type'])) . '</td>
                </tr>';
            }
            $html .= '
            </table>';
        }

        $html .= '
        <div class="signature">
            <p>Signature de l\'agence : _____________________________</p>
            <p>Signature du locataire : _____________________________</p>
        </div>
        <div class="footer-note">
            <p>Document généré par ImmoApp - Tous droits réservés</p>
        </div>';

        // Écrire le contenu HTML dans le PDF
        $pdf->writeHTML($html, true, false, true, false, '');

        // Fermer et sortir le PDF
        $pdf->Output('bail_' . $id . '.pdf', 'D');

        exit;
    } catch (PDOException $e) {
        $this->logger->error("Erreur lors du téléchargement du PDF du bail ID $id : " . $e->getMessage());
        $this->flash->flash('error', 'Une erreur est survenue lors de la génération du PDF.');
        $this->helpers->redirect('/leases');
    } catch (Exception $e) {
        $this->logger->error("Erreur TCPDF pour le bail ID $id : " . $e->getMessage());
        $this->flash->flash('error', 'Une erreur est survenue lors de la génération du PDF.');
        $this->helpers->redirect('/leases');
    }
}
}