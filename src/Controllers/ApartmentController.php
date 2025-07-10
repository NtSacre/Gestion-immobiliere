<?php

namespace App\Controllers;

use App\Utils\Auth;
use App\Utils\Logger;
use App\Utils\Helpers;
use App\Utils\Flash;
use App\Models\Apartment;
use App\Models\ApartmentType;
use App\Models\Building;
use App\Models\User;
use App\Models\Images;
use App\Models\Agency;
use App\Models\Owner;
use App\Config\Database;
use App\Utils\Audit;
use PDOException;

class ApartmentController
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
     * Vérifie si l'utilisateur peut gérer un appartement.
     * @param Apartment $apartment
     * @param array $user
     * @return bool
     */
    protected function canManageApartment($apartment, $user): bool
    {
        return match ($user['role'] ?? 'guest') {
            'superadmin' => true,
            'admin' => $user['agency_id'] === $apartment->getAgencyId(),
            'agent' => $user['agency_id'] === $apartment->getAgencyId() && $user['id'] === $apartment->getAgentId(),
            default => false
        };
    }

    /**
     * Affiche la liste des appartements avec recherche et pagination.
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
        $apartments = [];
        $totalApartments = 0;
        $search = trim($_GET['search'] ?? '');
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $limit = 10; // Nombre d'appartements par page
        $offset = ($page - 1) * $limit;
        $statuses = array_filter($_GET['statuses'] ?? [], fn($status) => in_array($status, ['disponible', 'loué', 'réservé', 'en_maintenance']));
        $imageCache = [];

        try {
            switch ($role) {
                case 'superadmin':
                    $apartments = Apartment::getAll($search, $limit, $offset, $statuses);
                    $totalApartments = Apartment::countAll($search, $statuses);
                    break;
                case 'admin':
                    if ($user['agency_id']) {
                        $apartments = Apartment::findByAgencyId($user['agency_id'], $search, $limit, $offset, $statuses);
                        $totalApartments = Apartment::countAll($search, $statuses);
                    } else {
                        $this->flash->flash('error', 'Aucune agence associée à votre compte.');
                        $this->helpers->redirect('/dashboard');
                        return;
                    }
                    break;
                case 'agent':
                    if ($user['agency_id']) {
                        $apartments = Apartment::findByAgentId($user['id'], $user['agency_id'], $search, $limit, $offset, $statuses);
                        $totalApartments = Apartment::countAll($search, $statuses);
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

            // Récupérer l'image principale pour chaque appartement
            foreach ($apartments as $apartment) {
                $images = Images::findByEntity('apartment', $apartment->getId());
                $imageCache[$apartment->getId()] = !empty($images) ? $images[0]->getPath() : '/assets/images/apartments/default-apartment.jpg';
                // Ajoute la propriété pour gérer les permissions dans la vue
                $apartment->setCanManage($this->canManageApartment($apartment, $user));
            }
        } catch (PDOException $e) {
            $this->logger->error("Erreur lors de la récupération des appartements : " . $e->getMessage());
            $this->flash->flash('error', 'Une erreur est survenue lors du chargement des appartements.');
        }

        $totalPages = ceil($totalApartments / $limit);
        $title = 'Liste des appartements';
        $content_view = 'admin/apartments/index.php';
        require_once dirname(__DIR__, 1) . '/Views/layouts/admin_layout.php';
    }

    /**
     * Affiche les détails d'un appartement.
     */
    public function show(int $id)
    {
        if (!$this->auth->check()) {
            $this->flash->flash('error', 'Vous devez être connecté pour accéder à cette page.');
            $this->helpers->redirect('/auth/login');
            return;
        }

        $user = $this->auth->user();
        $role = $user['role'] ?? 'guest';

        // Récupération de l'appartement
        $apartment = Apartment::find($id);
        if (!$apartment) {
            $this->flash->flash('error', "Appartement introuvable.");
            return $this->helpers->redirect('/admin/apartments');
        }

        try {
            // Vérification des droits d’accès
            if ($role !== 'superadmin') {
                $agencyId = $apartment->getAgencyId();
                $userAgencyId = $user['agency_id'];
                if (!$agencyId || $agencyId !== $userAgencyId) {
                    $this->flash->flash('error', "Vous n’avez pas l’autorisation d’accéder à cet appartement.");
                    return $this->helpers->redirect('/admin/apartments');
                }
            }

            // Chargement des relations
            $owner = Owner::find($apartment->getOwnerId());
            $ownerUser = $owner ? $owner->user() : null;
            $type = ApartmentType::find($apartment->getTypeId());
            $building = Building::find($apartment->getBuildingId());
            $agency = Agency::find($apartment->getAgencyId());
            $images = Images::findByEntity('apartment', $apartment->getId());

            // Envoi à la vue
            $apartmentData = [
                'apartment' => $apartment,
                'owner' => $owner,
                'ownerUser' => $ownerUser,
                'type' => $type,
                'building' => $building,
                'agency' => $agency,
                'images' => $images
            ];

            $title = 'Détails de l’appartement';
            $content_view = 'admin/apartments/show.php';
            $apartment_data = $apartmentData;

            require_once dirname(__DIR__, 1) . '/Views/layouts/admin_layout.php';
        } catch (PDOException $e) {
            $this->logger->error("Erreur lors du chargement de l’appartement : " . $e->getMessage(), [
                'apartment_id' => $id,
                'error' => $e->getMessage()
            ]);
            $this->flash->flash('error', 'Une erreur est survenue lors du chargement de l’appartement.');
            $this->helpers->redirect('/admin/apartments');
        }
    }

    /**
     * Affiche le formulaire de création d’un appartement.
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
        $apartmentTypes = [];
        $buildings = [];
        $agents = [];
        $owners = [];
        $agencies = [];
        $allowedStatuses = ['disponible', 'loué', 'réservé', 'en_maintenance'];

        try {
            switch ($role) {
                case 'superadmin':
                    $apartmentTypes = ApartmentType::get();
                    $buildings = Building::get();
                    $agents = User::getByAgency(null, '', 1000, 0, ['agent']);
                    $owners = User::getByAgency(null, '', 1000, 0, ['proprietaire']);
                    $agencies = Agency::get();
                    break;
                case 'admin':
                    if ($user['agency_id']) {
                        $apartmentTypes = ApartmentType::get();
                        $buildings = Building::findByAgencyId($user['agency_id']);
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
                        $apartmentTypes = ApartmentType::get();
                        $buildings = Building::findByAgentId($user['id'], $user['agency_id']);
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

        $csrf_token = $this->helpers->csrf_token('apartments.store');
        $title = 'Ajouter un appartement';
        $content_view = 'admin/apartments/create.php';
        require_once dirname(__DIR__, 1) . '/Views/layouts/admin_layout.php';
    }

    /**
     * Enregistre un nouvel appartement avec validation et transaction.
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

    // Liste des commodités autorisées
    $allowedAmenities = ['parking', 'ascenseur', 'climatisation', 'chauffage', 'jardin', 'terrasse', 'cave'];

    // Récupération des données du formulaire
    $data = [
        'building_id' => (int) ($_POST['building_id'] ?? 0),
        'owner_id' => (int) ($_POST['owner_id'] ?? 0),
        'agency_id' => isset($_POST['agency_id']) && $_POST['agency_id'] !== ''
            ? (int) $_POST['agency_id']
            : ($role === 'superadmin' || $role === 'admin' ? ($user['agency_id'] ?? null) : null),
        'agent_id' => isset($_POST['agent_id']) && $_POST['agent_id'] !== ''
            ? (int) $_POST['agent_id']
            : ($role === 'superadmin' || $role === 'admin' ? $user['id'] : null),
        'type_id' => (int) ($_POST['type_id'] ?? 0),
        'number' => trim($_POST['number'] ?? ''),
        'floor' => (int) ($_POST['floor'] ?? 0),
        'area' => !empty($_POST['area']) ? (float) ($_POST['area']) : null,
        'rooms' => !empty($_POST['rooms']) ? (int) ($_POST['rooms']) : null,
        'bedrooms' => !empty($_POST['bedrooms']) ? (int) ($_POST['bedrooms']) : null,
        'bathrooms' => !empty($_POST['bathrooms']) ? (int) ($_POST['bathrooms']) : null,
        'toilets' => !empty($_POST['toilets']) ? (int) ($_POST['toilets']) : null,
        'living_rooms' => !empty($_POST['living_rooms']) ? (int) ($_POST['living_rooms']) : null,
        'kitchens' => !empty($_POST['kitchens']) ? (int) ($_POST['kitchens']) : null,
        'has_balcony' => isset($_POST['has_balcony']) ? (int) $_POST['has_balcony'] : 0,
        'amenities' => isset($_POST['amenities']) && is_array($_POST['amenities']) ? array_filter($_POST['amenities'], fn($a) => in_array($a, $allowedAmenities)) : [],
        'rent_amount' => !empty($_POST['rent_amount']) ? (float) ($_POST['rent_amount']) : null,
        'charges_amount' => !empty($_POST['charges_amount']) ? (float) ($_POST['charges_amount']) : null,
        'price' => !empty($_POST['price']) ? (float) ($_POST['price']) : null,
        'status' => trim($_POST['status'] ?? ''),
        'images' => $_FILES['images'] ?? []
    ];

    // Validation des champs requis
    if (empty($data['number']) || strlen($data['number']) < 1) {
        $errors[] = 'Le numéro de l’appartement est requis.';
    }
    if (!Building::find($data['building_id'])) {
        $errors[] = 'Le bâtiment sélectionné est invalide.';
    }
    if (!Owner::find($data['owner_id'])) {
        $errors[] = 'Le propriétaire sélectionné est invalide.';
    }
    if (!ApartmentType::find($data['type_id'])) {
        $errors[] = 'Le type d’appartement sélectionné est invalide.';
    }
    if (!in_array($data['status'], ['disponible', 'louer', 'vendu', 'en_renovation'])) {
        $errors[] = 'Le statut est invalide.';
    }
    if ($data['area'] === null) {
        $errors[] = 'La superficie est requise.';
    }
    if ($data['rooms'] === null) {
        $errors[] = 'Le nombre de pièces est requis.';
    }
    if ($data['bedrooms'] === null) {
        $errors[] = 'Le nombre de chambres est requis.';
    }
    if ($data['bathrooms'] === null) {
        $errors[] = 'Le nombre de salles de bain est requis.';
    }
    if ($data['toilets'] === null) {
        $errors[] = 'Le nombre de toilettes est requis.';
    }
    if ($data['living_rooms'] === null) {
        $errors[] = 'Le nombre de salons est requis.';
    }
    if ($data['kitchens'] === null) {
        $errors[] = 'Le nombre de cuisines est requis.';
    }

    // Validation des champs numériques
    if ($data['floor'] < 0) {
        $errors[] = 'L’étage ne peut pas être négatif.';
    }
    if ($data['area'] <= 0) {
        $errors[] = 'La superficie doit être positive.';
    }
    if ($data['rooms'] < 0) {
        $errors[] = 'Le nombre de pièces ne peut pas être négatif.';
    }
    if ($data['bedrooms'] < 0) {
        $errors[] = 'Le nombre de chambres ne peut pas être négatif.';
    }
    if ($data['bathrooms'] < 0) {
        $errors[] = 'Le nombre de salles de bain ne peut pas être négatif.';
    }
    if ($data['toilets'] < 0) {
        $errors[] = 'Le nombre de toilettes ne peut pas être négatif.';
    }
    if ($data['living_rooms'] < 0) {
        $errors[] = 'Le nombre de salons ne peut pas être négatif.';
    }
    if ($data['kitchens'] < 0) {
        $errors[] = 'Le nombre de cuisines ne peut pas être négatif.';
    }
    if ($data['rent_amount'] && $data['rent_amount'] <= 0) {
        $errors[] = 'Le loyer doit être positif.';
    }
    if ($data['charges_amount'] && $data['charges_amount'] <= 0) {
        $errors[] = 'Le montant des charges doit être positif.';
    }
    if ($data['price'] && $data['price'] <= 0) {
        $errors[] = 'Le prix doit être positif.';
    }
    if (!in_array($data['has_balcony'], [0, 1])) {
        $errors[] = 'La valeur du balcon doit être "Oui" ou "Non".';
    }
    if (!empty($data['amenities']) && count($data['amenities']) > count($allowedAmenities)) {
        $errors[] = 'Les commodités sélectionnées sont invalides.';
    }

    // Vérification des permissions selon le rôle
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
        $building = Building::find($data['building_id']);
        if ($building && $building->getAgencyId() !== $user['agency_id']) {
            $errors[] = 'Le bâtiment sélectionné n’appartient pas à votre agence.';
        }
    } elseif ($role === 'agent') {
        if ($data['agency_id'] !== $user['agency_id']) {
            $errors[] = 'Vous ne pouvez associer cet appartement qu’à votre agence.';
        }
        if ($data['agent_id'] !== $user['id']) {
            $errors[] = 'Vous ne pouvez vous associer qu’à vous-même comme agent.';
        }
        $owner = Owner::find($data['owner_id']);
        if ($owner && $owner->getAgentId() !== $user['id']) {
            $errors[] = 'Vous ne pouvez associer cet appartement qu’à un propriétaire que vous gérez.';
        }
        $building = Building::find($data['building_id']);
        if ($building && $building->getAgentId() !== $user['id']) {
            $errors[] = 'Vous ne pouvez associer cet appartement qu’à un bâtiment que vous gérez.';
        }
    } else {
        $errors[] = 'Vous n’êtes pas autorisé à créer un appartement.';
    }

    // Validation des images
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

    // Retour en cas d’erreur
    if (!empty($errors)) {
        $this->flash->flash('error', implode('<br>', $errors));
        $this->helpers->redirect('/apartments/create');
        return;
    }

    // Création de l’appartement avec transaction
    $pdo = Database::getInstance();
    try {
        $pdo->beginTransaction();

        // Journaliser les données pour débogage
        $this->logger->info("Tentative de création d’appartement avec les données : ", $data);

        $apartment = Apartment::create([
            'building_id' => $data['building_id'],
            'owner_id' => $data['owner_id'],
            'agency_id' => $data['agency_id'] ?: null,
            'agent_id' => $data['agent_id'] ?: null,
            'type_id' => $data['type_id'],
            'number' => $data['number'],
            'floor' => $data['floor'],
            'area' => $data['area'],
            'rooms' => $data['rooms'],
            'bedrooms' => $data['bedrooms'],
            'bathrooms' => $data['bathrooms'],
            'toilets' => $data['toilets'],
            'living_rooms' => $data['living_rooms'],
            'kitchens' => $data['kitchens'],
            'has_balcony' => $data['has_balcony'],
            'amenities' => !empty($data['amenities']) ? json_encode($data['amenities']) : null,
            'rent_amount' => $data['rent_amount'],
            'charges_amount' => $data['charges_amount'],
            'price' => $data['price'],
            'status' => $data['status']
        ]);

        // Vérifier si l’appartement a été créé
        if (!$apartment) {
            throw new PDOException("Échec de la création de l’appartement : aucune donnée retournée.");
        }

        // Upload des images
        if (!empty($data['images']['name'][0])) {
            $uploadDir = dirname(__DIR__, 2) . '/public/assets/images/apartments/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            if (!is_writable($uploadDir)) {
                throw new PDOException("Le répertoire $uploadDir n’est pas accessible en écriture.");
            }

            foreach ($data['images']['name'] as $key => $imageName) {
                if ($imageName) {
                    $ext = pathinfo($imageName, PATHINFO_EXTENSION);
                    $filename = uniqid('apartment_') . '.' . $ext;
                    $destination = $uploadDir . $filename;

                    if (move_uploaded_file($data['images']['tmp_name'][$key], $destination)) {
                        Images::create([
                            'entity_type' => 'apartment',
                            'entity_id' => $apartment->getId(),
                            'path' => '/assets/images/apartments/' . $filename,
                            'alt_text' => 'Image de l’appartement ' . htmlspecialchars($data['number']),
                            'order' => $key + 1
                        ]);
                    } else {
                        throw new PDOException("Erreur lors de l’upload de $imageName");
                    }
                }
            }
        }

        $pdo->commit();
        // Audit de la création de l’appartement
        Audit::log('create', 'apartments', $apartment->getId(), $data['agency_id'], null, $data);

        $this->flash->flash('success', 'Appartement ajouté avec succès.');
        $this->helpers->redirect('/apartments');
    } catch (PDOException $e) {
        $pdo->rollBack();
        $this->logger->error("Erreur lors de l’ajout de l’appartement : " . $e->getMessage(), [
            'data' => $data,
            'user_id' => $user['id'],
            'role' => $role
        ]);
        $this->flash->flash('error', 'Une erreur est survenue lors de l’ajout de l’appartement : ' . $e->getMessage());
        $this->helpers->redirect('/apartments/create');
    }
}

    /**
     * Affiche le formulaire d’édition d’un appartement.
     */
    public function edit(int $id)
    {
        if (!$this->auth->check()) {
            $this->flash->flash('error', 'Vous devez être connecté pour accéder à cette page.');
            $this->helpers->redirect('/auth/login');
            return;
        }

        $apartment = Apartment::find($id);
        if (!$apartment) {
            $this->flash->flash('error', 'Appartement introuvable.');
            $this->helpers->redirect('/apartments');
            return;
        }

        $images = Images::findByEntity('apartment', $apartment->getId());
        $user = $this->auth->user();
        $role = $user['role'] ?? 'guest';

        $apartmentTypes = [];
        $buildings = [];
        $agents = [];
        $owners = [];
        $agencies = [];
        $allowedStatuses = ['disponible', 'loué', 'réservé', 'en_maintenance'];

        try {
            switch ($role) {
                case 'superadmin':
                    $apartmentTypes = ApartmentType::get();
                    $buildings = Building::get();
                    $agents = User::getByAgency(null, '', 1000, 0, ['agent']);
                    $owners = User::getByAgency(null, '', 1000, 0, ['proprietaire']);
                    $agencies = Agency::get();
                    break;
                case 'admin':
                    if ($user['agency_id']) {
                        $apartmentTypes = ApartmentType::get();
                        $buildings = Building::findByAgencyId($user['agency_id']);
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
                        $apartmentTypes = ApartmentType::get();
                        $buildings = Building::findByAgentId($user['id'], $user['agency_id']);
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
            $this->helpers->redirect('/apartments');
            return;
        }

        $csrf_token = $this->helpers->csrf_token('apartments.update');
        $title = 'Modifier un appartement';
        $content_view = 'admin/apartments/edit.php';
        require_once dirname(__DIR__, 1) . '/Views/layouts/admin_layout.php';
    }

    /**
     * Met à jour les données d’un appartement.
     */
    public function update(int $id)
    {
        if (!$this->auth->check()) {
            $this->flash->flash('error', 'Vous devez être connecté pour accéder à cette page.');
            $this->helpers->redirect('/auth/login');
            return;
        }

        $apartment = Apartment::find($id);
        if (!$apartment) {
            $this->flash->flash('error', 'Appartement introuvable.');
            $this->helpers->redirect('/apartments');
            return;
        }

        $user = $this->auth->user();
        $role = $user['role'] ?? 'guest';
        if ($role === 'admin' && $apartment->getAgencyId() !== $user['agency_id']) {
            $this->flash->flash('error', 'Vous n’êtes pas autorisé à modifier cet appartement.');
            $this->helpers->redirect('/apartments');
            return;
        }
        if ($role === 'agent' && $apartment->getAgentId() !== $user['id']) {
            $this->flash->flash('error', 'Vous ne pouvez modifier que les appartements que vous gérez.');
            $this->helpers->redirect('/apartments');
            return;
        }

        // Récupération des données du formulaire
        $data = [
            'building_id' => (int) ($_POST['building_id'] ?? 0),
            'owner_id' => (int) ($_POST['owner_id'] ?? 0),
            'agency_id' => isset($_POST['agency_id']) && $_POST['agency_id'] !== ''
                ? (int) $_POST['agency_id']
                : ($role === 'superadmin' || $role === 'admin' ? ($user['agency_id'] ?? 0) : 0),
            'agent_id' => isset($_POST['agent_id']) && $_POST['agent_id'] !== ''
                ? (int) $_POST['agent_id']
                : ($role === 'superadmin' || $role === 'admin' ? $user['id'] : 0),
            'type_id' => (int) ($_POST['type_id'] ?? 0),
            'number' => trim($_POST['number'] ?? ''),
            'floor' => (int) ($_POST['floor'] ?? 0),
            'area' => !empty($_POST['area']) ? (float) ($_POST['area']) : null,
            'rooms' => (int) ($_POST['rooms'] ?? 0),
            'bathrooms' => (int) ($_POST['bathrooms'] ?? 0),
            'rent' => !empty($_POST['rent']) ? (float) ($_POST['rent']) : null,
            'status' => trim($_POST['status'] ?? ''),
            'images' => $_FILES['images'] ?? [],
            'delete_images' => $_POST['delete_images'] ?? []
        ];

        // Validation des champs
        $errors = [];
        if (empty($data['number']) || strlen($data['number']) < 1) {
            $errors[] = 'Le numéro de l’appartement est requis.';
        }
        if (!Building::find($data['building_id'])) {
            $errors[] = 'Le bâtiment sélectionné est invalide.';
        }
        if (!Owner::find($data['owner_id'])) {
            $errors[] = 'Le propriétaire sélectionné est invalide.';
        }
        if (!ApartmentType::find($data['type_id'])) {
            $errors[] = 'Le type d’appartement sélectionné est invalide.';
        }
        if ($data['floor'] < 0) {
            $errors[] = 'L’étage ne peut pas être négatif.';
        }
        if ($data['area'] && $data['area'] <= 0) {
            $errors[] = 'La superficie doit être positive.';
        }
        if ($data['rooms'] < 0) {
            $errors[] = 'Le nombre de pièces ne peut pas être négatif.';
        }
        if ($data['bathrooms'] < 0) {
            $errors[] = 'Le nombre de salles de bain ne peut pas être négatif.';
        }
        if ($data['rent'] && $data['rent'] <= 0) {
            $errors[] = 'Le loyer doit être positif.';
        }
        if (!in_array($data['status'], ['disponible', 'loué', 'réservé', 'en_maintenance'])) {
            $errors[] = 'Le statut est invalide.';
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
            $building = Building::find($data['building_id']);
            if ($building && $building->getAgencyId() !== $user['agency_id']) {
                $errors[] = 'Le bâtiment sélectionné n’appartient pas à votre agence.';
            }
        } elseif ($role === 'agent') {
            if ($data['agency_id'] !== $user['agency_id']) {
                $errors[] = 'Vous ne pouvez associer cet appartement qu’à votre agence.';
            }
            if ($data['agent_id'] !== $user['id']) {
                $errors[] = 'Vous ne pouvez vous associer qu’à vous-même comme agent.';
            }
            $owner = Owner::find($data['owner_id']);
            if ($owner && !Owner::isCreatedByAgent($user['id'], $owner->getUserId())) {
                $errors[] = 'Vous ne pouvez associer cet appartement qu’à un propriétaire que vous gérez.';
            }
            $building = Building::find($data['building_id']);
            if ($building && $building->getAgentId() !== $user['id']) {
                $errors[] = 'Vous ne pouvez associer cet appartement qu’à un bâtiment que vous gérez.';
            }
        } else {
            $errors[] = 'Vous n’êtes pas autorisé à modifier cet appartement.';
        }

        // Validation du nombre total d'images
        $maxImages = 4;
        $existingImages = Images::findByEntity('apartment', $apartment->getId());
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
            $this->helpers->redirect("/apartments/edit/$id");
            return;
        }

        // Mise à jour en base + images
        $pdo = Database::getInstance();
        try {
            $pdo->beginTransaction();

            // Mise à jour de l’appartement
            Apartment::update($id, [
                'building_id' => $data['building_id'],
                'owner_id' => $data['owner_id'],
                'agency_id' => $data['agency_id'],
                'agent_id' => $data['agent_id'],
                'type_id' => $data['type_id'],
                'number' => $data['number'],
                'floor' => $data['floor'],
                'area' => $data['area'],
                'rooms' => $data['rooms'],
                'bathrooms' => $data['bathrooms'],
                'rent' => $data['rent'],
                'status' => $data['status']
            ]);

            // Suppression des images marquées pour suppression
            if (!empty($data['delete_images'])) {
                foreach ($data['delete_images'] as $imageId) {
                    $image = Images::find($imageId);
                    if ($image && $image->getEntityType() === 'apartment' && $image->getEntityId() === $apartment->getId()) {
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
                $uploadDir = dirname(__DIR__, 2) . '/public/assets/images/apartments/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                if (!is_writable($uploadDir)) {
                    throw new PDOException("Le répertoire $uploadDir n’est pas accessible en écriture.");
                }

                foreach ($data['images']['name'] as $key => $imageName) {
                    if ($imageName) {
                        $ext = pathinfo($imageName, PATHINFO_EXTENSION);
                        $filename = uniqid('apartment_') . '.' . $ext;
                        $destination = $uploadDir . $filename;

                        if (move_uploaded_file($data['images']['tmp_name'][$key], $destination)) {
                            Images::create([
                                'entity_type' => 'apartment',
                                'entity_id' => $apartment->getId(),
                                'path' => '/assets/images/apartments/' . $filename,
                                'alt_text' => 'Image de l’appartement ' . htmlspecialchars($data['number']),
                                'order' => $key + 1
                            ]);
                        } else {
                            throw new PDOException("Erreur lors de l’upload de $imageName");
                        }
                    }
                }
            }

            $pdo->commit();
            // Audit de la mise à jour de l’appartement
            Audit::log('update', 'apartments', $id, $data['agency_id'], $apartment->toArray(), $data);
            $this->flash->flash('success', 'Appartement mis à jour avec succès.');
            $this->helpers->redirect("/apartments/$id");
        } catch (PDOException $e) {
            $pdo->rollBack();
            $this->logger->error("Erreur lors de la mise à jour de l’appartement ID $id : " . $e->getMessage());
            $this->flash->flash('error', 'Une erreur est survenue lors de la mise à jour de l’appartement.');
            $this->helpers->redirect("/apartments/edit/$id");
        }
    }

    /**
     * Supprime un appartement (suppression logique) et ses images associées.
     */
    public function delete(int $id)
    {
        if (!$this->auth->check()) {
            $this->flash->flash('error', 'Vous devez être connecté pour accéder à cette page.');
            $this->helpers->redirect('/auth/login');
            return;
        }

        $apartment = Apartment::find($id);
        if (!$apartment) {
            $this->flash->flash('error', 'Appartement introuvable.');
            $this->helpers->redirect('/apartments');
            return;
        }

        $user = $this->auth->user();
        $role = $user['role'] ?? 'guest';
        if ($role === 'admin' && $apartment->getAgencyId() !== $user['agency_id']) {
            $this->flash->flash('error', 'Vous n’êtes pas autorisé à supprimer cet appartement.');
            $this->helpers->redirect('/apartments');
            return;
        }
        if ($role === 'agent' && $apartment->getAgentId() !== $user['id']) {
            $this->flash->flash('error', 'Vous ne pouvez supprimer que les appartements que vous gérez.');
            $this->helpers->redirect('/apartments');
            return;
        }
        if ($role !== 'superadmin' && $role !== 'admin' && $role !== 'agent') {
            $this->flash->flash('error', 'Vous n’êtes pas autorisé à supprimer cet appartement.');
            $this->helpers->redirect('/apartments');
            return;
        }

        $pdo = Database::getInstance();
        try {
            $pdo->beginTransaction();

            if (!Images::deleteByEntity('apartment', $apartment->getId())) {
                // Pas d'erreur fatale si aucune image
            }

            if (!Apartment::delete($id)) {
                throw new PDOException('Échec de la suppression logique de l’appartement.');
            }

            $pdo->commit();
            $this->flash->flash('success', 'Appartement supprimé avec succès.');
            $this->helpers->redirect('/apartments');
        } catch (PDOException $e) {
            $pdo->rollBack();
            $this->logger->error("Erreur lors de la suppression de l’appartement ID $id : " . $e->getMessage());
            $this->flash->flash('error', 'Une erreur est survenue lors de la suppression de l’appartement.');
            $this->helpers->redirect("/apartments/edit/$id");
        }
    }
}
