<?php
namespace App\Models;

use App\Config\Database;
use PDO;
use PDOException;

/**
 * Modèle pour la table roles
 * ImmoApp (L2GIB, 2024-2025)
 */
class Role
{
    private $pdo;
    protected $id;
    protected $name;
    protected $description;
    protected $created_at;
    protected $updated_at;

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

    // Protected setters
    protected function setId($id) { $this->id = $id; }
    protected function setName($name) { $this->name = $name; }
    protected function setDescription(?string $description) { $this->description = $description; }
    protected function setCreatedAt($created_at) { $this->created_at = $created_at; }
    protected function setUpdatedAt($updated_at) { $this->updated_at = $updated_at; }

    /**
     * Crée un objet Role à partir des données de la base
     * @param array $data
     * @return Role
     */
    protected static function fromData(array $data): Role
    {
        $role = new self();
        $role->setId($data['id']);
        $role->setName($data['name']);
        $role->setDescription($data['description'] ?? null);
        $role->setCreatedAt($data['created_at']);
        $role->setUpdatedAt($data['updated_at'] ?? null);
        return $role;
    }

    /**
     * Trouve un rôle par ID
     * @param int $id
     * @return Role|null
     */
    public static function find($id)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT * FROM roles WHERE id = ?');
            $stmt->execute([$id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? self::fromData($data) : null;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche du rôle : " . $e->getMessage());
        }
    }

    /**
     * Alias pour find
     * @param int $id
     * @return Role|null
     */
    public static function findById($id)
    {
        return self::find($id);
    }

    /**
     * Trouve un rôle par nom
     * @param string $name
     * @return Role|null
     */
    public static function findByName($name)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT * FROM roles WHERE name = ?');
            $stmt->execute([$name]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? self::fromData($data) : null;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche du rôle par nom : " . $e->getMessage());
        }
    }

    /**
     * Récupère tous les rôles
     * @return array Liste des rôles
     */
    public static function getAll()
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->query('SELECT * FROM roles ORDER BY name ASC');
            $roles = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $roles[] = self::fromData($data);
            }
            return $roles;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération des rôles : " . $e->getMessage());
        }
    }

    /**
     * Récupère le premier rôle (par ordre alphabétique)
     * @return Role|null
     */
    public static function first()
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->query('SELECT * FROM roles ORDER BY name ASC LIMIT 1');
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? self::fromData($data) : null;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération du premier rôle : " . $e->getMessage());
        }
    }

    /**
     * Récupère les rôles par noms
     * @param array $names Liste des noms de rôles
     * @return array Liste des rôles correspondants
     */
    public static function getByNames(array $names)
    {
        try {
            $pdo = Database::getInstance();
            $placeholders = implode(',', array_fill(0, count($names), '?'));
            $stmt = $pdo->prepare("SELECT * FROM roles WHERE name IN ($placeholders) ORDER BY name ASC");
            $stmt->execute($names);
            $roles = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $roles[] = self::fromData($data);
            }
            return $roles;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération des rôles par noms : " . $e->getMessage());
        }
    }

    /**
     * Vérifie si un rôle existe par ID
     * @param int $id
     * @return bool
     */
    public static function exists($id)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM roles WHERE id = ?');
            $stmt->execute([$id]);
            return (int) $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la vérification de l'existence du rôle : " . $e->getMessage());
        }
    }

    /**
     * Récupère le nom d’un rôle par ID
     * @param int $id
     * @return string|null
     */
    public static function getNameById($id)
    {
        try {
            $role = self::find($id);
            return $role ? $role->getName() : null;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération du nom du rôle : " . $e->getMessage());
        }
    }

    /**
     * Crée un nouveau rôle
     * @param array $data
     * @return Role
     */
    public static function create(array $data)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('
                INSERT INTO roles (name, description, created_at, updated_at)
                VALUES (?, ?, NOW(), NOW())
            ');
            $stmt->execute([
                $data['name'],
                $data['description'] ?? null
            ]);
            $id = $pdo->lastInsertId();
            return self::find($id);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la création du rôle : " . $e->getMessage());
        }
    }

    /**
     * Met à jour un rôle
     * @param int $id
     * @param array $data
     * @return Role|null
     */
    public static function update($id, array $data)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('
                UPDATE roles 
                SET name = ?, description = ?, updated_at = NOW() 
                WHERE id = ?
            ');
            $stmt->execute([
                $data['name'],
                $data['description'] ?? null,
                $id
            ]);
            return self::find($id);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la mise à jour du rôle : " . $e->getMessage());
        }
    }

    /**
     * Supprime un rôle
     * @param int $id
     * @return bool
     */
    public static function delete($id)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('DELETE FROM roles WHERE id = ?');
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la suppression du rôle : " . $e->getMessage());
        }
    }
}
?>