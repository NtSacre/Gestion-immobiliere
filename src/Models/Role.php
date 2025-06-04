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

    // Setters
    public function setName($name) { $this->name = $name; }
   public function setDescription($description) { $this->description = $description; }
    public function setCreatedAt($created_at) { $this->created_at = $created_at; }
    public function setUpdatedAt($updated_at) { $this->updated_at = $updated_at; }

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
            if ($data) {
                $role = new self();
                $role->id = $data['id'];
                $role->name = $data['name'];
                $role->description = $data['description'];
                $role->created_at = $data['created_at'];
                $role->updated_at = $data['updated_at'];
                return $role;
            }
            return null;
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
     * Récupère tous les rôles
     * @return array
     */
    public static function get()
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->query('SELECT * FROM roles ORDER BY name');
            $roles = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $role = new self();
                $role->id = $data['id'];
                $role->name = $data['name'];
                $role->description = $data['description'];
                $role->created_at = $data['created_at'];
                $role->updated_at = $data['updated_at'];
                $roles[] = $role;
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
            $stmt = $pdo->query('SELECT * FROM roles ORDER BY name LIMIT 1');
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($data) {
                $role = new self();
                $role->id = $data['id'];
                $role->name = $data['name'];
                $role->description = $data['description'];
                $role->created_at = $data['created_at'];
                $role->updated_at = $data['updated_at'];
                return $role;
            }
            return null;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération du premier rôle : " . $e->getMessage());
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
            $stmt = $pdo->prepare('INSERT INTO roles (name, description, created_at, updated_at) VALUES (?, ?, NOW(), NOW())');
            $stmt->execute([
    'name' => $data['name'],
    'description' => $data['description']
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
            $stmt = $pdo->prepare('UPDATE roles SET name = ?, updated_at = NOW() WHERE id = ?');
            $stmt->execute([$data['name'], $id]);
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
            if ($data) {
                $role = new self();
                $role->id = $data['id'];
                $role->name = $data['name'];
                $role->created_at = $data['created_at'];
                $role->updated_at = $data['updated_at'];
                return $role;
            }
            return null;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche du rôle par nom : " . $e->getMessage());
        }
    }
}