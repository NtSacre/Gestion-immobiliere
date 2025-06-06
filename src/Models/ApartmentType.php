<?php
namespace App\Models;

use App\Config\Database;
use PDO;
use PDOException;

/**
 * Modèle pour la table apartment_types
 * ImmoApp (L2GIB, 2024-2025)
 * Représente un type d’appartement (ex. T1, T2, Studio)
 */
class ApartmentType
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
     * Crée un objet ApartmentType à partir des données de la base
     * @param array $data
     * @return ApartmentType
     */
    protected static function fromData(array $data): ApartmentType
    {
        $apartmentType = new self();
        $apartmentType->setId($data['id']);
        $apartmentType->setName($data['name']);
        $apartmentType->setDescription($data['description'] ?? null);
        $apartmentType->setCreatedAt($data['created_at']);
        $apartmentType->setUpdatedAt($data['updated_at'] ?? null);
        $apartmentType->setDeletedAt($data['deleted_at'] ?? null);
        return $apartmentType;
    }

    /**
     * Trouve un type d’appartement par ID
     * @param int $id
     * @return ApartmentType|null
     */
    public static function find($id)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT * FROM apartment_types WHERE id = ? AND is_deleted IS FALSE');
            $stmt->execute([$id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? self::fromData($data) : null;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche du type d’appartement : " . $e->getMessage());
        }
    }

    /**
     * Alias pour find
     * @param int $id
     * @return ApartmentType|null
     */
    public static function findById($id)
    {
        return self::find($id);
    }

    /**
     * Récupère tous les types d’appartements
     * @return array
     */
    public static function get()
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->query('SELECT * FROM apartment_types WHERE is_deleted IS FALSE ORDER BY name ASC');
            $apartmentTypes = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $apartmentTypes[] = self::fromData($data);
            }
            return $apartmentTypes;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération des types d’appartements : " . $e->getMessage());
        }
    }

    /**
     * Récupère le premier type d’appartement (par nom)
     * @return ApartmentType|null
     */
    public static function first()
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->query('SELECT * FROM apartment_types WHERE is_deleted IS FALSE ORDER BY name ASC LIMIT 1');
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? self::fromData($data) : null;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération du premier type d’appartement : " . $e->getMessage());
        }
    }

    /**
     * Crée un nouveau type d’appartement
     * @param array $data
     * @return ApartmentType
     */
    public static function create(array $data)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('
                INSERT INTO apartment_types (name, description, created_at, updated_at)
                VALUES (?, ?, NOW(), NOW())
            ');
            $stmt->execute([
                $data['name'],
                $data['description'] ?? null
            ]);
            $id = $pdo->lastInsertId();
            return self::find($id);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la création du type d’appartement : " . $e->getMessage());
        }
    }

    /**
     * Met à jour un type d’appartement
     * @param int $id
     * @param array $data
     * @return ApartmentType|null
     */
    public static function update($id, array $data)
    {
        try {
            $pdo = Database::getInstance();
            $existing = self::find($id);
            if (!$existing) {
                throw new PDOException("Type d’appartement introuvable");
            }
            $stmt = $pdo->prepare('
                UPDATE apartment_types
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
            throw new PDOException("Erreur lors de la mise à jour du type d’appartement : " . $e->getMessage());
        }
    }

    /**
     * Supprime un type d’appartement (soft delete)
     * @param int $id
     * @return bool
     */
    public static function delete($id)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('UPDATE apartment_types SET deleted_at = NOW() WHERE id = ?');
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la suppression du type d’appartement : " . $e->getMessage());
        }
    }

    /**
     * Trouve un type d’appartement par nom
     * @param string $name
     * @return ApartmentType|null
     */
    public static function findByName($name)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT * FROM apartment_types WHERE name = ? AND is_deleted IS FALSE');
            $stmt->execute([$name]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? self::fromData($data) : null;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche du type d’appartement par nom : " . $e->getMessage());
        }
    }
}