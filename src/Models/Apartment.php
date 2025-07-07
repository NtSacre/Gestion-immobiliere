<?php
namespace App\Models;

use App\Config\Database;
use App\Utils\Logger;
use PDO;
use PDOException;

/**
 * Modèle pour la table apartments
 * ImmoApp (L2GIB, 2024-2025)
 * Représente un appartement dans un bâtiment, appartenant à un propriétaire
 */
class Apartment
{
    private $pdo;
    protected $id;
    protected $building_id;
    protected $owner_id;
    protected $agency_id;
    protected $agent_id;
    protected $number;
    protected $floor;
    protected $area;
    protected $rooms;
    protected $bedrooms;
    protected $bathrooms;
    protected $toilets;
    protected $living_rooms;
    protected $kitchens;
    protected $has_balcony;
    protected $amenities;
    protected $type_id;
    protected $rent_amount;
    protected $charges_amount;
    protected $status;
    protected $price;
    protected $created_at;
    protected $updated_at;
    protected $is_deleted;
    protected $canManage;
     protected $logger;


    public function __construct()
    {
        $this->pdo = Database::getInstance();
        $this->logger = new Logger();
    }

    // Getters
    public function getId() { return $this->id; }
    public function getBuildingId() { return $this->building_id; }
    public function getOwnerId() { return $this->owner_id; }
    public function getAgencyId() { return $this->agency_id; }
    public function getAgentId() { return $this->agent_id; }
    public function getNumber() { return $this->number; }
    public function getFloor() { return $this->floor; }
    public function getArea() { return $this->area; }
    public function getRooms() { return $this->rooms; }
    public function getBedrooms() { return $this->bedrooms; }
    public function getBathrooms() { return $this->bathrooms; }
    public function getToilets() { return $this->toilets; }
    public function getLivingRooms() { return $this->living_rooms; }
    public function getKitchens() { return $this->kitchens; }
    public function getHasBalcony() { return $this->has_balcony; }
    public function getAmenities() { return $this->amenities; }
    public function getTypeId() { return $this->type_id; }
    public function getRentAmount() { return $this->rent_amount; }
    public function getChargesAmount() { return $this->charges_amount; }
    public function getStatus() { return $this->status; }
    public function getPrice() { return $this->price; }
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedAt() { return $this->updated_at; }
    public function getIsDeletedAt() { return $this->is_deleted; }
    public function getCanManage() { return $this->canManage; }

    // Setters
    protected function setId($id) { $this->id = $id; }
    protected function setBuildingId($building_id) { $this->building_id = $building_id; }
    protected function setOwnerId($owner_id) { $this->owner_id = $owner_id; }
    protected function setAgencyId($agency_id) { $this->agency_id = $agency_id; }
    protected function setAgentId($agent_id) { $this->agent_id = $agent_id; }
    protected function setNumber($number) { $this->number = $number; }
    protected function setFloor($floor) { $this->floor = $floor; }
    protected function setArea($area) { $this->area = $area; }
    protected function setRooms($rooms) { $this->rooms = $rooms; }
    protected function setBedrooms($bedrooms) { $this->bedrooms = $bedrooms; }
    protected function setBathrooms($bathrooms) { $this->bathrooms = $bathrooms; }
    protected function setToilets($toilets) { $this->toilets = $toilets; }
    protected function setLivingRooms($living_rooms) { $this->living_rooms = $living_rooms; }
    protected function setKitchens($kitchens) { $this->kitchens = $kitchens; }
    protected function setHasBalcony($has_balcony) { $this->has_balcony = $has_balcony; }
    protected function setAmenities($amenities) { $this->amenities = $amenities; }
    protected function setTypeId($type_id) { $this->type_id = $type_id; }
    protected function setRentAmount($rent_amount) { $this->rent_amount = $rent_amount; }
    protected function setChargesAmount($charges_amount) { $this->charges_amount = $charges_amount; }
    protected function setStatus($status) { $this->status = $status; }
    protected function setPrice($price) { $this->price = $price; }
    protected function setCreatedAt($created_at) { $this->created_at = $created_at; }
    protected function setUpdatedAt($updated_at) { $this->updated_at = $updated_at; }
    protected function setIsDeletedAt($is_deleted) { $this->is_deleted = $is_deleted; }
    public function setCanManage($canManage) { $this->canManage = $canManage; }

    /**
     * Crée un objet Apartment à partir des données de la base
     * @param array $data
     * @return Apartment
     */
    protected static function fromData(array $data): Apartment
    {
        $apartment = new self();
        $apartment->setId($data['id']);
        $apartment->setBuildingId($data['building_id']);
        $apartment->setOwnerId($data['owner_id']);
        $apartment->setAgencyId($data['agency_id'] ?? null);
        $apartment->setAgentId($data['agent_id'] ?? null);
        $apartment->setNumber($data['number']);
        $apartment->setFloor($data['floor']);
        $apartment->setArea($data['area']);
        $apartment->setRooms($data['rooms']);
        $apartment->setBedrooms($data['bedrooms']);
        $apartment->setBathrooms($data['bathrooms']);
        $apartment->setToilets($data['toilets']);
        $apartment->setLivingRooms($data['living_rooms']);
        $apartment->setKitchens($data['kitchens']);
        $apartment->setHasBalcony($data['has_balcony']);
        $apartment->setAmenities($data['amenities'] ? json_decode($data['amenities'], true) : null);
        $apartment->setTypeId($data['type_id']);
        $apartment->setRentAmount($data['rent_amount'] ?? null);
        $apartment->setChargesAmount($data['charges_amount'] ?? null);
        $apartment->setStatus($data['status']);
        $apartment->setPrice($data['price'] ?? null);
        $apartment->setCreatedAt($data['created_at']);
        $apartment->setUpdatedAt($data['updated_at'] ?? null);
        $apartment->setIsDeletedAt($data['is_deleted'] ?? null);
        $apartment->setCanManage($data['canManage'] ?? false);
        return $apartment;
    }

    /**
     * Trouve un appartement par ID
     * @param int $id
     * @return Apartment|null
     */
    public static function find($id)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT * FROM apartments WHERE id = ? AND is_deleted = 0');
            $stmt->execute([$id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? self::fromData($data) : null;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche de l’appartement : " . $e->getMessage());
        }
    }

    /**
     * Alias pour find
     * @param int $id
     * @return Apartment|null
     */
    public static function findById($id)
    {
        return self::find($id);
    }

    /**
     * Récupère tous les appartements avec recherche, pagination et filtres
     * @param string $search
     * @param int $limit
     * @param int $offset
     * @param array $statuses
     * @return array
     */
    public static function getAll($search = '', $limit = 10, $offset = 0, $statuses = [])
    {
        try {
            $pdo = Database::getInstance();
            $query = 'SELECT a.* FROM apartments a JOIN buildings b ON a.building_id = b.id WHERE a.is_deleted = 0';
            $params = [];

            if ($search) {
                $query .= ' AND (a.number LIKE ? OR b.name LIKE ? OR b.city LIKE ?)';
                $searchParam = "%$search%";
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }

            if (!empty($statuses)) {
                $placeholders = implode(',', array_fill(0, count($statuses), '?'));
                $query .= " AND a.status IN ($placeholders)";
                $params = array_merge($params, $statuses);
            }

            $query .= ' ORDER BY a.number ASC LIMIT ? OFFSET ?';
            $params[] = $limit;
            $params[] = $offset;

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $apartments = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $apartments[] = self::fromData($data);
            }
            return $apartments;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération des appartements : " . $e->getMessage());
        }
    }

    /**
     * Compte tous les appartements avec recherche et filtres
     * @param string $search
     * @param array $statuses
     * @return int
     */
    public static function countAll($search = '', $statuses = [])
    {
        try {
            $pdo = Database::getInstance();
            $query = 'SELECT COUNT(*) FROM apartments a JOIN buildings b ON a.building_id = b.id WHERE a.is_deleted = 0';
            $params = [];

            if ($search) {
                $query .= ' AND (a.number LIKE ? OR b.name LIKE ? OR b.city LIKE ?)';
                $searchParam = "%$search%";
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }

            if (!empty($statuses)) {
                $placeholders = implode(',', array_fill(0, count($statuses), '?'));
                $query .= " AND a.status IN ($placeholders)";
                $params = array_merge($params, $statuses);
            }

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors du comptage des appartements : " . $e->getMessage());
        }
    }

    /**
     * Récupère les appartements par ID d’agence
     * @param int $agencyId
     * @param string $search
     * @param int $limit
     * @param int $offset
     * @param array $statuses
     * @return array
     */
    public static function findByAgencyId($agencyId, $search = '', $limit = 10, $offset = 0, $statuses = [])
    {
        try {
            $pdo = Database::getInstance();
            $query = 'SELECT a.* FROM apartments a JOIN buildings b ON a.building_id = b.id WHERE a.is_deleted = 0 AND b.agency_id = ?';
            $params = [$agencyId];

            if ($search) {
                $query .= ' AND (a.number LIKE ? OR b.name LIKE ? OR b.city LIKE ?)';
                $searchParam = "%$search%";
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }

            if (!empty($statuses)) {
                $placeholders = implode(',', array_fill(0, count($statuses), '?'));
                $query .= " AND a.status IN ($placeholders)";
                $params = array_merge($params, $statuses);
            }

            $query .= ' ORDER BY a.number ASC LIMIT ? OFFSET ?';
            $params[] = $limit;
            $params[] = $offset;

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $apartments = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $apartments[] = self::fromData($data);
            }
            return $apartments;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche des appartements par agence : " . $e->getMessage());
        }
    }

    /**
     * Récupère les appartements par ID d’agent et d’agence
     * @param int $agentId
     * @param int $agencyId
     * @param string $search
     * @param int $limit
     * @param int $offset
     * @param array $statuses
     * @return array
     */
    public static function findByAgentId($agentId, $agencyId, $search = '', $limit = 10, $offset = 0, $statuses = [])
    {
        try {
            $pdo = Database::getInstance();
            $query = 'SELECT a.* FROM apartments a JOIN buildings b ON a.building_id = b.id WHERE a.is_deleted = 0 AND a.agent_id = ? AND b.agency_id = ?';
            $params = [$agentId, $agencyId];

            if ($search) {
                $query .= ' AND (a.number LIKE ? OR b.name LIKE ? OR b.city LIKE ?)';
                $searchParam = "%$search%";
                $params[] = $searchParam;
                $params[] = $searchParam;
                $params[] = $searchParam;
            }

            if (!empty($statuses)) {
                $placeholders = implode(',', array_fill(0, count($statuses), '?'));
                $query .= " AND a.status IN ($placeholders)";
                $params = array_merge($params, $statuses);
            }

            $query .= ' ORDER BY a.number ASC LIMIT ? OFFSET ?';
            $params[] = $limit;
            $params[] = $offset;

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $apartments = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $apartments[] = self::fromData($data);
            }
            return $apartments;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche des appartements par agent : " . $e->getMessage());
        }
    }

    /**
     * Récupère tous les appartements
     * @return array
     */
    public static function get()
    {
        return self::getAll();
    }

    /**
     * Récupère le premier appartement (par numéro)
     * @return Apartment|null
     */
    public static function first()
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->query('SELECT * FROM apartments WHERE is_deleted = 0 ORDER BY number ASC LIMIT 1');
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? self::fromData($data) : null;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération du premier appartement : " . $e->getMessage());
        }
    }

    /**
     * Crée un nouvel appartement
     * @param array $data
     * @return Apartment
     */
public static function create(array $data)
{
    try {
        $pdo = Database::getInstance();
        // Vérifications des dépendances
        if (!Building::find($data['building_id'])) {
            throw new PDOException("Building ID invalide : {$data['building_id']}");
        }
        if (!Owner::find($data['owner_id'])) {
            throw new PDOException("Owner ID invalide : {$data['owner_id']}");
        }
        if (!empty($data['agency_id']) && !Agency::find($data['agency_id'])) {
            throw new PDOException("Agency ID invalide : {$data['agency_id']}");
        }
        if (!empty($data['agent_id']) && !User::find($data['agent_id'])) {
            throw new PDOException("Agent ID invalide : {$data['agent_id']}");
        }
        if (!ApartmentType::find($data['type_id'])) {
            throw new PDOException("ApartmentType ID invalide : {$data['type_id']}");
        }

        // Journaliser les données pour débogage
        $logger = new Logger();
        $logger->info("Tentative d’insertion d’un appartement avec les données : ", $data);

        $stmt = $pdo->prepare('
            INSERT INTO apartments (
                building_id, owner_id, agency_id, agent_id, number, floor, area, rooms, bedrooms, bathrooms, toilets,
                living_rooms, kitchens, has_balcony, amenities, type_id, rent_amount, charges_amount, status, price, is_deleted, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, NOW(), NOW())
        ');
        $stmt->execute([
            $data['building_id'],
            $data['owner_id'],
            $data['agency_id'] ?? null,
            $data['agent_id'] ?? null,
            $data['number'],
            $data['floor'],
            $data['area'],
            $data['rooms'],
            $data['bedrooms'],
            $data['bathrooms'],
            $data['toilets'],
            $data['living_rooms'],
            $data['kitchens'],
            $data['has_balcony'],
            $data['amenities'] ? json_encode($data['amenities']) : null,
            $data['type_id'],
            $data['rent_amount'] ?? null,
            $data['charges_amount'] ?? null,
            $data['status'],
            $data['price'] ?? null
        ]);
        $id = $pdo->lastInsertId();
        
        if (!$id) {
            throw new PDOException("Aucun ID généré après l’insertion de l’appartement.");
        }

        // Vérifier si l’enregistrement existe
        $checkStmt = $pdo->prepare('SELECT id, is_deleted FROM apartments WHERE id = ?');
        $checkStmt->execute([$id]);
        $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
        if (!$result) {
            throw new PDOException("L’enregistrement avec l’ID $id n’existe pas dans la base après insertion.");
        }
        if ($result['is_deleted'] != 0) {
            throw new PDOException("L’enregistrement avec l’ID $id a is_deleted = {$result['is_deleted']} après insertion.");
        }

        $apartment = self::find($id);
        if (!$apartment) {
            throw new PDOException("L’appartement avec l’ID $id n’a pas été trouvé après insertion.");
        }

        return $apartment;
    } catch (PDOException $e) {
        $logger = new Logger();
        $logger->error("Erreur lors de la création de l’appartement : " . $e->getMessage(), $data);
        throw new PDOException("Erreur lors de la création de l’appartement : " . $e->getMessage());
    }
}

    /**
     * Met à jour un appartement
     * @param int $id
     * @param array $data
     * @return Apartment|null
     */
    public static function update($id, array $data)
    {
        try {
            $pdo = Database::getInstance();
            $existing = self::find($id);
            if (!$existing) {
                throw new PDOException("Appartement introuvable");
            }

            // Vérifier les dépendances si modifiées
            if (!empty($data['building_id']) && !Building::find($data['building_id'])) {
                throw new PDOException("Building ID invalide");
            }
            if (!empty($data['owner_id']) && !Owner::find($data['owner_id'])) {
                throw new PDOException("Owner ID invalide");
            }
            if (!empty($data['agency_id']) && !Agency::find($data['agency_id'])) {
                throw new PDOException("Agency ID invalide");
            }
            if (!empty($data['agent_id']) && !User::find($data['agent_id'])) {
                throw new PDOException("Agent ID invalide");
            }
            if (!empty($data['type_id']) && !ApartmentType::find($data['type_id'])) {
                throw new PDOException("ApartmentType ID invalide");
            }

            $stmt = $pdo->prepare('
                UPDATE apartments
                SET building_id = ?, owner_id = ?, agency_id = ?, agent_id = ?, number = ?, floor = ?, area = ?, rooms = ?, bedrooms = ?,
                    bathrooms = ?, toilets = ?, living_rooms = ?, kitchens = ?, has_balcony = ?, amenities = ?,
                    type_id = ?, rent_amount = ?, charges_amount = ?, status = ?, price = ?, updated_at = NOW()
                WHERE id = ?
            ');
            $stmt->execute([
                $data['building_id'] ?? $existing->getBuildingId(),
                $data['owner_id'] ?? $existing->getOwnerId(),
                $data['agency_id'] ?? $existing->getAgencyId(),
                $data['agent_id'] ?? $existing->getAgentId(),
                $data['number'] ?? $existing->getNumber(),
                $data['floor'] ?? $existing->getFloor(),
                $data['area'] ?? $existing->getArea(),
                $data['rooms'] ?? $existing->getRooms(),
                $data['bedrooms'] ?? $existing->getBedrooms(),
                $data['bathrooms'] ?? $existing->getBathrooms(),
                $data['toilets'] ?? $existing->getToilets(),
                $data['living_rooms'] ?? $existing->getLivingRooms(),
                $data['kitchens'] ?? $existing->getKitchens(),
                $data['has_balcony'] ?? $existing->getHasBalcony(),
                isset($data['amenities']) ? json_encode($data['amenities']) : ($existing->getAmenities() ? json_encode($existing->getAmenities()) : null),
                $data['type_id'] ?? $existing->getTypeId(),
                $data['rent_amount'] ?? $existing->getRentAmount(),
                $data['charges_amount'] ?? $existing->getChargesAmount(),
                $data['status'] ?? $existing->getStatus(),
                $data['price'] ?? $existing->getPrice(),
                $id
            ]);
            return self::find($id);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la mise à jour de l’appartement : " . $e->getMessage());
        }
    }

    /**
     * Supprime un appartement (soft delete)
     * @param int $id
     * @return bool
     */
    public static function delete($id)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('UPDATE apartments SET is_deleted = NOW() WHERE id = ?');
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la suppression de l’appartement : " . $e->getMessage());
        }
    }

    /**
     * Trouve les appartements par building_id
     * @param int $buildingId
     * @return array
     */
    public static function findByBuildingId($buildingId)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT * FROM apartments WHERE building_id = ? AND is_deleted = 0');
            $stmt->execute([$buildingId]);
            $apartments = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $apartments[] = self::fromData($data);
            }
            return $apartments;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche des appartements par building_id : " . $e->getMessage());
        }
    }

    /**
     * Trouve les appartements par owner_id
     * @param int $ownerId
     * @return array
     */
    public static function findByOwnerId($ownerId)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT * FROM apartments WHERE owner_id = ? AND is_deleted = 0');
            $stmt->execute([$ownerId]);
            $apartments = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $apartments[] = self::fromData($data);
            }
            return $apartments;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche des appartements par owner_id : " . $e->getMessage());
        }
    }

    /**
     * Récupère le bâtiment de l’appartement
     * @return Building|null
     */
    public function building()
    {
        return Building::find($this->building_id);
    }

    /**
     * Récupère le propriétaire de l’appartement
     * @return Owner|null
     */
    public function owner()
    {
        return Owner::find($this->owner_id);
    }

    /**
     * Compte les appartements disponibles (non loués, non supprimés)
     * @return int
     */
    public static function countAvailable()
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('
                SELECT COUNT(*) 
                FROM apartments 
                WHERE is_deleted = 0 
                AND id NOT IN (SELECT apartment_id FROM leases WHERE is_active = 1 AND is_deleted = 0)
            ');
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors du comptage des appartements disponibles : " . $e->getMessage());
        }
    }

    /**
     * Compte les nouveaux appartements disponibles ajoutés ce mois
     * @return int
     */
    public static function countNewAvailableThisMonth()
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('
                SELECT COUNT(*) 
                FROM apartments 
                WHERE is_deleted = 0 
                AND created_at >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
                AND id NOT IN (SELECT apartment_id FROM leases WHERE is_active = 1 AND is_deleted = 0)
            ');
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors du comptage des nouveaux appartements disponibles : " . $e->getMessage());
        }
    }

    /**
     * Compte le nombre d'appartements disponibles pour une agence spécifique
     * @param int $agency_id
     * @return int
     */
    public static function countAvailableByAgency($agency_id)
    {
        try {
            $pdo = Database::getInstance();
            $query = "
                SELECT COUNT(*)
                FROM apartments a
                JOIN buildings b ON a.building_id = b.id
                LEFT JOIN leases l ON a.id = l.apartment_id AND l.is_active = 1 AND l.is_deleted = 0
                WHERE a.is_deleted = 0 AND b.agency_id = ?
            ";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$agency_id]);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors du comptage des appartements disponibles pour une agence spécifique : " . $e->getMessage());
        }
    }

    /**
     * Compte le nombre d'appartements disponibles pour un agent spécifique dans une agence
     * @param int $agent_id
     * @param int $agency_id
     * @return int
     */
    public static function countAvailableByAgent($agent_id, $agency_id)
    {
        try {
            $pdo = Database::getInstance();
            $query = "
                SELECT COUNT(*)
                FROM apartments a
                JOIN buildings b ON a.building_id = b.id
                LEFT JOIN leases l ON a.id = l.apartment_id AND l.is_active = 1 AND l.is_deleted = 0
                WHERE a.is_deleted = 0 AND a.agent_id = ? AND b.agency_id = ?
            ";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$agent_id, $agency_id]);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors du comptage des appartements disponibles pour un agent spécifique : " . $e->getMessage());
        }
    }

    /**
     * Compte le nombre d'appartements loués pour un propriétaire spécifique
     * @param int $owner_id
     * @return int
     */
    public static function countRentedByOwner($owner_id)
    {
        try {
            $pdo = Database::getInstance();
            $query = "
                SELECT COUNT(*)
                FROM apartments a
                JOIN leases l ON a.id = l.apartment_id
                WHERE l.is_active = 1 AND a.owner_id = ? AND a.is_deleted = 0
            ";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$owner_id]);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors du comptage des appartements loués pour un propriétaire spécifique : " . $e->getMessage());
        }
    }

    /**
     * Compte le nombre d'appartements disponibles pour un propriétaire spécifique
     * @param int $owner_id
     * @return int
     */
    public static function countAvailableByOwner($owner_id)
    {
        try {
            $pdo = Database::getInstance();
            $query = "
                SELECT COUNT(*)
                FROM apartments a
                LEFT JOIN leases l ON a.id = l.apartment_id AND l.is_active = 1 AND l.is_deleted = 0
                WHERE a.is_deleted = 0 AND a.owner_id = ?
            ";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$owner_id]);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors du comptage des appartements disponibles pour un propriétaire spécifique : " . $e->getMessage());
        }
    }

    /**
     * Compte le nombre d'appartements occupés par un locataire spécifique
     * @param int $tenant_id
     * @return int
     */
    public static function countOccupiedByTenant($tenant_id)
    {
        try {
            $pdo = Database::getInstance();
            $query = "
                SELECT COUNT(*)
                FROM apartments a
                JOIN leases l ON a.id = l.apartment_id
                WHERE l.tenant_id = ? AND l.is_active = 1 AND a.is_deleted = 0
            ";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$tenant_id]);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors du comptage des appartements occupés par un locataire spécifique : " . $e->getMessage());
        }
    }
}