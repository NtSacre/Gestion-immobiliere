<?php
namespace App\Models;

use App\Config\Database;
use PDO;
use PDOException;

/**
 * Modèle pour la table building_types
 * ImmoApp (L2GIB, 2024-2025)
 * Représente un type de bâtiment (ex. Résidentiel, Commercial)
 */
class BuildingType
{
    private $pdo;
    protected $id;
    protected $name;
    protected $description;
    protected $created_at;
    protected $updated_at;
    protected $deleted_at;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    // Getters
    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getDescription() { return $this->description; }
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedAt() { return $this->updated_at; }
    public function getDeletedAt() { return $this->deleted_at; }

    // Protected setters
    protected function setId($id) { $this->id = $id; }
    protected function setName($name) { $this->name = $name; }
    protected function setDescription($description) { $this->description = $description; }
    protected function setCreatedAt($created_at) { $this->created_at = $created_at; }
    protected function setUpdatedAt($updated_at) { $this->updated_at = $updated_at; }
    protected function setDeletedAt($deleted_at) { $this->deleted_at = $deleted_at; }

    /**
     * Crée un objet BuildingType à partir des données de la base
     * @param array $data
     * @return BuildingType
     */
    protected static function fromData(array $data): BuildingType
    {
        $buildingType = new self();
        $buildingType->setId($data['id']);
        $buildingType->setName($data['name']);
        $buildingType->setDescription($data['description'] ?? null);
        $buildingType->setCreatedAt($data['created_at']);
        $buildingType->setUpdatedAt($data['updated_at'] ?? null);
        $buildingType->setDeletedAt($data['deleted_at'] ?? null);
        return $buildingType;
    }

    /**
     * Trouve un type de bâtiment par ID
     * @param int $id
     * @return BuildingType|null
     */
    public static function find($id)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT * FROM building_types WHERE id = ? AND deleted_at IS NULL');
            $stmt->execute([$id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? self::fromData($data) : null;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche du type de bâtiment : " . $e->getMessage());
        }
    }

    /**
     * Alias pour find
     * @param int $id
     * @return BuildingType|null
     */
    public static function findById($id)
    {
        return self::find($id);
    }

    /**
     * Récupère tous les types de bâtiments
     * @return array
     */
    public static function get()
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->query('SELECT * FROM building_types WHERE deleted_at IS NULL ORDER BY name ASC');
            $buildingTypes = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $buildingTypes[] = self::fromData($data);
            }
            return $buildingTypes;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération des types de bâtiments : " . $e->getMessage());
        }
    }

    /**
     * Récupère le premier type de bâtiment (par nom)
     * @return BuildingType|null
     */
    public static function first()
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->query('SELECT * FROM building_types WHERE deleted_at IS NULL ORDER BY name ASC LIMIT 1');
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? self::fromData($data) : null;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération du premier type de bâtiment : " . $e->getMessage());
        }
    }

    /**
     * Crée un nouveau type de bâtiment
     * @param array $data
     * @return BuildingType
     */
    public static function create(array $data)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('
                INSERT INTO building_types (name, description, created_at, updated_at)
                VALUES (?, ?, NOW(), NOW())
            ');
            $stmt->execute([
                $data['name'],
                $data['description'] ?? null
            ]);
            $id = $pdo->lastInsertId();
            return self::find($id);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la création du type de bâtiment : " . $e->getMessage());
        }
    }

    /**
     * Met à jour un type de bâtiment
     * @param int $id
     * @param array $data
     * @return BuildingType|null
     */
    public static function update($id, array $data)
    {
        try {
            $pdo = Database::getInstance();
            $existing = self::find($id);
            if (!$existing) {
                throw new PDOException("Type de bâtiment introuvable");
            }
            $stmt = $pdo->prepare('
                UPDATE building_types
                SET name = ?, description = ?, updated_at = NOW()
                WHERE id = ?
            ');
            $stmt->execute([
                $data['name'] ?? $existing->getName(),
                $data['description'] ?? $existing->getDescription(),
                $id
            ]);
            return self::find($id);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la mise à jour du type de bâtiment : " . $e->getMessage());
        }
    }

    /**
     * Supprime un type de bâtiment (soft delete)
     * @param int $id
     * @return bool
     */
    public static function delete($id)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('UPDATE building_types SET deleted_at = NOW() WHERE id = ?');
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la suppression du type de bâtiment : " . $e->getMessage());
        }
    }

    /**
     * Trouve un type de bâtiment par nom
     * @param string $name
     * @return BuildingType|null
     */
    public static function findByName($name)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT * FROM building_types WHERE name = ? AND deleted_at IS NULL');
            $stmt->execute([$name]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? self::fromData($data) : null;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche du type de bâtiment par nom : " . $e->getMessage());
        }
    }
}