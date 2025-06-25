<?php
namespace App\Models;

use App\Config\Database;
use PDO;
use PDOException;

class Building
{
    private $pdo;
    protected $id;
    protected $agency_id;
    protected $agent_id;
    protected $owner_id;
    protected $name;
    protected $city;
    protected $neighborhood;
    protected $country;
    protected $floors;
    protected $apartment_count;
    protected $land_area;
    protected $parking;
    protected $type_id;
    protected $year_built;
    protected $status;
    protected $price;
    protected $created_at;
    protected $updated_at;
    protected $is_deleted;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    // Getters
    public function getId() { return $this->id; }
    public function getAgencyId() { return $this->agency_id; }
    public function getAgentId() { return $this->agent_id; }
    public function getOwnerId() { return $this->owner_id; }
    public function getName() { return $this->name; }
    public function getCity() { return $this->city; }
    public function getNeighborhood() { return $this->neighborhood; }
    public function getCountry() { return $this->country; }
    public function getFloors() { return $this->floors; }
    public function getApartmentCount() { return $this->apartment_count; }
    public function getLandArea() { return $this->land_area; }
    public function getParking() { return $this->parking; }
    public function getTypeId() { return $this->type_id; }
    public function getYearBuilt() { return $this->year_built; }
    public function getStatus() { return $this->status; }
    public function getPrice() { return $this->price; }
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedAt() { return $this->updated_at; }
    public function getIsDeleted() { return $this->is_deleted; } // Changed to public

    // Setters
    protected function setId($id) { $this->id = $id; }
    protected function setAgencyId($agency_id) { $this->agency_id = $agency_id; }
    protected function setAgentId($agent_id) { $this->agent_id = $agent_id; }
    protected function setOwnerId($owner_id) { $this->owner_id = $owner_id; }
    protected function setName($name) { $this->name = $name; }
    protected function setCity($city) { $this->city = $city; }
    protected function setNeighborhood($neighborhood) { $this->neighborhood = $neighborhood; }
    protected function setCountry($country) { $this->country = $country; }
    protected function setFloors($floors) { $this->floors = $floors; }
    protected function setApartmentCount($apartment_count) { $this->apartment_count = $apartment_count; }
    protected function setLandArea($land_area) { $this->land_area = $land_area; }
    protected function setParking($parking) { $this->parking = $parking; }
    protected function setTypeId($type_id) { $this->type_id = $type_id; }
    protected function setYearBuilt($year_built) { $this->year_built = $year_built; }
    protected function setStatus($status) { $this->status = $status; }
    protected function setPrice($price) { $this->price = $price; }
    protected function setCreatedAt($created_at) { $this->created_at = $created_at; }
    protected function setUpdatedAt($updated_at) { $this->updated_at = $updated_at; }
    protected function setIsDeleted($is_deleted) { $this->is_deleted = $is_deleted; }

    /**
     * Crée un objet Building à partir des données de la base.
     * @param array $data Données de la base
     * @return Building
     */
    protected static function fromData(array $data): Building
    {
        $building = new self();
        $building->setId($data['id']);
        $building->setAgencyId($data['agency_id']);
        $building->setAgentId($data['agent_id'] ?? null);
        $building->setOwnerId($data['owner_id']);
        $building->setName($data['name']);
        $building->setCity($data['city']);
        $building->setNeighborhood($data['neighborhood'] ?? null);
        $building->setCountry($data['country']);
        $building->setFloors($data['floors']);
        $building->setApartmentCount($data['apartment_count']);
        $building->setLandArea($data['land_area'] ?? null);
        $building->setParking($data['parking']);
        $building->setTypeId($data['type_id']);
        $building->setYearBuilt($data['year_built'] ?? null);
        $building->setStatus($data['status']);
        $building->setPrice($data['price'] ?? null);
        $building->setCreatedAt($data['created_at']);
        $building->setUpdatedAt($data['updated_at'] ?? null);
        $building->setIsDeleted($data['is_deleted'] ?? 0);
        return $building;
    }

    /**
     * Trouve un bâtiment par ID.
     * @param int $id
     * @return Building|null
     */
    public static function find($id): ?Building
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT * FROM buildings WHERE id = ? AND is_deleted = 0');
            $stmt->execute([$id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? self::fromData($data) : null;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche du bâtiment : " . $e->getMessage());
        }
    }

    /**
     * Alias pour find.
     * @param int $id
     * @return Building|null
     */
    public static function findById($id): ?Building
    {
        return self::find($id);
    }

    /**
     * Récupère tous les bâtiments avec pagination et filtres.
     * @param string $search
     * @param int $limit
     * @param int $offset
     * @param array $statuses
     * @return array
     */
    public static function getAll($search = '', $limit = 10, $offset = 0, $statuses = []): array
    {
        try {
            $pdo = Database::getInstance();
            $query = 'SELECT * FROM buildings WHERE is_deleted = 0';
            $params = [];
            if ($search) {
                $query .= ' AND (name LIKE ? OR city LIKE ? OR country LIKE ?)';
                $params = ["%$search%", "%$search%", "%$search%"];
            }
            if (!empty($statuses)) {
                $query .= ' AND status IN (' . implode(',', array_fill(0, count($statuses), '?')) . ')';
                $params = array_merge($params, $statuses);
            }
            $query .= ' ORDER BY name ASC LIMIT ? OFFSET ?';
            $params[] = $limit;
            $params[] = $offset;

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $buildings = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $buildings[] = self::fromData($data);
            }
            return $buildings;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération des bâtiments : " . $e->getMessage());
        }
    }

    /**
     * Compte tous les bâtiments non supprimés.
     * @param string $search
     * @param array $statuses
     * @return int
     */
    public static function countAll($search = '', $statuses = []): int
    {
        try {
            $pdo = Database::getInstance();
            $query = 'SELECT COUNT(*) FROM buildings WHERE is_deleted = 0';
            $params = [];
            if ($search) {
                $query .= ' AND (name LIKE ? OR city LIKE ? OR country LIKE ?)';
                $params = ["%$search%", "%$search%", "%$search%"];
            }
            if (!empty($statuses)) {
                $query .= ' AND status IN (' . implode(',', array_fill(0, count($statuses), '?')) . ')';
                $params = array_merge($params, $statuses);
            }
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors du comptage des bâtiments : " . $e->getMessage());
        }
    }

    /**
     * Trouve les bâtiments par agency_id avec pagination et filtres.
     * @param int $agencyId
     * @param string $search
     * @param int $limit
     * @param int $offset
     * @param array $statuses
     * @return array
     */
    public static function findByAgencyId($agencyId, $search = '', $limit = 10, $offset = 0, $statuses = []): array
    {
        try {
            $pdo = Database::getInstance();
            $query = 'SELECT * FROM buildings WHERE agency_id = ? AND is_deleted = 0';
            $params = [$agencyId];
            if ($search) {
                $query .= ' AND (name LIKE ? OR city LIKE ? OR country LIKE ?)';
                $params = array_merge($params, ["%$search%", "%$search%", "%$search%"]);
            }
            if (!empty($statuses)) {
                $query .= ' AND status IN (' . implode(',', array_fill(0, count($statuses), '?')) . ')';
                $params = array_merge($params, $statuses);
            }
            $query .= ' ORDER BY name ASC LIMIT ? OFFSET ?';
            $params[] = $limit;
            $params[] = $offset;

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $buildings = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $buildings[] = self::fromData($data);
            }
            return $buildings;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche des bâtiments par agency_id : " . $e->getMessage());
        }
    }

    /**
     * Trouve les bâtiments par agent_id et agency_id avec pagination et filtres.
     * @param int $agentId
     * @param int $agencyId
     * @param string $search
     * @param int $limit
     * @param int $offset
     * @param array $statuses
     * @return array
     */
    public static function findByAgentId($agentId, $agencyId, $search = '', $limit = 10, $offset = 0, $statuses = []): array
    {
        try {
            $pdo = Database::getInstance();
            $query = 'SELECT * FROM buildings WHERE agent_id = ? AND agency_id = ? AND is_deleted = 0';
            $params = [$agentId, $agencyId];
            if ($search) {
                $query .= ' AND (name LIKE ? OR city LIKE ? OR country LIKE ?)';
                $params = array_merge($params, ["%$search%", "%$search%", "%$search%"]);
            }
            if (!empty($statuses)) {
                $query .= ' AND status IN (' . implode(',', array_fill(0, count($statuses), '?')) . ')';
                $params = array_merge($params, $statuses);
            }
            $query .= ' ORDER BY name ASC LIMIT ? OFFSET ?';
            $params[] = $limit;
            $params[] = $offset;

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $buildings = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $buildings[] = self::fromData($data);
            }
            return $buildings;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche des bâtiments par agent_id : " . $e->getMessage());
        }
    }

    /**
     * Met à jour un bâtiment existant.
     * @param int $id
     * @param array $data
     * @return bool
     */
    public static function update($id, array $data): bool
    {
        try {
            $pdo = Database::getInstance();
            $query = 'UPDATE buildings SET 
                agency_id = ?, agent_id = ?, owner_id = ?, name = ?, city = ?, neighborhood = ?, 
                country = ?, floors = ?, apartment_count = ?, land_area = ?, parking = ?, 
                type_id = ?, year_built = ?, status = ?, price = ?, updated_at = NOW()
                WHERE id = ? AND is_deleted = 0';
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                $data['agency_id'],
                $data['agent_id'] ?? null,
                $data['owner_id'],
                $data['name'],
                $data['city'],
                $data['neighborhood'] ?? null,
                $data['country'],
                $data['floors'],
                $data['apartment_count'],
                $data['land_area'] ?? null,
                $data['parking'],
                $data['type_id'],
                $data['year_built'] ?? null,
                $data['status'],
                $data['price'] ?? null,
                $id
            ]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la mise à jour du bâtiment : " . $e->getMessage());
        }
    }

    /**
     * Supprime un bâtiment (suppression logique).
     * @param int $id
     * @return bool
     */
    public static function delete($id): bool
    {
        try {
            $pdo = Database::getInstance();
            $query = 'UPDATE buildings SET is_deleted = 1, updated_at = NOW() WHERE id = ?';
            $stmt = $pdo->prepare($query);
            $stmt->execute([$id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la suppression du bâtiment : " . $e->getMessage());
        }
    }

    /**
     * Récupère le propriétaire associé au bâtiment.
     * @return Owner|null
     */
    public function owner(): ?Owner
    {
        return Owner::findById($this->owner_id);
    }

    /**
     * Récupère l’utilisateur associé au propriétaire (pour compatibilité).
     * @return User|null
     * @deprecated Use owner()->user() instead
     */
    public function user(): ?User
    {
        $owner = $this->owner();
        return $owner ? $owner->user() : null;
    }

    /**
     * Crée un nouveau bâtiment.
     * @param array $data Données du bâtiment
     * @return Building
     * @throws PDOException
     */
    public static function create(array $data): Building
    {
        try {
            $pdo = Database::getInstance();
            
            // Vérifications préalables
            if (!Agency::find($data['agency_id'])) {
                throw new PDOException("ID d'agence invalide : {$data['agency_id']}");
            }
            if (isset($data['agent_id']) && $data['agent_id'] && !User::find($data['agent_id'])) {
                throw new PDOException("ID d'agent invalide : {$data['agent_id']}");
            }
            if (!Owner::findById($data['owner_id'])) {
                throw new PDOException("ID de propriétaire invalide : {$data['owner_id']}");
            }
            if (!BuildingType::find($data['type_id'])) {
                throw new PDOException("ID de type de bâtiment invalide : {$data['type_id']}");
            }

            $stmt = $pdo->prepare('
                INSERT INTO buildings (
                    agency_id, agent_id, owner_id, name, city, neighborhood, country,
                    floors, apartment_count, land_area, parking, type_id, year_built,
                    status, price, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ');
            
            $stmt->execute([
                $data['agency_id'],
                $data['agent_id'] ?? null,
                $data['owner_id'],
                $data['name'],
                $data['city'],
                $data['neighborhood'] ?? null,
                $data['country'],
                $data['floors'],
                $data['apartment_count'],
                $data['land_area'] ?? null,
                $data['parking'],
                $data['type_id'],
                $data['year_built'] ?? null,
                $data['status'],
                $data['price'] ?? null
            ]);

            $id = $pdo->lastInsertId();
            $building = self::find($id);
            if (!$building) {
                throw new PDOException("Bâtiment non trouvé après création, ID : $id");
            }
            return $building;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la création du bâtiment : " . $e->getMessage());
        }
    }
     /**
     * Compte le nombre de bâtiments pour une agence spécifique.
     *
     * @param int $agency_id ID de l'agence
     * @return int Nombre de bâtiments
     */
    public static function countByAgency($agency_id)
    {
        try {
            $pdo = Database::getInstance();
            $query = "
                SELECT COUNT(*)
                FROM buildings
                WHERE agency_id = ?
            ";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$agency_id]);
            $result = $stmt->fetchColumn();
            return (int) $result;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors du comptage des bâtiments pour une agence spécifique : " . $e->getMessage());
        }
    }

    
    /**
     * Compte le nombre de bâtiments pour un agent spécifique dans une agence.
     *
     * @param int $agent_id ID de l'agent
     * @param int $agency_id ID de l'agence
     * @return int Nombre de bâtiments
     */
    public static function countByAgent($agent_id, $agency_id)
    {
        try {
            $pdo = Database::getInstance();
            $query = "
                SELECT COUNT(*)
                FROM buildings
                WHERE agent_id = ? AND agency_id = ?
            ";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$agent_id, $agency_id]);
            $result = $stmt->fetchColumn();
            return (int) $result;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors du comptage des bâtiments pour un agent spécifique : " . $e->getMessage());
        }
    }
}