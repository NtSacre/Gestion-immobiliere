<?php

namespace App\Controllers;

use App\Utils\Auth;
use App\Utils\Logger;
use App\Utils\Helpers;
use App\Utils\Flash;
use App\Models\Building;
use App\Models\BuildingType;
use App\Models\User;
use App\Models\Images;
use App\Models\Agency;
use App\Models\Owner;
use App\Config\Database;
use App\Utils\Audit;
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
     * Vérifie si l'utilisateur peut gérer un bâtiment.
     * @param Building $building
     * @param array $user
     * @return bool
     */
    protected function canManageBuilding($building, $user): bool
    {
        return match ($user['role'] ?? 'guest') {
            'superadmin' => true,
            'admin' => $user['agency_id'] === $building->getAgencyId(),
            'agent' => $user['agency_id'] === $building->getAgencyId() && $user['id'] === $building->getAgentId(),
            default => false
        };
    }

    /**
     * Affiche la liste des bâtiments avec recherche et pagination.
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
        $buildings = [];
        $totalBuildings = 0;
        $search = trim($_GET['search'] ?? '');
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = 10; // Nombre de bâtiments par page
        $offset = ($page - 1) * $limit;
        $statuses = array_filter($_GET['statuses'] ?? [], fn($status) => in_array($status, ['disponible', 'vendu', 'en_construction', 'en_renovation']));
        $imageCache = [];

        try {
            switch ($role) {
                case 'superadmin':
                    $buildings = Building::getAll($search, $limit, $offset, $statuses);
                    $totalBuildings = Building::countAll($search, $statuses);
                    break;
                case 'admin':
                    if ($user['agency_id']) {
                        $buildings = Building::findByAgencyId($user['agency_id'], $search, $limit, $offset, $statuses);
                        $totalBuildings = Building::countAll($search, $statuses);
                    } else {
                        $this->flash->flash('error', 'Aucune agence associée à votre compte.');
                        $this->helpers->redirect('/dashboard');
                        return;
                    }
                    break;
                case 'agent':
                    if ($user['agency_id']) {
                        $buildings = Building::findByAgentId($user['id'], $user['agency_id'], $search, $limit, $offset, $statuses);
                        $totalBuildings = Building::countAll($search, $statuses);
                    } else {
                        $this->flash->flash('error', 'Aucune agence associée à votre compte.');
                        $this->helpers->redirect('/dashboard');
                        return;
                    }
                    break;
                default:
                    $this->flash->flash('error', 'Rôle non autorisé.');
                    $this->helpers->redirect('/dashboard');
                    return;
            }

            // Récupérer l'image principale pour chaque bâtiment
            foreach ($buildings as $building) {
                $images = Images::findByEntity('building', $building->getId());
                $imageCache[$building->getId()] = !empty($images) ? $images[0]->getPath() : '/assets/images/buildings/default-building.jpg';
                // Ajoute la propriété pour gérer les permissions dans la vue
                $building->setCanManage($this->canManageBuilding($building, $user));
            }
        } catch (PDOException $e) {
            $this->logger->error("Erreur lors de la récupération des bâtiments : " . $e->getMessage());
            $this->flash->flash('error', 'Une erreur est survenue lors du chargement des bâtiments.');
        }

        $totalPages = ceil($totalBuildings / $limit);
        $title = 'Liste des bâtiments';
        $content_view = 'admin/buildings/index.php';
        require_once dirname(__DIR__, 1) . '/Views/layouts/admin_layout.php';
    }

    /**
     * Affiche un  bâtiments.
     */
    public function show(int $id)
    {
        // Vérification d'authentification
        if (!$this->auth->check()) {
            $this->flash->flash('error', 'Vous devez être connecté pour accéder à cette page.');
            $this->helpers->redirect('/auth/login');
            return;
        }

        $user = $this->auth->user();
        $role = $user['role'] ?? 'guest';

        // Récupération du bâtiment
        $building = Building::find($id);
        if (!$building) {
            $this->flash->flash('error', "Bâtiment introuvable.");
            return $this->helpers->redirect('/admin/buildings');
        }
        try {
            //code...

            // Vérification des droits d’accès (admin ou agent de la même agence)
            if ($role !== 'admin') {
                $agencyId = $building->getAgencyId();
                $userAgencyId = $user['agency_id'];
                if (!$agencyId || $agencyId !== $userAgencyId) {
                    $this->flash->flash('error', "Vous n’avez pas l’autorisation d’accéder à ce bâtiment.");
                    return $this->helpers->redirect('/admin/buildings');
                }
            }

            // Chargement des relations
            $owner = Owner::find($building->getOwnerId());
            $ownerUser = $owner ? $owner->user() : null;

            $type = BuildingType::find($building->getTypeId());
            $agency = Agency::find($building->getAgencyId());

            $images = Images::findByEntity('building', $building->getId());

            // Envoi à la vue
            $buildingData = [
                'building' => $building,
                'owner' => $owner,
                'ownerUser' => $ownerUser,
                'type' => $type,
                'agency' => $agency,
                'images' => $images
            ];

            // var_dump($buildingData);

            // Définir les variables pour la vue
            $title = 'Détails du batiment';
            $content_view = 'admin/buildings/show.php';
            $building_data = $buildingData; // Passer les données à la vue

            // Charger le layout
            require_once dirname(__DIR__, 1) . '/Views/layouts/admin_layout.php';
        } catch (PDOException $e) {
            $this->logger->error("Erreur lors du chargement du batiment : " . $e->getMessage(), [
                'building_id' => $id,
                'error' => $e->getMessage()
            ]);
            $this->flash->flash('error', 'Une erreur est survenue lors du chargement du batiment.');
            $this->helpers->redirect('/buildings');
        }
    }


    /**
     * Affiche le formulaire de création d'un bâtiment.
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
        $buildingTypes = [];
        $agents = [];
        $owners = [];
        $agencies = [];
        $allowedStatuses = ['disponible', 'vendu', 'en_construction', 'en_renovation'];

        try {
            switch ($role) {
                case 'superadmin':
                    $buildingTypes = BuildingType::get();
                    $agents = User::getByAgency(null, '', 1000, 0, ['agent']);
                    $owners = User::getByAgency(null, '', 1000, 0, ['proprietaire']);
                    $agencies = Agency::get();
                    break;
                case 'admin':
                    if ($user['agency_id']) {
                        $buildingTypes = BuildingType::get();
                        $agents = User::getByAgency($user['agency_id'], '', 1000, 0, ['agent']);
                        $owners = Owner::findByAgencyId($user['agency_id']);
                    } else {
                        $this->flash->flash('error', 'Aucune agence associée à votre compte.');
                        $this->helpers->redirect('/dashboard');
                        return;
                    }
                    break;
                case 'agent':
                    if ($user['agency_id']) {
                        $buildingTypes = BuildingType::get();
                        $owners = User::getByAgent($user['id'], $user['agency_id'], '', 1000, 0, ['proprietaire']);
                    } else {
                        $this->flash->flash('error', 'Aucune agence associée à votre compte.');
                        $this->helpers->redirect('/dashboard');
                        return;
                    }
                    break;
                default:
                    $this->flash->flash('error', 'Rôle non autorisé.');
                    $this->helpers->redirect('/dashboard');
                    return;
            }
        } catch (PDOException $e) {
            $this->logger->error("Erreur lors du chargement des données : " . $e->getMessage());
            $this->flash->flash('error', 'Une erreur est survenue lors du chargement des données.');
        }
        $parkingOptions = [
        'aucun' => 'Aucun',
        'souterrain' => 'Souterrain',
        'exterieur' => 'Extérieur',
        'couvert' => 'Couvert',
    ];


        $csrf_token = $this->helpers->csrf_token('buildings.store');
        $title = 'Ajouter un bâtiment';
        $content_view = 'admin/buildings/create.php';
        require_once dirname(__DIR__, 1) . '/Views/layouts/admin_layout.php';
    }


    /**
     * Enregistre un nouveau bâtiment avec validation et transaction.
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

        // Récupération des données du formulaire
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'city' => trim($_POST['city'] ?? ''),
            'neighborhood' => trim($_POST['neighborhood'] ?? ''),
            'country' => trim($_POST['country'] ?? ''),
            'floors' => (int) ($_POST['floors'] ?? 0),
            'apartment_count' => (int) ($_POST['apartment_count'] ?? 0),
            'land_area' => !empty($_POST['land_area']) ? (float) ($_POST['land_area']) : null,
            'parking' => isset($_POST['parking']) ? 1 : 0,
            'type_id' => (int) ($_POST['type_id'] ?? 0),
            'year_built' => !empty($_POST['year_built']) ? (int) ($_POST['year_built']) : null,
            'status' => trim($_POST['status'] ?? ''),
            'price' => !empty($_POST['price']) ? (float) ($_POST['price']) : null,
            'agency_id' => isset($_POST['agency_id']) && $_POST['agency_id'] !== ''
                ? (int) $_POST['agency_id']
                : ($role === 'superadmin' || $role === 'admin' ? ($user['agency_id'] ?? 0) : 0), // fallback si superadmin
            'agent_id' => isset($_POST['agent_id']) && $_POST['agent_id'] !== ''
                ? (int) $_POST['agent_id']
                : ($role === 'superadmin' || $role === 'admin' ? $user['id'] : 0), // fallback si superadmin
            'owner_id' => (int) ($_POST['owner_id'] ?? 0),
            'images' => $_FILES['images'] ?? []
        ];


        // Validation des champs requis
        if (empty($data['name']) || strlen($data['name']) < 3) {
            $errors[] = 'Le nom du bâtiment doit contenir au moins 3 caractères.';
        }
        if (empty($data['city'])) {
            $errors[] = 'La ville est obligatoire.';
        }
        if (empty($data['country'])) {
            $errors[] = 'Le pays est obligatoire.';
        }
        if ($data['floors'] < 0) {
            $errors[] = 'Le nombre d’étages ne peut pas être négatif.';
        }
        if ($data['apartment_count'] < 0) {
            $errors[] = 'Le nombre d’appartements ne peut pas être négatif.';
        }


        if (!empty($data['land_area']) && $data['land_area'] <= 0) {
            $errors[] = 'La superficie du terrain doit être positive.';
        }
        if (!BuildingType::find($data['type_id'])) {
            $errors[] = 'Le type de bâtiment sélectionné est invalide.';
        }
        if (!in_array($data['status'], ['disponible', 'vendu', 'en_construction', 'en_renovation'])) {
            $errors[] = 'Le statut est invalide.';
        }
        if (!empty($data['year_built']) && ($data['year_built'] < 1800 || $data['year_built'] > date('Y') + 1)) {
            $errors[] = 'L’année de construction est invalide.';
        }
        if (!empty($data['price']) && $data['price'] <= 0) {
            $errors[] = 'Le prix doit être positif.';
        }
        if (!Owner::find($data['owner_id'])) {
            $errors[] = 'Le propriétaire sélectionné est invalide.';
        }

        // 🔐 Vérification des permissions selon le rôle
        if ($role === 'superadmin') {
            if (!$data['agency_id'] || !Agency::find($data['agency_id'])) {
                $errors[] = 'L’agence est invalide ou manquante.';
            }
            if (!$data['agent_id'] || !User::find($data['agent_id'])) {
                $errors[] = 'L’agent est invalide ou manquant.';
            }
        } elseif ($role === 'admin') {
            // if ($data['agency_id'] !== $user['agency_id']) {
            //     $errors[] = 'Vous ne pouvez associer ce bâtiment qu’à votre agence.';
            // }
            if ($data['agent_id']) {
                $agent = User::find($data['agent_id']);
                if (
                    !$agent ||
                    $agent->getAgencyId() !== $user['agency_id'] ||
                    !in_array($agent->role()->getName(), ['agent', 'admin', 'superadmin'])
                ) {
                    $errors[] = 'L’agent sélectionné n’appartient pas à votre agence ou n’a pas un rôle autorisé.';
                }
            }
            $owner = Owner::find($data['owner_id']);
            if ($owner && $owner->getAgencyId() !== $user['agency_id']) {
                $errors[] = 'Le propriétaire sélectionné n’appartient pas à votre agence.';
            }
        } elseif ($role === 'agent') {
            if ($data['agency_id'] !== $user['agency_id']) {
                $errors[] = 'Vous ne pouvez associer ce bâtiment qu’à votre agence.';
            }
            if ($data['agent_id'] !== $user['id']) {
                $errors[] = 'Vous ne pouvez vous associer qu’à vous-même comme agent.';
            }
            $owner = Owner::find($data['owner_id']);
            if ($owner && $owner->getAgentId() !== $user['id']) {
                $errors[] = 'Vous ne pouvez associer ce bâtiment qu’à un propriétaire que vous gérez.';
            }
        } else {
            $errors[] = 'Vous n’êtes pas autorisé à créer un bâtiment.';
        }

        // 📷 Validation des images
        if (!empty($data['images']['name'][0])) {
            $maxImages = 4;
            $validImageTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $maxFileSize = 5 * 1024 * 1024;

            if (count(array_filter($data['images']['name'])) > $maxImages) {
                $errors[] = 'Vous ne pouvez uploader que 4 images maximum.';
            }

            foreach ($data['images']['name'] as $key => $imageName) {
                if ($imageName) {
                    if (!in_array($data['images']['type'][$key], $validImageTypes)) {
                        $errors[] = "Le fichier $imageName n’est pas une image valide.";
                    }
                    if ($data['images']['size'][$key] > $maxFileSize) {
                        $errors[] = "Le fichier $imageName dépasse la taille maximale autorisée.";
                    }
                    if ($data['images']['error'][$key] !== UPLOAD_ERR_OK) {
                        $errors[] = "Une erreur est survenue lors de l’upload du fichier $imageName.";
                    }
                }
            }
        }

        // 🔴 Retour en cas d’erreur
        if (!empty($errors)) {
            $this->flash->flash('error', implode('<br>', $errors));
            $this->helpers->redirect('/buildings/create');
            return;
        }

        // 💾 Création du bâtiment avec transaction
        $pdo = Database::getInstance();
        try {
            $pdo->beginTransaction();

            $building = Building::create([
                'agency_id' => $data['agency_id'],
                'agent_id' => $data['agent_id'],
                'owner_id' => $data['owner_id'],
                'name' => $data['name'],
                'city' => $data['city'],
                'neighborhood' => $data['neighborhood'],
                'country' => $data['country'],
                'floors' => $data['floors'],
                'apartment_count' => $data['apartment_count'],
                'land_area' => $data['land_area'],
                'parking' => $data['parking'],
                'type_id' => $data['type_id'],
                'year_built' => $data['year_built'],
                'status' => $data['status'],
                'price' => $data['price']
            ]);

            // 🔄 Upload des images
            if (!empty($data['images']['name'][0])) {
                $uploadDir = dirname(__DIR__, 2) . '/public/assets/images/buildings/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                foreach ($data['images']['name'] as $key => $imageName) {
                    if ($imageName) {
                        $ext = pathinfo($imageName, PATHINFO_EXTENSION);
                        $filename = uniqid('building_') . '.' . $ext;
                        $destination = $uploadDir . $filename;

                        if (move_uploaded_file($data['images']['tmp_name'][$key], $destination)) {
                            Images::create([
                                'entity_type' => 'building',
                                'entity_id' => $building->getId(),
                                'path' => '/assets/images/buildings/' . $filename,
                                'alt_text' => 'Image du bâtiment ' . htmlspecialchars($data['name']),
                                'order' => $key + 1
                            ]);
                        } else {
                            throw new PDOException("Erreur lors de l’upload de $imageName");
                        }
                    }
                }
            }

            $pdo->commit();
            // Audit de la création du bâtiment
            Audit::log('create', 'buildings', $building->getId(), $data['agency_id'], null, $data);
            $this->flash->flash('success', 'Bâtiment ajouté avec succès.');
            $this->helpers->redirect('/buildings/create');
        } catch (PDOException $e) {
            $pdo->rollBack();
            $this->logger->error("Erreur lors de l’ajout du bâtiment : " . $e->getMessage());
            $this->flash->flash('error', 'Une erreur est survenue lors de l’ajout du bâtiment.');
            $this->helpers->redirect('/buildings/create');
        }
    }

        /**
     * Affiche le formulaire d’édition d’un bâtiment.
     */
        public function edit(int $id)
        {
            if (!$this->auth->check()) {
                $this->flash->flash('error', 'Vous devez être connecté pour accéder à cette page.');
                $this->helpers->redirect('/auth/login');
                return;
            }

            $building = Building::find($id);
            
            if (!$building) {
                $this->flash->flash('error', 'Bâtiment introuvable.');
                $this->helpers->redirect('/buildings');
                return;
            }
             
            $images = Images::findByEntity('building', $building->getId());
            $user = $this->auth->user();
            $role = $user['role'] ?? 'guest';

            $buildingTypes = [];
            $agents = [];
            $owners = [];
            $agencies = [];
            $allowedStatuses = ['disponible', 'vendu', 'en_construction', 'en_renovation'];

            try {
                switch ($role) {
                    case 'superadmin':
                        $buildingTypes = BuildingType::get();
                        $agents = User::getByAgency(null, '', 1000, 0, ['agent']);
                        $owners = User::getByAgency(null, '', 1000, 0, ['proprietaire']);
                        $agencies = Agency::get();
                        break;
                    case 'admin':
                        if ($user['agency_id']) {
                            $buildingTypes = BuildingType::get();
                            $agents = User::getByAgency($user['agency_id'], '', 1000, 0, ['agent']);
                            $owners = Owner::findByAgencyId($user['agency_id']);
                        } else {
                            $this->flash->flash('error', 'Aucune agence associée à votre compte.');
                            $this->helpers->redirect('/dashboard');
                            return;
                        }
                        break;
                    case 'agent':
                        if ($user['agency_id']) {
                            $buildingTypes = BuildingType::get();
                            $owners = User::getByAgent($user['id'], $user['agency_id'], '', 1000, 0, ['proprietaire']);
                        } else {
                            $this->flash->flash('error', 'Aucune agence associée à votre compte.');
                            $this->helpers->redirect('/dashboard');
                            return;
                        }
                        break;
                    default:
                        $this->flash->flash('error', 'Rôle non autorisé.');
                        $this->helpers->redirect('/dashboard');
                        return;
                }
            } catch (PDOException $e) {
                $this->logger->error("Erreur lors du chargement des données : " . $e->getMessage());
                $this->flash->flash('error', 'Erreur lors du chargement des données.');
                $this->helpers->redirect('/buildings');
                return;
            }

                    $parkingOptions = [
        'aucun' => 'Aucun',
        'souterrain' => 'Souterrain',
        'exterieur' => 'Extérieur',
        'couvert' => 'Couvert',
    ];

            $csrf_token = $this->helpers->csrf_token("buildings.update");
            $title = 'Modifier un bâtiment';
            $content_view = 'admin/buildings/edit.php';
            require_once dirname(__DIR__, 1) . '/Views/layouts/admin_layout.php';
        }


        /**
     * Met à jour les données du bâtiment.
     */
    public function update(int $id)
    {
        // Vérification de la connexion
        if (!$this->auth->check()) {
            $this->flash->flash('error', 'Vous devez être connecté pour accéder à cette page.');
            $this->helpers->redirect('/auth/login');
            return;
        }

        // Récupération du bâtiment
        $building = Building::find($id);
        if (!$building) {
            $this->flash->flash('error', 'Bâtiment introuvable.');
            $this->helpers->redirect('/buildings');
            return;
        }

        // Vérification des permissions
        $user = $this->auth->user();
        $role = $user['role'] ?? 'guest';
        if ($role === 'admin' && $building->getAgencyId() !== $user['agency_id']) {
            $this->flash->flash('error', 'Vous n’êtes pas autorisé à modifier ce bâtiment.');
            $this->helpers->redirect('/buildings');
            return;
        }
        if ($role === 'agent' && $building->getAgentId() !== $user['id']) {
            $this->flash->flash('error', 'Vous ne pouvez modifier que les bâtiments que vous gérez.');
            $this->helpers->redirect('/buildings');
            return;
        }

        // Récupération des données du formulaire
        /** @var array{name: string, city: string, neighborhood: string, country: string, floors: int, apartment_count: int, land_area: float|null, parking: string|null, type_id: int, year_built: int|null, status: string, price: float|null, agency_id: int, agent_id: int|null, owner_id: int, images: array, delete_images: array} $data */
        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'city' => trim($_POST['city'] ?? ''),
            'neighborhood' => trim($_POST['neighborhood'] ?? ''),
            'country' => trim($_POST['country'] ?? ''),
            'floors' => (int) ($_POST['floors'] ?? 0),
            'apartment_count' => (int) ($_POST['apartment_count'] ?? 0),
            'land_area' => !empty($_POST['land_area']) ? (float) ($_POST['land_area']) : null,
            'parking' => isset($_POST['parking']) ? $_POST['parking'] : null,
            'type_id' => (int) ($_POST['type_id'] ?? 0),
            'year_built' => !empty($_POST['year_built']) ? (int) ($_POST['year_built']) : null,
            'status' => trim($_POST['status'] ?? ''),
            'price' => !empty($_POST['price']) ? (float) ($_POST['price']) : null,
            'agency_id' => isset($_POST['agency_id']) && $_POST['agency_id'] !== ''
                ? (int) $_POST['agency_id']
                : ($role === 'superadmin' || $role === 'admin' ? ($user['agency_id'] ?? 0) : 0),
            'agent_id' => isset($_POST['agent_id']) && $_POST['agent_id'] !== ''
                ? (int) $_POST['agent_id']
                : ($role === 'superadmin' || $role === 'admin' ? $user['id'] : 0),
            'owner_id' => (int) ($_POST['owner_id'] ?? 0),
            'images' => $_FILES['images'] ?? [],
            'delete_images' => $_POST['delete_images'] ?? []
        ];

        // === VALIDATIONS ===
        $errors = [];
        if (empty($data['name']) || strlen($data['name']) < 3) {
            $errors[] = 'Le nom du bâtiment doit contenir au moins 3 caractères.';
        }
        if (empty($data['city'])) {
            $errors[] = 'La ville est obligatoire.';
        }
        if (empty($data['country'])) {
            $errors[] = 'Le pays est obligatoire.';
        }
        if ($data['floors'] < 0) {
            $errors[] = 'Le nombre d’étages ne peut pas être négatif.';
        }
        if ($data['apartment_count'] < 0) {
            $errors[] = 'Le nombre d’appartements ne peut pas être négatif.';
        }
        if (empty($data['parking'])) {
            $errors[] = 'Le parking est obligatoire.';
        }
        if (!in_array($data['parking'], ['aucun', 'souterrain', 'extérieur', 'couvert'])) {
            $errors[] = 'Le type de parking est invalide.';
        }
        if (!empty($data['land_area']) && $data['land_area'] <= 0) {
            $errors[] = 'La superficie du terrain doit être positive.';
        }
        if (!BuildingType::find($data['type_id'])) {
            $errors[] = 'Le type de bâtiment sélectionné est invalide.';
        }
        if (!in_array($data['status'], ['disponible', 'vendu', 'en_construction', 'en_renovation'])) {
            $errors[] = 'Le statut est invalide.';
        }
        if (!empty($data['year_built']) && ($data['year_built'] < 1800 || $data['year_built'] > date('Y') + 1)) {
            $errors[] = 'L’année de construction est invalide.';
        }
        if (!empty($data['price']) && $data['price'] <= 0) {
            $errors[] = 'Le prix doit être positif.';
        }
        if (!Owner::find($data['owner_id'])) {
            $errors[] = 'Le propriétaire sélectionné est invalide.';
        }

        // Vérifications spécifiques aux rôles
        if ($role === 'superadmin') {
            if (!$data['agency_id'] || !Agency::find($data['agency_id'])) {
                $errors[] = 'L’agence est invalide ou manquante.';
            }
            if ($data['agent_id'] && !User::find($data['agent_id'])) {
                $errors[] = 'L’agent est invalide.';
            }
        } elseif ($role === 'admin') {
            if ($data['agent_id']) {
                $agent = User::find($data['agent_id']);
                if (
                    !$agent ||
                    $agent->getAgencyId() !== $user['agency_id'] ||
                    !in_array($agent->role()->getName(), ['agent', 'admin', 'superadmin'])
                ) {
                    $errors[] = 'L’agent sélectionné n’appartient pas à votre agence ou n’a pas un rôle autorisé.';
                }
            }
            $owner = Owner::find($data['owner_id']);
            if ($owner && $owner->getAgencyId() !== $user['agency_id']) {
                $errors[] = 'Le propriétaire sélectionné n’appartient pas à votre agence.';
            }
        } elseif ($role === 'agent') {
            if ($data['agency_id'] !== $user['agency_id']) {
                $errors[] = 'Vous ne pouvez associer ce bâtiment qu’à votre agence.';
            }
            if ($data['agent_id'] !== $user['id']) {
                $errors[] = 'Vous ne pouvez vous associer qu’à vous-même comme agent.';
            }
            $owner = Owner::find($data['owner_id']);
            if ($owner && !Owner::isCreatedByAgent($user['id'], $owner->getUserId())) {
                $errors[] = 'Vous ne pouvez associer ce bâtiment qu’à un propriétaire que vous gérez.';
            }
        } else {
            $errors[] = 'Vous n’êtes pas autorisé à modifier ce bâtiment.';
        }

        // Validation du nombre total d'images
        $maxImages = 4;
        $existingImages = Images::findByEntity('building', $building->getId());
        $existingImageCount = count($existingImages) - count($data['delete_images']);
        $newImageCount = !empty($data['images']['name'][0]) ? count(array_filter($data['images']['name'])) : 0;
        if ($existingImageCount + $newImageCount > $maxImages) {
            $errors[] = "Le nombre total d’images (existantes + nouvelles) ne peut pas dépasser $maxImages.";
        }

        // Validation des nouvelles images
        if ($newImageCount > 0) {
            $validImageTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $maxFileSize = 5 * 1024 * 1024; // 5MB
            foreach ($data['images']['name'] as $key => $imageName) {
                if ($imageName) {
                    if (!in_array($data['images']['type'][$key], $validImageTypes)) {
                        $errors[] = "Le fichier $imageName n’est pas une image valide (JPEG, PNG, GIF).";
                    }
                    if ($data['images']['size'][$key] > $maxFileSize) {
                        $errors[] = "Le fichier $imageName dépasse la taille maximale de 5Mo.";
                    }
                    if ($data['images']['error'][$key] !== UPLOAD_ERR_OK) {
                        $errors[] = "Une erreur est survenue lors de l’upload du fichier $imageName.";
                    }
                }
            }
        }

        // Si erreurs, redirection avec message flash
        if (!empty($errors)) {
            $this->flash->flash('error', implode('<br>', $errors));
            $this->helpers->redirect("/buildings/edit/$id");
            return;
        }

        // === MISE À JOUR EN BASE + IMAGES ===
        $pdo = Database::getInstance();
        try {
            $pdo->beginTransaction();

            // Mise à jour du bâtiment
            Building::update($id, [
                'agency_id' => $data['agency_id'],
                'agent_id' => $data['agent_id'],
                'owner_id' => $data['owner_id'],
                'name' => $data['name'],
                'city' => $data['city'],
                'neighborhood' => $data['neighborhood'],
                'country' => $data['country'],
                'floors' => $data['floors'],
                'apartment_count' => $data['apartment_count'],
                'land_area' => $data['land_area'],
                'parking' => $data['parking'],
                'type_id' => $data['type_id'],
                'year_built' => $data['year_built'],
                'status' => $data['status'],
                'price' => $data['price']
            ]);

            // Suppression des images marquées pour suppression
            if (!empty($data['delete_images'])) {
                foreach ($data['delete_images'] as $imageId) {
                    $image = Images::find($imageId);
                    if ($image && $image->getEntityType() === 'building' && $image->getEntityId() === $building->getId()) {
                        $filePath = dirname(__DIR__, 2) . '/public' . $image->getPath();
                        if (file_exists($filePath)) {
                            unlink($filePath);
                        }
                        Images::delete($imageId);
                    }
                }
            }

            // Gestion des nouvelles images
            if ($newImageCount > 0) {
                $uploadDir = dirname(__DIR__, 2) . '/public/assets/images/buildings/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                if (!is_writable($uploadDir)) {
                    throw new PDOException("Le répertoire $uploadDir n’est pas accessible en écriture.");
                }

                foreach ($data['images']['name'] as $key => $imageName) {
                    if ($imageName) {
                        $ext = pathinfo($imageName, PATHINFO_EXTENSION);
                        $filename = uniqid('building_') . '.' . $ext;
                        $destination = $uploadDir . $filename;

                        if (move_uploaded_file($data['images']['tmp_name'][$key], $destination)) {
                            Images::create([
                                'entity_type' => 'building',
                                'entity_id' => $building->getId(),
                                'path' => '/assets/images/buildings/' . $filename,
                                'alt_text' => 'Image du bâtiment ' . htmlspecialchars($data['name']),
                                'order' => $key + 1
                            ]);
                        } else {
                            throw new PDOException("Erreur lors de l’upload de $imageName");
                        }
                    }
                }
            }

            $pdo->commit();
            // Audit de la mise à jour du bâtiment
            Audit::log('update', 'buildings', $id, $data['agency_id'], $building->toArray(), $data);
            $this->flash->flash('success', 'Bâtiment mis à jour avec succès.');
            $this->helpers->redirect("/buildings/$id");
        } catch (PDOException $e) {
            $pdo->rollBack();
            $this->logger->error("Erreur lors de la mise à jour du bâtiment ID $id : " . $e->getMessage());
            $this->flash->flash('error', 'Une erreur est survenue lors de la mise à jour du bâtiment.');
            $this->helpers->redirect("/buildings/edit/$id");
        }
    }

    /**
     * Affiche les détails d'un bâtiment.
     */
        /**
     * Supprime un bâtiment (suppression logique) et ses images associées (suppression logique).
     *
     * @param int $id ID du bâtiment
     * @return void
     */
    public function delete(int $id)
    {
        // Vérification de la connexion
        if (!$this->auth->check()) {
            $this->flash->flash('error', 'Vous devez être connecté pour accéder à cette page.');
            $this->helpers->redirect('/auth/login');
            return;
        }

        // Récupération du bâtiment
        $building = Building::find($id);
        if (!$building) {
            $this->flash->flash('error', 'Bâtiment introuvable.');
            $this->helpers->redirect('/buildings');
            return;
        }

        // Vérification des permissions
        $user = $this->auth->user();
        $role = $user['role'] ?? 'guest';
        if ($role === 'admin' && $building->getAgencyId() !== $user['agency_id']) {
            $this->flash->flash('error', 'Vous n’êtes pas autorisé à supprimer ce bâtiment.');
            $this->helpers->redirect('/buildings');
            return;
        }
        if ($role === 'agent' && $building->getAgentId() !== $user['id']) {
            $this->flash->flash('error', 'Vous ne pouvez supprimer que les bâtiments que vous gérez.');
            $this->helpers->redirect('/buildings');
            return;
        }
        if ($role !== 'superadmin' && $role !== 'admin' && $role !== 'agent') {
            $this->flash->flash('error', 'Vous n’êtes pas autorisé à supprimer ce bâtiment.');
            $this->helpers->redirect('/buildings');
            return;
        }

        // Suppression en base avec transaction
        $pdo = Database::getInstance();
        try {
            $pdo->beginTransaction();

            // Suppression logique des images associées
            if (!Images::deleteByEntity('building', $building->getId())) {
                // Note : Pas d'erreur fatale si aucune image n'est affectée, car c'est un cas valide
            }

            // Suppression logique du bâtiment
            if (!Building::delete($id)) {
                throw new PDOException('Échec de la suppression logique du bâtiment.');
            }

            $pdo->commit();
            $this->flash->flash('success', 'Bâtiment supprimé avec succès.');
            $this->helpers->redirect('/buildings');
        } catch (PDOException $e) {
            $pdo->rollBack();
            $this->logger->error("Erreur lors de la suppression du bâtiment ID $id : " . $e->getMessage());
            $this->flash->flash('error', 'Une erreur est survenue lors de la suppression du bâtiment.');
            $this->helpers->redirect("/buildings/edit/$id");
        }
    }

}
