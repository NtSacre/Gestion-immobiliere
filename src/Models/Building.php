<?php
namespace App\Models;

use App\Config\Database;
use PDO;
use PDOException;

/**
 * Modèle pour la table buildings
 * ImmoApp (L2GIB, 2024-2025)
 * Représente un bâtiment géré par une agence
 */
class Building
{
    private $pdo;
    protected $id;
    protected $agency_id;
    protected $name;
    protected $address;
    protected $city;
    protected $postal_code;
    protected $country;
    protected $building_type_id;
    protected $construction_year;
    protected $created_at;
    protected $updated_at;
    protected $deleted_at;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    // Getters
    public function getId() { return $this->id; }
    public function getAgencyId() { return $this->agency_id; }
    public function getName() { return $this->name; }
    public function getAddress() { return $this->address; }
    public function getCity() { return $this->city; }
    public function getPostalCode() { return $this->postal_code; }
    public function getCountry() { return $this->country; }
    public function getBuildingTypeId() { return $this->building_type_id; }
    public function getConstructionYear() { return $this->construction_year; }
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedAt() { return $this->updated_at; }
    public function getDeletedAt() { return $this->deleted_at; }

    // Protected setters
    protected function setId($id) { $this->id = $id; }
    protected function setAgencyId($agency_id) { $this->agency_id = $agency_id; }
    protected function setName($name) { $this->name = $name; }
    protected function setAddress($address) { $this->address = $address; }
    protected function setCity($city) { $this->city = $city; }
    protected function setPostalCode($postal_code) { $this->postal_code = $postal_code; }
    protected function setCountry($country) { $this->country = $country; }
    protected function setBuildingTypeId($building_type_id) { $this->building_type_id = $building_type_id; }
    protected function setConstructionYear($construction_year) { $this->construction_year = $construction_year; }
    protected function setCreatedAt($created_at) { $this->created_at = $created_at; }
    protected function setUpdatedAt($updated_at) { $this->updated_at = $updated_at; }
    protected function setDeletedAt($deleted_at) { $this->deleted_at = $deleted_at; }

    /**
     * Crée un objet Building à partir des données de la base
     * @param array $data
     * @return Building
     */
    protected static function fromData(array $data): Building
    {
        $building = new self();
        $building->setId($data['id']);
        $building->setAgencyId($data['agency_id']);
        $building->setName($data['name']);
        $building->setAddress($data['address']);
        $building->setCity($data['city']);
        $building->setPostalCode($data['postal_code']);
        $building->setCountry($data['country']);
        $building->setBuildingTypeId($data['building_type_id']);
        $building->setConstructionYear($data['construction_year'] ?? null);
        $building->setCreatedAt($data['created_at']);
        $building->setUpdatedAt($data['updated_at'] ?? null);
        $building->setDeletedAt($data['deleted_at'] ?? null);
        return $building;
    }

    /**
     * Trouve un bâtiment par ID
     * @param int $id
     * @return Building|null
     */
    public static function find($id)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT * FROM buildings WHERE id = ? AND is_deleted IS FALSE');
            $stmt->execute([$id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? self::fromData($data) : null;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche du bâtiment : " . $e->getMessage());
        }
    }

    /**
     * Alias pour find
     * @param int $id
     * @return Building|null
     */
    public static function findById($id)
    {
        return self::find($id);
    }

    /**
     * Récupère tous les bâtiments
     * @return array
     */
    public static function get()
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->query('SELECT * FROM buildings WHERE is_deleted IS FALSE ORDER BY name ASC');
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
     * Récupère le premier bâtiment (par nom)
     * @return Building|null
     */
    public static function first()
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->query('SELECT * FROM buildings WHERE is_deleted IS FALSE ORDER BY name ASC LIMIT 1');
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? self::fromData($data) : null;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération du premier bâtiment : " . $e->getMessage());
        }
    }

    /**
     * Crée un nouveau bâtiment
     * @param array $data
     * @return Building
     */
    public static function create(array $data)
    {
        try {
            $pdo = Database::getInstance();
            // Vérifier l’existence de l’agence
            if (!Agency::find($data['agency_id'])) {
                throw new PDOException("Agency ID invalide");
            }
            // Note : building_types non implémenté, à vérifier
            $stmt = $pdo->prepare('
                INSERT INTO buildings (
                    agency_id, name, address, city, postal_code, country, building_type_id,
                    construction_year, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ');
            $stmt->execute([
                $data['agency_id'],
                $data['name'],
                $data['address'],
                $data['city'],
                $data['postal_code'],
                $data['country'],
                $data['building_type_id'],
                $data['construction_year'] ?? null
            ]);
            $id = $pdo->lastInsertId();
            return self::find($id);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la création du bâtiment : " . $e->getMessage());
        }
    }

    /**
     * Met à jour un bâtiment
     * @param int $id
     * @param array $data
     * @return Building|null
     */
    public static function update($id, array $data)
    {
        try {
            $pdo = Database::getInstance();
            // Vérifier l’existence de l’agence si modifiée
            if (!empty($data['agency_id']) && !Agency::find($data['agency_id'])) {
                throw new PDOException("Agency ID invalide");
            }
            $existing = self::find($id);
            if (!$existing) {
                throw new PDOException("Bâtiment introuvable");
            }
            $stmt = $pdo->prepare('
                UPDATE buildings
                SET agency_id = ?, name = ?, address = ?, city = ?, postal_code = ?, country = ?,
                    building_type_id = ?, construction_year = ?, updated_at = NOW()
                WHERE id = ?
            ');
            $stmt->execute([
                $data['agency_id'] ?? $existing->getAgencyId(),
                $data['name'] ?? $existing->getName(),
                $data['address'] ?? $existing->getAddress(),
                $data['city'] ?? $existing->getCity(),
                $data['postal_code'] ?? $existing->getPostalCode(),
                $data['country'] ?? $existing->getCountry(),
                $data['building_type_id'] ?? $existing->getBuildingTypeId(),
                $data['construction_year'] ?? $existing->getConstructionYear(),
                $id
            ]);
            return self::find($id);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la mise à jour du bâtiment : " . $e->getMessage());
        }
    }

    /**
     * Supprime un bâtiment (soft delete)
     * @param int $id
     * @return bool
     */
    public static function delete($id)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('UPDATE buildings SET deleted_at = NOW() WHERE id = ?');
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la suppression du bâtiment : " . $e->getMessage());
        }
    }

    /**
     * Trouve les bâtiments par agency_id
     * @param int $agencyId
     * @return array
     */
    public static function findByAgencyId($agencyId)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT * FROM buildings WHERE agency_id = ? AND is_deleted IS FALSE');
            $stmt->execute([$agencyId]);
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
     * Récupère l’agence associée
     * @return Agency|null
     */
    public function agency()
    {
        return Agency::find($this->agency_id);
    }

    /**
     * Récupère les appartements du bâtiment
     * @return array
     */
    public function apartments()
    {
        return Apartment::findByBuildingId($this->id);
    }

/**
 * Compte le nombre total de bâtiments gérés (non supprimés)
 * @return int
 */
public static function countAll()
{
    try {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM buildings WHERE is_deleted = 0');
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    } catch (PDOException $e) {
        throw new PDOException("Erreur lors du comptage des bâtiments : " . $e->getMessage());
    }
}

/**
 * Compte les nouveaux bâtiments ajoutés ce mois
 * @return int
 */
public static function countNewThisMonth()
{
    try {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('
            SELECT COUNT(*) 
            FROM buildings 
            WHERE is_deleted = 0 
            AND created_at >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
        ');
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    } catch (PDOException $e) {
        throw new PDOException("Erreur lors du comptage des nouveaux bâtiments : " . $e->getMessage());
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