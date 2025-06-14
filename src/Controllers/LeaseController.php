<?php

namespace App\Controllers;

use App\Utils\Auth;
use App\Utils\Logger;
use App\Utils\Helpers;
use App\Utils\Flash;
use App\Models\Lease;
use App\Models\Apartment;
use App\Models\Tenant;
use App\Config\Database;
use PDOException;

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
     * Affiche la liste paginée des baux filtrée selon le rôle de l'utilisateur connecté.
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
        $agencyId = $user['agency_id'] ?? null;
        $agentId = $user['id'] ?? null;
        $userId = $user['id'] ?? null;

        // Paramètres de pagination et filtres
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        $search = trim($_GET['search'] ?? '');

        $leases = [];
        $totalLeases = 0;

        try {
            switch ($role) {
                case 'superadmin':
                    $leases = Lease::get();
                    $totalLeases = count($leases);
                    break;
                case 'admin':
                    if ($agencyId) {
                        $leases = Lease::findByAgencyId($agencyId, $search, $perPage, $offset);
                        $totalLeases = Lease::countByAgency($agencyId, $search);
                    } else {
                        $this->flash->flash('error', 'Aucune agence associée à votre compte.');
                        $this->helpers->redirect('/dashboard');
                        return;
                    }
                    break;
                case 'agent':
                    if ($agencyId && $agentId) {
                        $leases = Lease::findByAgentId($agentId, $agencyId, $search, $perPage, $offset);
                        $totalLeases = Lease::countActiveByAgent($agentId, $agencyId);
                    } else {
                        $this->flash->flash('error', 'Informations d\'agent ou d\'agence manquantes.');
                        $this->helpers->redirect('/dashboard');
                        return;
                    }
                    break;
                case 'proprietaire':
                    $leases = Lease::findByOwnerId($userId, $search, $perPage, $offset);
                    $totalLeases = Lease::countByOwner($userId, $search);
                    break;
                case 'locataire':
                    $tenant = Tenant::findByUserId($userId);
                    if ($tenant) {
                        $leases = Lease::findByTenantId($tenant->getId());
                        $totalLeases = Lease::countActiveByTenant($tenant->getId());
                    } else {
                        $this->flash->flash('error', 'Aucun profil locataire associé.');
                        $this->helpers->redirect('/dashboard');
                        return;
                    }
                    break;
                default:
                    $this->flash->flash('error', 'Vous n\'avez pas les permissions nécessaires pour accéder à cette page.');
                    $this->helpers->redirect('/dashboard');
                    return;
            }

            $totalPages = $totalLeases > 0 ? ceil($totalLeases / $perPage) : 1;

            if ($page > $totalPages && $totalPages > 0) {
                $this->helpers->redirect("/leases?page={$totalPages}&search=" . urlencode($search));
                return;
            }

        } catch (PDOException $e) {
            $this->logger->error("Erreur lors du chargement des baux : " . $e->getMessage());
            $this->flash->flash('error', 'Une erreur est survenue lors du chargement des baux.');
            $leases = [];
            $totalLeases = 0;
            $totalPages = 1;
        }

        $viewData = [
            'leases' => $leases,
            'totalLeases' => $totalLeases,
            'totalPages' => $totalPages,
            'page' => $page,
            'search' => $search,
            'role' => $role
        ];

        extract($viewData);
        $title = 'Gestion des baux';
        $content_view = 'admin/leases/index.php';
        require_once dirname(__DIR__, 1) . '/Views/layouts/admin_layout.php';
    }

    /**
     * Affiche le formulaire de création d'un bail.
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
        $agencyId = $user['agency_id'] ?? null;
        $agentId = $user['id'] ?? null;

        try {
            $apartments = [];
            $tenants = [];
            switch ($role) {
                case 'superadmin':
                    $apartments = Apartment::getAllAvailable();
                    $tenants = Tenant::getAll();
                    break;
                case 'admin':
                    if ($agencyId) {
                        $apartments = Apartment::getByAgency($agencyId);
                        $tenants = Tenant::getByAgency($agencyId);
                    }
                    break;
                case 'agent':
                    if ($agencyId && $agentId) {
                        $apartments = Apartment::getByAgent($agentId, $agencyId);
                        $tenants = Tenant::getByAgent($agentId, $agencyId);
                    }
                    break;
                default:
                    $this->flash->flash('error', 'Vous n\'avez pas les permissions nécessaires pour créer un bail.');
                    $this->helpers->redirect('/dashboard');
                    return;
            }

            $csrf_token = $this->helpers->csrf_token('leases.store');
            $title = 'Créer un bail';
            $content_view = 'admin/leases/create.php';
            require_once dirname(__DIR__, 1) . '/Views/layouts/admin_layout.php';
        } catch (PDOException $e) {
            $this->logger->error("Erreur lors du chargement des données pour la création du bail : " . $e->getMessage());
            $this->flash->flash('error', 'Une erreur est survenue.');
            $this->helpers->redirect('/leases');
        }
    }

    /**
     * Enregistre un nouveau bail avec validation.
     */
    public function store()
    {
        if (!$this->auth->check()) {
            $this->flash->flash('error', 'Vous devez être connecté pour accéder à cette page.');
            $this->helpers->redirect('/auth/login');
            return;
        }

        if (!$this->helpers->verify_csrf_token('leases.store')) {
            $this->flash->flash('error', 'Jeton CSRF invalide.');
            $this->helpers->redirect('/leases/create');
            return;
        }

        $user = $this->auth->user();
        $role = $user['role'] ?? 'guest';
        $agencyId = $user['agency_id'] ?? null;
        $agentId = $user['id'] ?? null;

        $data = [
            'apartment_id' => (int) ($_POST['apartment_id'] ?? 0),
            'tenant_id' => (int) ($_POST['tenant_id'] ?? 0),
            'start_date' => trim($_POST['start_date'] ?? ''),
            'end_date' => trim($_POST['end_date'] ?? ''),
            'rent_amount' => (float) ($_POST['rent_amount'] ?? 0),
            'charges_amount' => (float) ($_POST['charges_amount'] ?? 0),
            'deposit_amount' => (float) ($_POST['deposit_amount'] ?? 0),
            'payment_frequency' => trim($_POST['payment_frequency'] ?? ''),
            'status' => trim($_POST['status'] ?? '')
        ];

        $errors = [];
        if (!Apartment::find($data['apartment_id'])) {
            $errors[] = 'L’appartement sélectionné est invalide.';
        }
        if (!Tenant::find($data['tenant_id'])) {
            $errors[] = 'Le locataire sélectionné est invalide.';
        }
        if (empty($data['start_date']) || !strtotime($data['start_date'])) {
            $errors[] = 'La date de début est invalide.';
        }
        if (!empty($data['end_date']) && !strtotime($data['end_date'])) {
            $errors[] = 'La date de fin est invalide.';
        }
        if ($data['rent_amount'] <= 0) {
            $errors[] = 'Le montant du loyer doit être supérieur à 0.';
        }
        if ($data['charges_amount'] < 0) {
            $errors[] = 'Le montant des charges ne peut pas être négatif.';
        }
        if ($data['deposit_amount'] < 0) {
            $errors[] = 'Le montant du dépôt ne peut pas être négatif.';
        }
        if (!in_array($data['payment_frequency'], ['mensuel', 'trimestriel', 'annuel'])) {
            $errors[] = 'La fréquence de paiement est invalide.';
        }
        if (!in_array($data['status'], ['actif', 'terminé', 'annulé'])) {
            $errors[] = 'Le statut est invalide.';
        }

        if (!empty($errors)) {
            $this->flash->flash('error', implode('<br>', $errors));
            $this->helpers->redirect('/leases/create');
            return;
        }

        $pdo = Database::getInstance();
        try {
            $pdo->beginTransaction();

            $data['agent_id'] = in_array($role, ['agent']) ? $agentId : null;
            $data['agency_id'] = in_array($role, ['admin', 'agent']) ? $agencyId : null;
            $data['is_active'] = $data['status'] === 'actif' ? 1 : 0;

            Lease::create($data);
            $pdo->commit();
            $this->flash->flash('success', 'Bail ajouté avec succès.');
            $this->helpers->redirect('/leases');
        } catch (PDOException $e) {
            $pdo->rollBack();
            $this->logger->error("Erreur lors de l’ajout du bail : " . $e->getMessage());
            $this->flash->flash('error', 'Une erreur est survenue lors de l’ajout du bail.');
            $this->helpers->redirect('/leases/create');
        }
    }

    /**
     * Affiche les détails d’un bail spécifique.
     */
    public function show($id)
    {
        if (!$this->auth->check()) {
            $this->flash->flash('error', 'Vous devez être connecté pour accéder à cette page.');
            $this->helpers->redirect('/auth/login');
            return;
        }

        $user = $this->auth->user();
        $role = $user['role'] ?? 'guest';
        $userId = $user['id'] ?? null;

        try {
            $lease = Lease::findById($id);
            if (!$lease || $lease->getDeletedAt()) {
                $this->flash->flash('error', 'Bail introuvable.');
                $this->helpers->redirect('/leases');
                return;
            }

            if (!$this->canManageLease($user, $lease)) {
                $this->flash->flash('error', 'Vous n’êtes pas autorisé à voir ce bail.');
                $this->helpers->redirect('/leases');
                return;
            }

            $leaseData = [
                'id' => $lease->getId(),
                'apartment' => $lease->apartment()->number ?? 'Non spécifié',
                'tenant' => $lease->tenant()->user()->getFirstName() . ' ' . $lease->tenant()->user()->getLastName(),
                'start_date' => $this->formatDate($lease->getStartDate()),
                'end_date' => $this->formatDate($lease->getEndDate()),
                'rent_amount' => number_format($lease->getRentAmount(), 2) . ' FCFA',
                'charges_amount' => number_format($lease->getChargesAmount(), 2) . ' FCFA',
                'deposit_amount' => number_format($lease->getDepositAmount(), 2) . ' FCFA',
                'payment_frequency' => $this->formatPaymentFrequency($lease->getPaymentFrequency()),
                'status' => $this->formatStatus($lease->getStatus()),
                'created_at' => $this->formatDate($lease->getCreatedAt())
            ];

            $title = 'Détails du bail';
            $content_view = 'admin/leases/show.php';
            $lease_data = $leaseData;
            require_once dirname(__DIR__, 1) . '/Views/layouts/admin_layout.php';
        } catch (PDOException $e) {
            $this->logger->error("Erreur lors du chargement du bail : " . $e->getMessage());
            $this->flash->flash('error', 'Une erreur est survenue lors du chargement du bail.');
            $this->helpers->redirect('/leases');
        }
    }

    /**
     * Affiche le formulaire de modification d’un bail.
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
        $agencyId = $user['agency_id'] ?? null;
        $agentId = $user['id'] ?? null;

        try {
            $lease = Lease::findById($id);
            if (!$lease || $lease->getDeletedAt()) {
                $this->flash->flash('error', 'Bail introuvable.');
                $this->helpers->redirect('/leases');
                return;
            }

            if (!$this->canManageLease($user, $lease)) {
                $this->flash->flash('error', 'Vous n’êtes pas autorisé à modifier ce bail.');
                $this->helpers->redirect('/leases');
                return;
            }

            $apartments = [];
            $tenants = [];
            switch ($role) {
                case 'superadmin':
                    $apartments = Apartment::getAllAvailable();
                    $tenants = Tenant::getAll();
                    break;
                case 'admin':
                    if ($agencyId) {
                        $apartments = Apartment::getByAgency($agencyId);
                        $tenants = Tenant::getByAgency($agencyId);
                    }
                    break;
                case 'agent':
                    if ($agencyId && $agentId) {
                        $apartments = Apartment::getByAgent($agentId, $agencyId);
                        $tenants = Tenant::getByAgent($agentId, $agencyId);
                    }
                    break;
            }

            $csrf_token = $this->helpers->csrf_token('leases.update');
            $title = 'Modifier le bail';
            $content_view = 'admin/leases/edit.php';
            require_once dirname(__DIR__, 1) . '/Views/layouts/admin_layout.php';
        } catch (PDOException $e) {
            $this->logger->error("Erreur lors du chargement du bail : " . $e->getMessage());
            $this->flash->flash('error', 'Une erreur est survenue lors du chargement du bail.');
            $this->helpers->redirect('/leases');
        }
    }

    /**
     * Met à jour un bail avec validation.
     */
    public function update($id)
    {
        if (!$this->auth->check()) {
            $this->flash->flash('error', 'Vous devez être connecté pour accéder à cette page.');
            $this->helpers->redirect('/auth/login');
            return;
        }

        if (!$this->helpers->verify_csrf_token('leases.update')) {
            $this->flash->flash('error', 'Jeton CSRF invalide.');
            $this->helpers->redirect("/leases/edit/{$id}");
            return;
        }

        $user = $this->auth->user();
        $role = $user['role'] ?? 'guest';

        $data = [
            'apartment_id' => (int) ($_POST['apartment_id'] ?? 0),
            'tenant_id' => (int) ($_POST['tenant_id'] ?? 0),
            'start_date' => trim($_POST['start_date'] ?? ''),
            'end_date' => trim($_POST['end_date'] ?? ''),
            'rent_amount' => (float) ($_POST['rent_amount'] ?? 0),
            'charges_amount' => (float) ($_POST['charges_amount'] ?? 0),
            'deposit_amount' => (float) ($_POST['deposit_amount'] ?? 0),
            'payment_frequency' => trim($_POST['payment_frequency'] ?? ''),
            'status' => trim($_POST['status'] ?? '')
        ];

        $errors = [];
        if (!Apartment::find($data['apartment_id'])) {
            $errors[] = 'L’appartement sélectionné est invalide.';
        }
        if (!Tenant::find($data['tenant_id'])) {
            $errors[] = 'Le locataire sélectionné est invalide.';
        }
        if (empty($data['start_date']) || !strtotime($data['start_date'])) {
            $errors[] = 'La date de début est invalide.';
        }
        if (!empty($data['end_date']) && !strtotime($data['end_date'])) {
            $errors[] = 'La date de fin est invalide.';
        }
        if ($data['rent_amount'] <= 0) {
            $errors[] = 'Le montant du loyer doit être supérieur à 0.';
        }
        if ($data['charges_amount'] < 0) {
            $errors[] = 'Le montant des charges ne peut pas être négatif.';
        }
        if ($data['deposit_amount'] < 0) {
            $errors[] = 'Le montant du dépôt ne peut pas être négatif.';
        }
        if (!in_array($data['payment_frequency'], ['mensuel', 'trimestriel', 'annuel'])) {
            $errors[] = 'La fréquence de paiement est invalide.';
        }
        if (!in_array($data['status'], ['actif', 'terminé', 'annulé'])) {
            $errors[] = 'Le statut est invalide.';
        }

        if (!empty($errors)) {
            $this->flash->flash('error', implode('<br>', $errors));
            $this->helpers->redirect("/leases/edit/{$id}");
            return;
        }

        $pdo = Database::getInstance();
        try {
            $pdo->beginTransaction();

            $lease = Lease::findById($id);
            if (!$lease || $lease->getDeletedAt()) {
                $pdo->rollBack();
                $this->flash->flash('error', 'Bail introuvable.');
                $this->helpers->redirect('/leases');
                return;
            }

            if (!$this->canManageLease($user, $lease)) {
                $pdo->rollBack();
                $this->flash->flash('error', 'Vous n’êtes pas autorisé à modifier ce bail.');
                $this->helpers->redirect('/leases');
                return;
            }

            $data['is_active'] = $data['status'] === 'actif' ? 1 : 0;
            Lease::update($id, $data);
            $pdo->commit();
            $this->flash->flash('success', 'Bail mis à jour avec succès.');
            $this->helpers->redirect('/leases');
        } catch (PDOException $e) {
            $pdo->rollBack();
            $this->logger->error("Erreur lors de la mise à jour du bail : " . $e->getMessage());
            $this->flash->flash('error', 'Une erreur est survenue lors de la mise à jour du bail.');
            $this->helpers->redirect("/leases/edit/{$id}");
        }
    }

    /**
     * Supprime un bail (suppression logique).
     */
    public function delete($id)
    {
        if (!$this->auth->check()) {
            $this->flash->flash('error', 'Vous devez être connecté pour accéder à cette page.');
            $this->helpers->redirect('/auth/login');
            return;
        }

        if (!$this->helpers->verify_csrf_token('leases.delete')) {
            $this->flash->flash('error', 'Jeton CSRF invalide.');
            $this->helpers->redirect('/leases');
            return;
        }

        $user = $this->auth->user();
        $role = $user['role'] ?? 'guest';

        $pdo = Database::getInstance();
        try {
            $pdo->beginTransaction();

            $lease = Lease::findById($id);
            if (!$lease || $lease->getDeletedAt()) {
                $pdo->rollBack();
                $this->flash->flash('error', 'Bail introuvable.');
                $this->helpers->redirect('/leases');
                return;
            }

            if (!$this->canManageLease($user, $lease)) {
                $pdo->rollBack();
                $this->flash->flash('error', 'Vous n’êtes pas autorisé à supprimer ce bail.');
                $this->helpers->redirect('/leases');
                return;
            }

            Lease::delete($id);
            $pdo->commit();
            $this->flash->flash('success', 'Bail supprimé avec succès.');
            $this->helpers->redirect('/leases');
        } catch (PDOException $e) {
            $pdo->rollBack();
            $this->logger->error("Erreur lors de la suppression du bail : " . $e->getMessage());
            $this->flash->flash('error', 'Une erreur est survenue lors de la suppression du bail.');
            $this->helpers->redirect('/leases');
        }
    }

    /**
     * Vérifie si l’utilisateur connecté peut gérer un bail.
     */
    private function canManageLease($user, $lease)
    {
        $role = $user['role'] ?? 'guest';
        $agencyId = $user['agency_id'] ?? null;
        $agentId = $user['id'] ?? null;
        $userId = $user['id'] ?? null;

        $tenant = Tenant::find($lease->getTenantId());
        $apartment = Apartment::find($lease->getApartmentId());

        return match ($role) {
            'superadmin' => true,
            'admin' => $agencyId && $lease->getAgencyId() === $agencyId,
            'agent' => $agencyId && $agentId && $lease->getAgentId() === $agentId && $lease->getAgencyId() === $agencyId,
            'proprietaire' => $apartment && $apartment->getOwnerId() === $userId,
            'locataire' => $tenant && $tenant->getUserId() === $userId,
            default => false
        };
    }

    /**
     * Formate la date pour l’affichage.
     */
    private function formatDate($date)
    {
        return $date ? date('d/m/Y', strtotime($date)) : 'Non spécifié';
    }

    /**
     * Formate la fréquence de paiement pour l’affichage.
     */
    private function formatPaymentFrequency($frequency)
    {
        $frequencies = [
            'mensuel' => 'Mensuel',
            'trimestriel' => 'Trimestriel',
            'annuel' => 'Annuel'
        ];
        return $frequencies[$frequency] ?? ucfirst($frequency);
    }

    /**
     * Formate le statut pour l’affichage.
     */
    private function formatStatus($status)
    {
        $statuses = [
            'actif' => 'Actif',
            'terminé' => 'Terminé',
            'annulé' => 'Annulé'
        ];
        return $statuses[$status] ?? ucfirst($status);
    }
}