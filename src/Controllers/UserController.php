<?php

namespace App\Controllers;

use App\Utils\Auth;
use App\Utils\Logger;
use App\Utils\Helpers;
use App\Utils\Flash;
use App\Models\User;
use App\Models\Role;
use App\Models\Owner;
use App\Models\Tenant;
use App\Models\Buyer;
use App\Config\Database;
use PDOException;

class UserController
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
 * Affiche la liste paginée des utilisateurs filtrée selon le rôle de l'utilisateur connecté.
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

    // Paramètres de pagination et filtres
    $page = max(1, (int) ($_GET['page'] ?? 1));
    $perPage = 10;
    $offset = ($page - 1) * $perPage;
    $search = trim($_GET['search'] ?? '');
    
    // Correction pour les rôles sélectionnés
    $selectedRoles = [];
    if (isset($_GET['roles']) && is_array($_GET['roles'])) {
        $selectedRoles = array_filter($_GET['roles'], function($role) {
            return !empty(trim($role));
        });
    }

    // Définir les rôles autorisés pour le filtre
    $allowedRoles = match ($role) {
        'superadmin' => ['agent', 'proprietaire', 'locataire', 'acheteur'],
        'admin' => ['agent', 'proprietaire', 'locataire', 'acheteur'],
        'agent' => ['proprietaire', 'locataire', 'acheteur'],
        default => []
    };

    $users = [];
    $totalUsers = 0;
    $roles = [];

    try {
        // Charger les rôles pour le filtre seulement si l'utilisateur a des rôles autorisés
        if (!empty($allowedRoles)) {
            $roles = Role::getByNames($allowedRoles);
        }

        // Valider les rôles sélectionnés
        $validSelectedRoles = array_intersect($selectedRoles, $allowedRoles);

        // Récupérer les utilisateurs selon le rôle
        if (!empty($allowedRoles)) {
            switch ($role) {
                case 'superadmin':
                    $users = User::getAll($search, $perPage, $offset, $validSelectedRoles);
                    $totalUsers = User::countAll($search, $validSelectedRoles);
                    break;
                    
                case 'admin':
                    if ($agencyId) {
                        $users = User::getByAgency($agencyId, $search, $perPage, $offset, $validSelectedRoles);
                        $totalUsers = User::countByAgency($agencyId, $search, $validSelectedRoles);
                    } else {
                        $this->flash->flash('error', 'Aucune agence associée à votre compte.');
                        $this->helpers->redirect('/dashboard');
                        return;
                    }
                    break;
                    
                case 'agent':
                    if ($agencyId && $agentId) {
                        $users = User::getByAgent($agentId, $agencyId, $search, $perPage, $offset, $validSelectedRoles);
                        $totalUsers = User::countByAgent($agentId, $agencyId, $search, $validSelectedRoles);
                    } else {
                        $this->flash->flash('error', 'Informations d\'agent ou d\'agence manquantes.');
                        $this->helpers->redirect('/dashboard');
                        return;
                    }
                    break;
                    
                default:
                    $this->flash->flash('error', 'Vous n\'avez pas les permissions nécessaires pour accéder à cette page.');
                    $this->helpers->redirect('/dashboard');
                    return;
            }
        } else {
            $this->flash->flash('error', 'Vous n\'avez pas les permissions nécessaires pour gérer les utilisateurs.');
            $this->helpers->redirect('/dashboard');
            return;
        }

        $totalPages = $totalUsers > 0 ? ceil($totalUsers / $perPage) : 1;
        
        // Vérifier que la page demandée existe
        if ($page > $totalPages && $totalPages > 0) {
            $this->helpers->redirect("/users?page={$totalPages}&search=" . urlencode($search) . 
                (!empty($validSelectedRoles) ? '&' . http_build_query(['roles' => $validSelectedRoles]) : ''));
            return;
        }

    } catch (PDOException $e) {
        $this->logger->error("Erreur lors du chargement des utilisateurs : " . $e->getMessage());
        $this->flash->flash('error', 'Une erreur est survenue lors du chargement des utilisateurs.');
        $users = [];
        $totalUsers = 0;
        $totalPages = 1;
    } catch (PDOException $e) {
        $this->logger->error("Erreur générale lors du chargement des utilisateurs : " . $e->getMessage());
        $this->flash->flash('error', 'Une erreur inattendue est survenue.');
        $users = [];
        $totalUsers = 0;
        $totalPages = 1;
    }

    // Préparer les variables pour la vue
    $viewData = [
        'users' => $users,
        'totalUsers' => $totalUsers,
        'totalPages' => $totalPages,
        'page' => $page,
        'search' => $search,
        'selectedRoles' => $validSelectedRoles,
        'roles' => $roles,
        'role' => $role, // Importante : passer le rôle à la vue
        'allowedRoles' => $allowedRoles
    ];

    // Extracter les variables pour la vue
    extract($viewData);

    $title = 'Gestion des utilisateurs';
    $content_view = 'admin/users/index.php';
    require_once dirname(__DIR__, 1) . '/Views/layouts/admin_layout.php';
}
    /**
     * Affiche le formulaire de création d'utilisateur avec les rôles autorisés.
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
        $roles = [];

        try {
            switch ($role) {
                case 'superadmin':
                    $roles = Role::getAll();
                    break;
                case 'admin':
                    $roles = Role::getByNames(['agent', 'proprietaire', 'locataire', 'acheteur']);
                    break;
                case 'agent':
                    $roles = Role::getByNames(['proprietaire', 'locataire', 'acheteur']);
                    break;
                default:
                    $this->flash->flash('error', 'Rôle non autorisé.');
                    $this->helpers->redirect('/auth/login');
                    return;
            }
        } catch (PDOException $e) {
            $this->logger->error("Erreur lors du chargement des rôles : " . $e->getMessage());
            $this->flash->flash('error', 'Une erreur est survenue lors du chargement des rôles.');
        }

        // Génère un jeton CSRF avec le nom de la route comme form_id
        $csrf_token = $this->helpers->csrf_token('users.store');
        $title = 'Ajouter un utilisateur';
        $content_view = 'admin/users/create.php';
        require_once dirname(__DIR__, 1) . '/Views/layouts/admin_layout.php';
    }

    /**
     * Enregistre un nouvel utilisateur avec validation et transaction.
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
        $errors = [];

        // Validation des entrées
        $data = [
            'username' => trim($_POST['username'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'password_confirm' => $_POST['password_confirm'] ?? '',
            'role_id' => (int) ($_POST['role_id'] ?? 0),
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'owner_type' => trim($_POST['owner_type'] ?? ''), // Pour owners uniquement
            'siret' => trim($_POST['siret'] ?? '') // Pour owners de type entreprise
        ];

        if (empty($data['username']) || strlen($data['username']) < 3) {
            $errors[] = 'Le nom d’utilisateur doit contenir au moins 3 caractères.';
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'L’email est invalide.';
        }
        if (User::emailExists($data['email'])) {
            $errors[] = 'Cet email est déjà utilisé.';
        }
        if (strlen($data['password']) < 8) {
            $errors[] = 'Le mot de passe doit contenir au moins 8 caractères.';
        }
        if ($data['password'] !== $data['password_confirm']) {
            $errors[] = 'Les mots de passe ne correspondent pas.';
        }
        if (empty($data['first_name']) || empty($data['last_name'])) {
            $errors[] = 'Le prénom et le nom sont obligatoires.';
        }
        if (!Role::exists($data['role_id'])) {
            $errors[] = 'Le rôle sélectionné est invalide.';
        }

        // Vérification des permissions pour le rôle
        $roleName = Role::getNameById($data['role_id']);
        $allowedRoles = match ($role) {
            'superadmin' => ['superadmin', 'admin', 'agent', 'proprietaire', 'locataire', 'acheteur'],
            'admin' => ['agent', 'proprietaire', 'locataire', 'acheteur'],
            'agent' => ['proprietaire', 'locataire', 'acheteur'],
            default => []
        };
        if (!in_array($roleName, $allowedRoles)) {
            $errors[] = 'Vous n’êtes pas autorisé à créer un utilisateur avec ce rôle.';
        }

        if ($roleName === 'proprietaire') {
            if (!in_array($data['owner_type'], ['particulier', 'entreprise'])) {
                $errors[] = 'Le type de propriétaire est invalide.';
            }
            if ($data['owner_type'] === 'entreprise' && empty($data['siret'])) {
                $errors[] = 'Le SIRET est requis pour un propriétaire de type entreprise.';
            }
            if ($data['owner_type'] === 'particulier' && !empty($data['siret'])) {
                $errors[] = 'Le SIRET ne doit pas être fourni pour un propriétaire de type particulier.';
            }
        }

        if (!empty($errors)) {
            $this->flash->flash('error', implode('<br>', $errors));
            $this->helpers->redirect('/users/create');
            return;
        }

        // Création de l'utilisateur dans une transaction
        $pdo = Database::getInstance();
        try {
            $pdo->beginTransaction();

            // Créer l'utilisateur
            $userData = [
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role_id' => $data['role_id'],
                'agency_id' => in_array($roleName, ['agent', 'proprietaire', 'locataire', 'acheteur']) ? $user['agency_id'] : null,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'phone' => $data['phone'] ?: null
            ];
            $userId = User::create($userData);

            // Créer l'entrée dans la table correspondante
            $relatedData = [
                'user_id' => $userId->getId(),
                'agent_id' => in_array($roleName, ['proprietaire', 'locataire', 'acheteur']) && $role === 'agent' ? $user['id'] : null,
                'agency_id' => in_array($roleName, ['proprietaire', 'locataire', 'acheteur']) ? $user['agency_id'] : null
            ];

            switch ($roleName) {
                case 'proprietaire':
                    $relatedData['type'] = $data['owner_type'];
                    $relatedData['siret'] = $data['owner_type'] === 'entreprise' ? $data['siret'] : null;
                    Owner::create($relatedData);
                    break;
                case 'locataire':
                    Tenant::create($relatedData);
                    break;
                case 'acheteur':
                    Buyer::create($relatedData);
                    break;
            }

            $pdo->commit();
            $this->flash->flash('success', 'Utilisateur ajouté avec succès.');
            $this->helpers->redirect('/users');
        } catch (PDOException $e) {
            $pdo->rollBack();
            $this->logger->error("Erreur lors de l’ajout de l’utilisateur : " . $e->getMessage());
            $this->flash->flash('error', 'Une erreur est survenue lors de l’ajout de l’utilisateur.');
            $this->helpers->redirect('/users/create');
        }
    }

    /**
     * Met à jour un utilisateur avec validation.
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
    $errors = [];

    $data = [
        'username' => trim($_POST['username'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'role_id' => (int) ($_POST['role_id'] ?? 0),
        'first_name' => trim($_POST['first_name'] ?? ''),
        'last_name' => trim($_POST['last_name'] ?? ''),
        'phone' => trim($_POST['phone'] ?? ''),
        'owner_type' => trim($_POST['owner_type'] ?? ''),
        'siret' => trim($_POST['siret'] ?? '')
    ];

    // --- Validations
    if (empty($data['username']) || strlen($data['username']) < 3) {
        $errors[] = 'Le nom d’utilisateur doit contenir au moins 3 caractères.';
    }
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'L’email est invalide.';
    }
    if (User::emailExists($data['email'], $id)) {
        $errors[] = 'Cet email est déjà utilisé par un autre utilisateur.';
    }
    if (!Role::exists($data['role_id'])) {
        $errors[] = 'Le rôle sélectionné est invalide.';
    }

    $roleName = Role::getNameById($data['role_id']);
    $allowedRoles = match ($role) {
        'superadmin' => ['superadmin', 'admin', 'agent', 'proprietaire', 'locataire', 'acheteur'],
        'admin' => ['agent', 'proprietaire', 'locataire', 'acheteur'],
        'agent' => ['proprietaire', 'locataire', 'acheteur'],
        default => []
    };

    if (!in_array($roleName, $allowedRoles)) {
        $errors[] = 'Vous n’êtes pas autorisé à assigner ce rôle.';
    }

    if ($roleName === 'proprietaire') {
        if (!in_array($data['owner_type'], ['particulier', 'entreprise'])) {
            $errors[] = 'Le type de propriétaire est invalide.';
        }
        if ($data['owner_type'] === 'entreprise' && empty($data['siret'])) {
            $errors[] = 'Le SIRET est requis pour un propriétaire de type entreprise.';
        }
        if ($data['owner_type'] === 'particulier' && !empty($data['siret'])) {
            $errors[] = 'Le SIRET ne doit pas être fourni pour un propriétaire de type particulier.';
        }
    }

    if (!empty($errors)) {
        $this->flash->flash('error', implode('<br>', $errors));
        $this->helpers->redirect("/users/edit/{$id}");
        return;
    }

    $pdo = Database::getInstance();
    try {
        $pdo->beginTransaction();

        $targetUser = User::findById($id);
        if (!$targetUser || $targetUser->getIsDeleted()) {
            $pdo->rollBack();
            $this->flash->flash('error', 'Utilisateur introuvable.');
            $this->helpers->redirect('/users');
            return;
        }

        if (!$this->canManageUser($user, $targetUser)) {
            $pdo->rollBack();
            $this->flash->flash('error', 'Vous n’êtes pas autorisé à modifier cet utilisateur.');
            $this->helpers->redirect('/users');
            return;
        }

        $userData = [
            'username' => $data['username'],
            'email' => $data['email'],
            'role_id' => $data['role_id'],
            'agency_id' => in_array($roleName, ['agent', 'proprietaire', 'locataire', 'acheteur']) ? $user['agency_id'] : null,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'phone' => $data['phone'] ?: null,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        User::update($id, $userData);

        $relatedData = [
            'user_id' => $id,
            'agent_id' => in_array($roleName, ['proprietaire', 'locataire', 'acheteur']) && $role === 'agent' ? $user['id'] : null,
            'agency_id' => in_array($roleName, ['proprietaire', 'locataire', 'acheteur']) ? $user['agency_id'] : null
        ];

        switch ($roleName) {
            case 'proprietaire':
                $relatedData['type'] = $data['owner_type'];
                $relatedData['siret'] = $data['owner_type'] === 'entreprise' ? $data['siret'] : null;
                $existingOwner = Owner::findByUserId($id);
                if ($existingOwner) {
                    Owner::update($existingOwner->getId(), $relatedData);
                } else {
                    Owner::create($relatedData);
                }
                break;

            case 'locataire':
                $existingTenant = Tenant::findByUserId($id);
                if ($existingTenant) {
                    Tenant::update($existingTenant->getId(), $relatedData);
                } else {
                    Tenant::create($relatedData);
                }
                break;

            case 'acheteur':
                $existingBuyer = Buyer::findByUserId($id);
                if ($existingBuyer) {
                    Buyer::update($existingBuyer->getId(), $relatedData);
                } else {
                    Buyer::create($relatedData);
                }
                break;
        }

        $pdo->commit();
        $this->flash->flash('success', 'Utilisateur mis à jour avec succès.');
        $this->helpers->redirect('/users');
    } catch (PDOException $e) {
        $pdo->rollBack();
        $this->logger->error("Erreur lors de la mise à jour de l’utilisateur : " . $e->getMessage());
        $this->flash->flash('error', 'Une erreur est survenue lors de la mise à jour de l’utilisateur.');
        $this->helpers->redirect("/users/edit/{$id}");
    }
}


/**
 * Affiche les détails d’un utilisateur spécifique.
 * @param int $id L’identifiant de l’utilisateur à afficher.
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

    try {
        // Récupérer l’utilisateur cible
        $targetUser = User::findById($id);
        if (!$targetUser || ($targetUser->is_deleted ?? false)) { // Vérification de is_deleted
            $this->flash->flash('error', 'Utilisateur introuvable.');
            $this->helpers->redirect('/users');
            return;
        }

        // Vérification des permissions
        if (!$this->canManageUser($user, $targetUser)) {
            $this->flash->flash('error', 'Vous n’êtes pas autorisé à voir cet utilisateur.');
            $this->helpers->redirect('/users');
            return;
        }

        // Préparer les données pour la vue
        $userData = [
            'id' => $targetUser->getId(),
            'first_name' => $targetUser->getFirstName(),
            'last_name' => $targetUser->getLastName(),
            'email' => $targetUser->getEmail(),
            'phone' => $targetUser->getPhone() ?? 'Non spécifié',
            'role' => $this->formatRole(Role::getNameById($targetUser->getRoleId())),
            'agency_id' => $targetUser->getAgencyId() ?? 'Non spécifié',
            'created_at' => $this->formatDate($targetUser->getCreatedAt()),
        ];

        // Définir les variables pour la vue
        $title = 'Détails de l’utilisateur';
        $content_view = 'admin/users/show.php';
        $user_data = $userData; // Passer les données à la vue

        // Charger le layout
        require_once dirname(__DIR__, 1) . '/Views/layouts/admin_layout.php';
    } catch (PDOException $e) {
        $this->logger->error("Erreur lors du chargement de l’utilisateur : " . $e->getMessage(), [
            'user_id' => $id,
            'error' => $e->getMessage()
        ]);
        $this->flash->flash('error', 'Une erreur est survenue lors du chargement de l’utilisateur.');
        $this->helpers->redirect('/users');
    }
}

/**
 * Formate le rôle pour l’affichage.
 * @param string $role Le rôle brut.
 * @return string Le rôle formaté.
 */
private function formatRole($role)
{
    $roles = [
        'superadmin' => 'Super Administrateur',
        'admin' => 'Administrateur',
        'agent' => 'Agent',
        'proprietaire' => 'Propriétaire',
        'locataire' => 'Locataire',
        'acheteur' => 'Acheteur',
        'guest' => 'Invité'
    ];
    return $roles[$role] ?? ucfirst($role);
}

/**
 * Formate la date pour l’affichage.
 * @param string $date La date brute (format SQL).
 * @return string La date formatée (ex. 01/01/2023).
 */
private function formatDate($date)
{
    return $date ? date('d/m/Y', strtotime($date)) : 'Non spécifié';
}

    /**
     * Affiche le formulaire de modification d’un utilisateur.
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
        $roles = [];

        try {
            $targetUser = User::findById($id);
            if (!$targetUser || $targetUser->getIsDeleted()) {
                $this->flash->flash('error', 'Utilisateur introuvable.');
                $this->helpers->redirect('/users');
                return;
            }

            // Vérification des permissions
            if (!$this->canManageUser($user, $targetUser)) {
                $this->flash->flash('error', 'Vous n’êtes pas autorisé à modifier cet utilisateur.');
                $this->helpers->redirect('/users');
                return;
            }

            // Charger les rôles autorisés
            switch ($role) {
                case 'superadmin':
                    $roles = Role::getAll();
                    break;
                case 'admin':
                    $roles = Role::getByNames(['agent', 'proprietaire', 'locataire', 'acheteur']);
                    break;
                case 'agent':
                    $roles = Role::getByNames(['proprietaire', 'locataire', 'acheteur']);
                    break;
            }

          // Génère un jeton CSRF pour la vue
        $csrf_token = $this->helpers->csrf_token('users.update');
            $title = 'Modifier l’utilisateur';
            $content_view = 'admin/users/edit.php';
            require_once dirname(__DIR__, 1) . '/Views/layouts/admin_layout.php';
        } catch (PDOException $e) {
            $this->logger->error("Erreur lors du chargement de l’utilisateur : " . $e->getMessage());
            $this->flash->flash('error', 'Une erreur est survenue lors du chargement de l’utilisateur.');
            $this->helpers->redirect('/users');
        }
    }



    /**
     * Supprime un utilisateur (suppression logique).
     */
    public function delete($id)
    {
        if (!$this->auth->check()) {
            $this->flash->flash('error', 'Vous devez être connecté pour accéder à cette page.');
            $this->helpers->redirect('/auth/login');
            return;
        }

        $user = $this->auth->user();
        $role = $user['role'] ?? 'guest';

        $pdo = Database::getInstance();
        try {
            $pdo->beginTransaction();

            $targetUser = User::findById($id);
            if (!$targetUser || $targetUser->getIsDeleted()) {
                $pdo->rollBack();
                $this->flash->flash('error', 'Utilisateur introuvable.');
                $this->helpers->redirect('/users');
                return;
            }

            if (!$this->canManageUser($user, $targetUser)) {
                $pdo->rollBack();
                $this->flash->flash('error', 'Vous n’êtes pas autorisé à supprimer cet utilisateur.');
                $this->helpers->redirect('/users');
                return;
            }

            // Suppression logique
            User::softDelete($id);
            Owner::softDeleteByUserId($id);
            Tenant::softDeleteByUserId($id);
            Buyer::softDeleteByUserId($id);

            $pdo->commit();
            $this->flash->flash('success', 'Utilisateur supprimé avec succès.');
            $this->helpers->redirect('/users');
        } catch (PDOException $e) {
            $pdo->rollBack();
            $this->logger->error("Erreur lors de la suppression de l’utilisateur : " . $e->getMessage());
            $this->flash->flash('error', 'Une erreur est survenue lors de la suppression de l’utilisateur.');
            $this->helpers->redirect('/users');
        }
    }

    /**
     * Vérifie si l’utilisateur connecté peut gérer un utilisateur cible.
     */
    private function canManageUser($currentUser, $targetUser)
    {
        $currentRole = $currentUser['role'];
        $targetRole = Role::getNameById($targetUser->getRoleId());

        return match ($currentRole) {
            'superadmin' => true,
            'admin' => $currentUser['agency_id'] === $targetUser->getAgencyId() && in_array($targetRole, ['agent', 'proprietaire', 'locataire', 'acheteur']),
            'agent' => $currentUser['agency_id'] === $targetUser->getAgencyId() && in_array($targetRole, ['proprietaire', 'locataire', 'acheteur']) && $this->isCreatedByAgent($currentUser['id'], $targetUser->getId()),
            default => false
        };
    }

    /**
     * Vérifie si un utilisateur a été créé par un agent spécifique.
     */
    private function isCreatedByAgent($agentId, $userId)
    {
        return Owner::isCreatedByAgent($agentId, $userId) ||
               Tenant::isCreatedByAgent($agentId, $userId) ||
               Buyer::isCreatedByAgent($agentId, $userId);
    }
}
?>