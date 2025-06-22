<?php
namespace App\Controllers;

use App\Utils\Auth;
use App\Utils\Logger;
use App\Utils\Helpers;
use App\Utils\Flash;
use App\Models\Building;
use App\Models\Owner;
use App\Models\BuildingType;
use App\Models\Agency;
use App\Models\User;
use App\Models\Role;
use App\Config\Database;
use PDOException;

class BuildingController
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
     * Affiche la liste paginée des bâtiments filtrée selon le rôle de l'utilisateur connecté.
     */
    public function index()
    {
        if (!$this->auth->check()) {
            $this->flash->flash('error', 'Vous devez être connecté pour accéder à cette page.');
            $this->helpers->redirect('/auth/login');
            return;
        }
    
        $user = $this->auth->user();
        $userObj = User::findById($user['id']);
        $role = $userObj && $userObj->role() ? $userObj->role()->getName() : 'guest';
        $agencyId = $user['agency_id'] ?? null;
        $agentId = $user['id'] ?? null;
    
        // Génération du jeton CSRF pour la suppression
        $csrf_token = $this->helpers->generateCsrfToken('buildings.delete');
        $_SESSION['csrf_tokens']['buildings.delete'] = $csrf_token;
        $this->logger->info('Jeton CSRF généré pour buildings.delete: ' . $csrf_token);
    
        // Paramètres de pagination et filtres
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 10;
        $offset = ($page - 1) * $perPage;
        $search = trim($_GET['search'] ?? '');
        $selectedStatuses = array_filter($_GET['status'] ?? [], 'strlen');
    
        // Définir les statuts autorisés pour le filtre
        $allowedStatuses = ['disponible', 'vendu', 'en_construction', 'en_renovation'];
    
        $buildings = [];
        $totalBuildings = 0;
        $buildingTypes = [];
    
        try {
            // Charger les types de bâtiments pour le filtre
            $buildingTypes = BuildingType::get();
    
            // Récupérer les bâtiments selon le rôle
            switch ($role) {
                case 'superadmin':
                    $buildings = Building::getAll($search, $perPage, $offset, $selectedStatuses);
                    $totalBuildings = Building::countAll($search, $selectedStatuses);
                    break;
                case 'admin':
                    if ($agencyId) {
                        $buildings = Building::findByAgencyId($agencyId, $search, $perPage, $offset, $selectedStatuses);
                        $totalBuildings = Building::countByAgency($agencyId, $search, $selectedStatuses);
                    } else {
                        $this->flash->flash('error', 'Aucune agence associée à votre compte.');
                        $this->helpers->redirect('/dashboard');
                        return;
                    }
                    break;
                case 'agent':
                    if ($agencyId && $agentId) {
                        $buildings = Building::findByAgentId($agentId, $agencyId, $search, $perPage, $offset, $selectedStatuses);
                        $totalBuildings = Building::countByAgent($agentId, $agencyId, $search, $selectedStatuses);
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
    
            $totalPages = $totalBuildings > 0 ? ceil($totalBuildings / $perPage) : 1;
    
            // Vérifier que la page demandée existe
            if ($page > $totalPages && $totalPages > 0) {
                $this->helpers->redirect("/buildings?page={$totalPages}&search=" . urlencode($search) . (!empty($selectedStatuses) ? '&' . http_build_query(['status' => $selectedStatuses]) : ''));
                return;
            }
    
        } catch (PDOException $e) {
            $this->logger->error("Erreur lors du chargement des bâtiments : " . $e->getMessage());
            $this->flash->flash('error', 'Une erreur est survenue lors du chargement des bâtiments.');
            $buildings = [];
            $totalBuildings = 0;
            $totalPages = 1;
        }
    
        // Préparer les variables pour la vue
        $viewData = [
            'buildings' => $buildings,
            'totalBuildings' => $totalBuildings,
            'totalPages' => $totalPages,
            'page' => $page,
            'search' => $search,
            'selectedStatuses' => $selectedStatuses,
            'buildingTypes' => $buildingTypes,
            'role' => $role,
            'allowedStatuses' => $allowedStatuses,
            'helpers' => $this->helpers,
            'csrf_token' => $csrf_token // Passer le jeton à la vue
        ];
    
        extract($viewData);
        $title = 'Gestion des bâtiments';
        $content_view = 'admin/buildings/index.php';
        require_once dirname(__DIR__, 1) . '/Views/layouts/admin_layout.php';
    }
    /**
     * Affiche le formulaire de création d’un bâtiment.
     */
    public function create()
    {
        if (!$this->auth->check()) {
            $this->flash->flash('error', 'Vous devez être connecté pour accéder à cette page.');
            $this->helpers->redirect('/auth/login');
            return;
        }

        $user = $this->auth->user();
        $userObj = User::findById($user['id']);
        $role = $userObj && $userObj->role() ? $userObj->role()->getName() : 'guest';
        $buildingTypes = [];
        $agencies = [];
        $owners = [];

        try {
            // Charger les types de bâtiments
            $buildingTypes = BuildingType::get();

            // Charger les agences et propriétaires selon le rôle
            switch ($role) {
                case 'superadmin':
                    $agencies = Agency::get();
                    $owners = Owner::get(); // Correction pour superadmin
                    break;
                case 'admin':
                case 'agent':
                    if ($user['agency_id']) {
                        $agency = Agency::find($user['agency_id']);
                        $agencies = $agency ? [$agency] : [];
                        $owners = Owner::findByAgencyId($user['agency_id']);
                    } else {
                        $this->flash->flash('error', 'Aucune agence associée à votre compte.');
                        $this->helpers->redirect('/buildings');
                        return;
                    }
                    break;
                default:
                    $this->flash->flash('error', 'Vous n\'avez pas les permissions nécessaires.');
                    $this->helpers->redirect('/dashboard');
                    return;
            }
        } catch (PDOException $e) {
            $this->logger->error("Erreur lors du chargement des données : " . $e->getMessage());
            $this->flash->flash('error', 'Une erreur est survenue lors du chargement des données.');
        }

        $csrf_token = $this->helpers->generateCsrfToken('buildings.store');
        $title = 'Ajouter un bâtiment';
        $content_view = 'admin/buildings/create.php';
        require_once dirname(__DIR__, 1) . '/Views/layouts/admin_layout.php';
    }

    /**
     * Enregistre un nouveau bâtiment avec validation.
     */
    public function store()
    {
        if (!$this->auth->check()) {
            $this->flash->flash('error', 'Vous devez être connecté pour accéder à cette page.');
            $this->helpers->redirect('/auth/login');
            return;
        }
    
        $token = $_POST['csrf_token'] ?? '';
        if (!$this->helpers->verifyCsrfToken('buildings.store', $token)) {
            $this->flash->flash('error', 'Jeton CSRF invalide.');
            $this->helpers->redirect('/buildings/create');
            return;
        }
    
        $user = $this->auth->user();
        $userObj = User::findById($user['id']);
        $role = $userObj && $userObj->role() ? $userObj->role()->getName() : 'guest';
        $errors = [];
    
        $data = [
            'agency_id' => (int) ($_POST['agency_id'] ?? 0),
            'owner_id' => (int) ($_POST['owner_id'] ?? 0),
            'name' => trim($_POST['name'] ?? ''),
            'city' => trim($_POST['city'] ?? ''),
            'neighborhood' => trim($_POST['neighborhood'] ?? ''),
            'country' => trim($_POST['country'] ?? ''),
            'floors' => (int) ($_POST['floors'] ?? 0),
            'apartment_count' => (int) ($_POST['apartment_count'] ?? 0),
            'land_area' => (float) ($_POST['land_area'] ?? 0),
            'parking' => trim($_POST['parking'] ?? ''),
            'type_id' => (int) ($_POST['type_id'] ?? 0),
            'year_built' => (int) ($_POST['year_built'] ?? 0),
            'status' => trim($_POST['status'] ?? ''),
            'price' => (float) ($_POST['price'] ?? 0)
        ];
    
        if (!Agency::find($data['agency_id'])) {
            $errors[] = 'L’agence sélectionnée est invalide.';
        }
        $owner = Owner::findById($data['owner_id']);
        if (!$owner) {
            $errors[] = 'Le propriétaire sélectionné est invalide.';
        }
        if (empty($data['name']) || strlen($data['name']) < 3) {
            $errors[] = 'Le nom du bâtiment doit contenir au moins 3 caractères.';
        }
        if (empty($data['city'])) {
            $errors[] = 'La ville est requise.';
        }
        if (empty($data['country'])) {
            $errors[] = 'Le pays est requise.';
        }
        if ($data['floors'] < 0) {
            $errors[] = 'Le nombre d’étages doit être positif.';
        }
        if ($data['apartment_count'] < 0) {
            $errors[] = 'Le nombre d’appartements doit être positif.';
        }
        if ($data['land_area'] < 0) {
            $errors[] = 'La superficie du terrain doit être positive.';
        }
        if (!in_array($data['parking'], ['aucun', 'souterrain', 'exterieur', 'couvert'])) {
            $errors[] = 'Le type de parking est invalide.';
        }
        if (!BuildingType::find($data['type_id'])) {
            $errors[] = 'Le type de bâtiment est invalide.';
        }
        if ($data['year_built'] && ($data['year_built'] < 1800 || $data['year_built'] > date('Y') + 5)) {
            $errors[] = 'L’année de construction est invalide.';
        }
        if (!in_array($data['status'], ['disponible', 'vendu', 'en_construction', 'en_renovation'])) {
            $errors[] = 'Le statut est invalide.';
        }
        if ($data['price'] < 0) {
            $errors[] = 'Le prix doit être positif.';
        }
    
        if ($role === 'admin' || $role === 'agent') {
            if ($data['agency_id'] !== $user['agency_id']) {
                $errors[] = 'Vous n’êtes pas autorisé à ajouter un bâtiment pour cette agence.';
            }
        }
    
        if (!empty($errors)) {
            $this->flash->flash('error', implode('<br>', $errors));
            $this->helpers->redirect('/buildings/create');
            return;
        }
    
        $pdo = Database::getInstance();
        try {
            $pdo->beginTransaction();
    
            $buildingData = [
                'agency_id' => $data['agency_id'],
                'agent_id' => ($role === 'agent') ? $user['id'] : null,
                'owner_id' => $data['owner_id'],
                'name' => $data['name'],
                'city' => $data['city'],
                'neighborhood' => $data['neighborhood'] ?: null,
                'country' => $data['country'],
                'floors' => $data['floors'],
                'apartment_count' => $data['apartment_count'],
                'land_area' => $data['land_area'] ?: null,
                'parking' => $data['parking'],
                'type_id' => $data['type_id'],
                'year_built' => $data['year_built'] ?: null,
                'status' => $data['status'],
                'price' => $data['price'] ?: null
            ];
    
            $building = Building::create($buildingData);
    
            // Gestion de l'image
            if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $uploadDir = __DIR__ . '/../../public/assets/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $imageName = uniqid() . '_' . basename($_FILES['image']['name']);
                $imagePath = $uploadDir . $imageName;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
                    $imageData = [
                        'entity_type' => 'building',
                        'entity_id' => $building->getId(),
                        'path' => $imageName,
                        'alt_text' => $data['name'] . ' image',
                        'order' => 1
                    ];
                    \App\Models\Images::create($imageData);
                } else {
                    $this->logger->warning('Échec du déplacement du fichier uploadé pour le bâtiment ID: ' . $building->getId());
                }
            }
    
            $pdo->commit();
            $this->logger->info('Bâtiment créé avec succès.');
            $this->flash->flash('success', 'Bâtiment ajouté avec succès.');
            $this->helpers->redirect('/buildings');
        } catch (PDOException $e) {
            $pdo->rollBack();
            $this->logger->error("Erreur lors de l’ajout du bâtiment : " . $e->getMessage());
            $this->flash->flash('error', 'Une erreur est survenue lors de l’ajout du bâtiment.');
            $this->helpers->redirect('/buildings/create');
        }
    }

    /**
     * Affiche les détails d’un bâtiment spécifique.
     */
    public function show(int $id)
    {
        if (!$this->auth->check()) {
            $this->flash->flash('error', 'Vous devez être connecté pour accéder à cette page.');
            $this->helpers->redirect('/auth/login');
            return;
        }

        $user = $this->auth->user();
        $userObj = User::findById($user['id']);
        $role = $userObj && $userObj->role() ? $userObj->role()->getName() : 'guest';

        try {
            $building = Building::findById($id);
            if (!$building || $building->getIsDeleted()) {
                $this->flash->flash('error', 'Bâtiment introuvable.');
                $this->helpers->redirect('/buildings');
                return;
            }

            if (!$this->canManageBuilding($user, $building)) {
                $this->flash->flash('error', 'Vous n’êtes pas autorisé à voir ce bâtiment.');
                $this->helpers->redirect('/buildings');
                return;
            }

            $buildingType = BuildingType::find($building->getTypeId());
            $agency = Agency::find($building->getAgencyId());
            $owner = $building->owner(); // Utilisation de la relation plutôt que findById
            $ownerUser = $owner ? $owner->user() : null;

            $buildingData = [
                'id' => $building->getId(),
                'name' => $building->getName() ?? 'N/A',
                'city' => $building->getCity() ?? 'N/A',
                'neighborhood' => $building->getNeighborhood() ?? 'Non spécifié',
                'country' => $building->getCountry() ?? 'N/A',
                'floors' => $building->getFloors() ?? 0,
                'apartment_count' => $building->getApartmentCount() ?? 0,
                'land_area' => $building->getLandArea() ? number_format($building->getLandArea(), 2, ',', ' ') : 'Non spécifié',
                'parking' => ucfirst($building->getParking() ?? 'aucun'),
                'type' => $buildingType ? $buildingType->getName() : 'Non spécifié',
                'year_built' => $building->getYearBuilt() ?: 'Non spécifié',
                'status' => ucfirst($building->getStatus() ?? 'inconnu'),
                'price' => number_format($building->getPrice() ?? 0, 2, ',', ' ') . ' €',
                'agency' => $agency ? $agency->getName() : 'Inconnue',
                'owner' => $ownerUser ? ($ownerUser->getFirstName() . ' ' . $ownerUser->getLastName()) : 'Inconnu',
                'created_at' => date('d/m/Y', strtotime($building->getCreatedAt()))
            ];

            $title = 'Détails du bâtiment';
            $content_view = 'admin/buildings/show.php';
            $building_data = $buildingData;
            require_once dirname(__DIR__, 1) . '/Views/layouts/admin_layout.php';
        } catch (PDOException $e) {
            $this->logger->error("Erreur lors du chargement du bâtiment : " . $e->getMessage());
            $this->flash->flash('error', 'Une erreur est survenue lors du chargement du bâtiment.');
            $this->helpers->redirect('/buildings');
        }
    }

    /**
     * Affiche le formulaire de modification d’un bâtiment.
     */
    public function edit(int $id)
    {
        if (!$this->auth->check()) {
            $this->flash->flash('error', 'Vous devez être connecté pour accéder à cette page.');
            $this->helpers->redirect('/auth/login');
            return;
        }

        $user = $this->auth->user();
        $userObj = User::findById($user['id']);
        $role = $userObj && $userObj->role() ? $userObj->role()->getName() : 'guest';

        if (!in_array($role, ['superadmin', 'admin', 'agent'])) {
            $this->flash->flash('error', 'Vous n\'avez pas les permissions nécessaires.');
            $this->helpers->redirect('/403');
            return;
        }

        $building = Building::find($id);
        if (!$building) {
            $this->flash->flash('error', 'Bâtiment non trouvé.');
            $this->helpers->redirect('/buildings');
            return;
        }

        // Générer et stocker explicitement le jeton CSRF dans la session
        $csrf_token = $this->helpers->generateCsrfToken('buildings.update');
        $_SESSION['csrf_tokens']['buildings.update'] = $csrf_token;
        $this->logger->info('Jeton CSRF généré et stocké pour edit (buildings.update): ' . $csrf_token);

        $agencies = [];
        $owners = [];
        $buildingTypes = [];
        try {
            $rawAgencies = Agency::all() ?? [];
            $this->logger->info('Données brutes agencies avant filtre : ' . json_encode($rawAgencies));
            $agencies = array_values(array_filter($rawAgencies, function ($agency) {
                return $agency instanceof Agency && $agency->getId() !== null && $agency->getName() !== null;
            }));
            $this->logger->info('Données filtrées agencies : ' . json_encode($agencies));
            $rawOwners = Owner::all() ?? [];
            $owners = array_values(array_filter($rawOwners, function ($owner) {
                return $owner instanceof Owner && $owner->getId() !== null;
            }));
            $rawBuildingTypes = BuildingType::all() ?? [];
            $buildingTypes = array_values(array_filter($rawBuildingTypes, function ($type) {
                return $type instanceof BuildingType && $type->getId() !== null && $type->getName() !== null;
            }));

            if (empty($agencies)) {
                $this->logger->warning('Aucune agence valide trouvée après filtrage.');
                if ($this->flash) {
                    $this->flash->flash('warning', 'Aucune agence disponible pour le moment.');
                }
            }
        } catch (Exception $e) {
            $this->logger->error('Erreur lors du chargement des données : ' . $e->getMessage());
            if ($this->flash) {
                $this->flash->flash('error', 'Une erreur est survenue lors du chargement des données.');
            }
            $this->helpers->redirect('/buildings');
            return;
        }

        // Vérification finale avant la vue
        if (!is_array($agencies) || empty($agencies)) {
            $agencies = [];
            $this->logger->warning('Forçage de $agencies à un tableau vide en raison de données invalides.');
            if ($this->flash) {
                $this->flash->flash('warning', 'Aucune agence disponible pour le moment.');
            }
        } else {
            foreach ($agencies as $index => $agency) {
                if (!($agency instanceof Agency) || $agency->getId() === null || $agency->getName() === null) {
                    unset($agencies[$index]);
                    $this->logger->error("Agence invalide détectée à l'index $index : " . json_encode($agency));
                }
            }
            $agencies = array_values($agencies);
            if (empty($agencies)) {
                $this->logger->warning('Toutes les agences ont été filtrées comme invalides.');
                if ($this->flash) {
                    $this->flash->flash('warning', 'Aucune agence disponible pour le moment.');
                }
            } else {
                foreach ($agencies as $agency) {
                    if (!method_exists($agency, 'getId') || !method_exists($agency, 'getName')) {
                        $this->logger->error('Agence sans méthode getId ou getName détectée : ' . json_encode($agency));
                        $agencies = [];
                        if ($this->flash) {
                            $this->flash->flash('warning', 'Une erreur interne a corrompu les données des agences.');
                        }
                        break;
                    }
                }
            }
        }

        $title = 'Modifier le bâtiment';
        $content_view = 'admin/buildings/edit.php';
        $flashInstance = $this->flash ?: new stdClass(); // Placeholder, à remplacer par la classe réelle si connue
        extract([
            'building' => $building,
            'agencies' => $agencies,
            'owners' => $owners,
            'buildingTypes' => $buildingTypes,
            'csrf_token' => $csrf_token,
            'helpers' => $this->helpers,
            'flash' => $flashInstance
        ]);
        require_once dirname(__DIR__, 1) . '/Views/layouts/admin_layout.php';
    }
    public function delete(int $id)
    {
        if (!$this->auth->check()) {
            return $this->jsonResponse(['success' => false, 'error' => 'Vous devez être connecté.']);
        }
    
        // Récupérer le jeton CSRF depuis l'en-tête ou le corps
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!$this->helpers->verifyCsrfToken('buildings.delete', $token)) {
            return $this->jsonResponse(['success' => false, 'error' => 'Jeton CSRF invalide.']);
        }
    
        $user = $this->auth->user();
        $userObj = User::findById($user['id']);
        $role = $userObj && $userObj->role() ? $userObj->role()->getName() : 'guest';
    
        $pdo = Database::getInstance();
        try {
            $pdo->beginTransaction();
    
            $building = Building::findById($id);
            if (!$building || $building->getIsDeleted()) {
                $pdo->rollBack();
                return $this->jsonResponse(['success' => false, 'error' => 'Bâtiment introuvable.']);
            }
    
            if (!$this->canManageBuilding($user, $building)) {
                $pdo->rollBack();
                return $this->jsonResponse(['success' => false, 'error' => 'Vous n’êtes pas autorisé.']);
            }
    
            Building::delete($id);
            $pdo->commit();
            $this->logger->info('Bâtiment supprimé avec succès. ID: ' . $id);
            return $this->jsonResponse(['success' => true]);
        } catch (PDOException $e) {
            $pdo->rollBack();
            $this->logger->error("Erreur lors de la suppression du bâtiment (ID: $id): " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'error' => 'Erreur serveur.']);
        }
    }
    
    private function jsonResponse(array $data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    /**
     * Vérifie si l’utilisateur connecté peut gérer un bâtiment.
     */
    private function canManageBuilding($user, $building)
    {
        $userObj = User::findById($user['id']);
        $role = $userObj && $userObj->role() ? $userObj->role()->getName() : 'guest';
        return match ($role) {
            'superadmin' => true,
            'admin' => $user['agency_id'] === $building->getAgencyId(),
            'agent' => $user['agency_id'] === $building->getAgencyId() && $user['id'] === $building->getAgentId(),
            default => false
        };
    }

    public function update(int $id)
    {
        if (!$this->auth->check()) {
            $this->flash->flash('error', 'Vous devez être connecté pour accéder à cette page.');
            $this->helpers->redirect('/auth/login');
            return;
        }
    
        $user = $this->auth->user();
        $userObj = User::findById($user['id']);
        $role = $userObj && $userObj->role() ? $userObj->role()->getName() : 'guest';
    
        if (!in_array($role, ['superadmin', 'admin', 'agent'])) {
            $this->flash->flash('error', 'Vous n\'avez pas les permissions nécessaires.');
            $this->helpers->redirect('/403');
            return;
        }
    
        $token = $_POST['csrf_token'] ?? '';
        if (!$this->helpers->verifyCsrfToken('buildings.update', $token)) {
            $this->logger->error('Jeton CSRF invalide dans update pour bâtiment ID: ' . $id, [
                'received_token' => $token,
                'expected_token' => $_SESSION['csrf_tokens']['buildings.update'] ?? 'non défini',
                'post_data' => $_POST
            ]);
            $this->flash->flash('error', 'Jeton CSRF invalide. Veuillez réessayer.');
            $this->helpers->redirect('/buildings/edit/' . $id);
            return;
        }
    
        $building = Building::find($id);
        if (!$building) {
            $this->flash->flash('error', 'Bâtiment non trouvé.');
            $this->helpers->redirect('/buildings');
            return;
        }
    
        if (!$this->canManageBuilding($user, $building)) {
            $this->flash->flash('error', 'Vous n’êtes pas autorisé à modifier ce bâtiment.');
            $this->helpers->redirect('/buildings');
            return;
        }
    
        $data = [
            'agency_id' => (int) ($_POST['agency_id'] ?? $building->getAgencyId()),
            'owner_id' => (int) ($_POST['owner_id'] ?? $building->getOwnerId()),
            'name' => trim($_POST['name'] ?? $building->getName()),
            'city' => trim($_POST['city'] ?? $building->getCity()),
            'neighborhood' => trim($_POST['neighborhood'] ?? $building->getNeighborhood()),
            'country' => trim($_POST['country'] ?? $building->getCountry()),
            'floors' => (int) ($_POST['floors'] ?? $building->getFloors()),
            'apartment_count' => (int) ($_POST['apartment_count'] ?? $building->getApartmentCount()),
            'land_area' => (float) ($_POST['land_area'] ?? $building->getLandArea()),
            'parking' => trim($_POST['parking'] ?? $building->getParking()),
            'type_id' => (int) ($_POST['type_id'] ?? $building->getTypeId()),
            'year_built' => (int) ($_POST['year_built'] ?? $building->getYearBuilt()),
            'status' => trim($_POST['status'] ?? $building->getStatus()),
            'price' => (float) ($_POST['price'] ?? $building->getPrice())
        ];
    
        $errors = [];
        if (empty($data['name']) || strlen($data['name']) < 3) {
            $errors[] = 'Le nom du bâtiment doit contenir au moins 3 caractères.';
        }
        if (empty($data['city'])) {
            $errors[] = 'La ville est requise.';
        }
        if (empty($data['country'])) {
            $errors[] = 'Le pays est requis.';
        }
        if ($data['floors'] < 0) {
            $errors[] = 'Le nombre d’étages doit être positif.';
        }
        if ($data['apartment_count'] < 0) {
            $errors[] = 'Le nombre d’appartements doit être positif.';
        }
        if ($data['land_area'] < 0) {
            $errors[] = 'La superficie du terrain doit être positive.';
        }
        if (!in_array($data['parking'], ['aucun', 'souterrain', 'exterieur', 'couvert'])) {
            $errors[] = 'Le type de parking est invalide.';
        }
        if (!BuildingType::find($data['type_id'])) {
            $errors[] = 'Le type de bâtiment est invalide.';
        }
        if ($data['year_built'] && ($data['year_built'] < 1800 || $data['year_built'] > date('Y') + 5)) {
            $errors[] = 'L’année de construction est invalide.';
        }
        if (!in_array($data['status'], ['disponible', 'vendu', 'en_construction', 'en_renovation'])) {
            $errors[] = 'Le statut est invalide.';
        }
        if ($data['price'] < 0) {
            $errors[] = 'Le prix doit être positif.';
        }
    
        if (!empty($errors)) {
            $this->flash->flash('error', implode('<br>', $errors));
            $this->helpers->redirect('/buildings/edit/' . $id);
            return;
        }
    
        try {
            $building->update($data);
            $this->flash->flash('success', 'Bâtiment modifié avec succès.');
            $this->helpers->redirect('/buildings');
        } catch (Exception $e) {
            $this->logger->error('Erreur lors de la mise à jour du bâtiment ID: ' . $id . ': ' . $e->getMessage());
            $this->flash->flash('error', 'Une erreur est survenue lors de la modification du bâtiment.');
            $this->helpers->redirect('/buildings/edit/' . $id);
        }
    }
}