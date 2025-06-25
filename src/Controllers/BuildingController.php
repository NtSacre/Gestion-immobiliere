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
                $imageCache[$building->getId()] = !empty($images) ? $images[0]->getPath() : '/assets/images/default-building.jpg';
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
                    $agents = User::getByAgency(null, '', 1000, 0, ['agent']); // Tous les agents
                    $owners = User::getByAgency(null, '', 1000, 0, ['proprietaire']); // Tous les propriétaires
                    $agencies = Agency::get(); // Supposons que Agency::get() existe
                    break;
                case 'admin':
                    if ($user['agency_id']) {
                        $buildingTypes = BuildingType::get();
                        $agents = User::getByAgency($user['agency_id'], '', 1000, 0, ['agent']);
                        $owners = User::getByAgency($user['agency_id'], '', 1000, 0, ['proprietaire']);
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

        // Génère un jeton CSRF
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

        // Validation des entrées
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
            'agency_id' => (int) ($_POST['agency_id'] ?? 0),
            'agent_id' => (int) ($_POST['agent_id'] ?? 0),
            'owner_id' => (int) ($_POST['owner_id'] ?? 0),
            'images' => $_FILES['images'] ?? []
        ];

        // Validations des champs requis
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
        if (!Owner::findById($data['owner_id'])) {
            $errors[] = 'Le propriétaire sélectionné est invalide.';
        }

        // Validation des permissions pour agency_id, agent_id, owner_id
        if ($role === 'superadmin') {
            if (!Agency::find($data['agency_id'])) {
                $errors[] = 'L’agence sélectionnée est invalide.';
            }
            if ($data['agent_id'] && !User::find($data['agent_id'])) {
                $errors[] = 'L’agent sélectionné est invalide.';
            }
        } elseif ($role === 'admin') {
            if ($data['agency_id'] !== $user['agency_id']) {
                $errors[] = 'Vous ne pouvez associer ce bâtiment qu’à votre agence.';
            }
            if ($data['agent_id']) {
                $agent = User::find($data['agent_id']);
                if (!$agent || $agent->getAgencyId() !== $user['agency_id'] || $agent->role()->getName() !== 'agent') {
                    $errors[] = 'L’agent sélectionné n’appartient pas à votre agence ou n’est pas un agent.';
                }
            }
            $owner = Owner::findById($data['owner_id']);
            if ($owner && $owner->getAgencyId() !== $user['agency_id']) {
                $errors[] = 'Le propriétaire sélectionné n’appartient pas à votre agence.';
            }
        } elseif ($role === 'agent') {
            if ($data['agency_id'] !== $user['agency_id']) {
                $errors[] = 'Vous ne pouvez associer ce bâtiment qu’à votre agence.';
            }
            if ($data['agent_id'] && $data['agent_id'] !== $user['id']) {
                $errors[] = 'Vous ne pouvez vous associer qu’à vous-même comme agent.';
            }
            $owner = Owner::findById($data['owner_id']);
            if ($owner && $owner->getAgentId() !== $user['id']) {
                $errors[] = 'Vous ne pouvez associer ce bâtiment qu’à un propriétaire que vous gérez.';
            }
        } else {
            $errors[] = 'Vous n’êtes pas autorisé à créer un bâtiment.';
        }

        // Validation des images
        if (!empty($data['images']['name'][0])) {
            $maxImages = 4;
            $validImageTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $maxFileSize = 5 * 1024 * 1024; // 5MB

            if (count(array_filter($data['images']['name'])) > $maxImages) {
                $errors[] = 'Vous ne pouvez uploader que 4 images maximum.';
            }

            foreach ($data['images']['name'] as $key => $imageName) {
                if ($imageName) {
                    if (!in_array($data['images']['type'][$key], $validImageTypes)) {
                        $errors[] = 'Le fichier ' . htmlspecialchars($imageName) . ' n’est pas une image valide (JPEG, PNG, GIF).';
                    }
                    if ($data['images']['size'][$key] > $maxFileSize) {
                        $errors[] = 'Le fichier ' . htmlspecialchars($imageName) . ' dépasse la taille maximale de 5MB.';
                    }
                    if ($data['images']['error'][$key] !== UPLOAD_ERR_OK) {
                        $errors[] = 'Une erreur est survenue lors de l’upload de ' . htmlspecialchars($imageName) . '.';
                    }
                }
            }
        }

        if (!empty($errors)) {
            $this->flash->flash('error', implode('<br>', $errors));
            $this->helpers->redirect('/buildings/create');
            return;
        }

        // Création du bâtiment dans une transaction
        $pdo = Database::getInstance();
        try {
            $pdo->beginTransaction();

            // Créer le bâtiment
            $buildingData = [
                'agency_id' => $data['agency_id'],
                'agent_id' => $data['agent_id'] ?: ($role === 'agent' ? $user['id'] : null),
                'owner_id' => $data['owner_id'],
                'name' => $data['name'],
                'city' => $data['city'],
                'neighborhood' => $data['neighborhood'] ?: null,
                'country' => $data['country'],
                'floors' => $data['floors'],
                'apartment_count' => $data['apartment_count'],
                'land_area' => $data['land_area'],
                'parking' => $data['parking'],
                'type_id' => $data['type_id'],
                'year_built' => $data['year_built'],
                'status' => $data['status'],
                'price' => $data['price']
            ];
            $building = Building::create($buildingData);

            // Gérer les images
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
                            throw new PDOException('Échec du déplacement du fichier ' . $imageName);
                        }
                    }
                }
            }

            $pdo->commit();
            $this->flash->flash('success', 'Bâtiment ajouté avec succès.');
            $this->helpers->redirect('/buildings/create');
        } catch (PDOException $e) {
            $pdo->rollBack();
            $this->logger->error("Erreur lors de l’ajout du bâtiment : " . $e->getMessage());
            $this->flash->flash('error', 'Une erreur est survenue lors de l’ajout du bâtiment.');
            $this->helpers->redirect('/buildings/create');
        }
    }
}