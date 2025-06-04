<?php
namespace App\Models;

use App\Config\Database;
use PDO;
use PDOException;

/**
 * Modèle pour la table owners
 * ImmoApp (L2GIB, 2024-2025)
 * Représente un propriétaire lié à un utilisateur
 */
class Owner
{
    private $pdo;
    protected $id;
    protected $user_id;
    protected $type;
    protected $name;
    protected $siret;
    protected $created_at;
    protected $updated_at;
    protected $deleted_at;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    // Getters
    public function getId() { return $this->id; }
    public function getUserId() { return $this->user_id; }
    public function getType() { return $this->type; }
    public function getName() { return $this->name; }
    public function getSiret() { return $this->siret; }
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedAt() { return $this->updated_at; }
    public function getDeletedAt() { return $this->deleted_at; }

    // Protected setters
    protected function setId($id) { $this->id = $id; }
    protected function setUserId($user_id) { $this->user_id = $user_id; }
    protected function setType($type) { $this->type = $type; }
    protected function setName($name) { $this->name = $name; }
    protected function setSiret($siret) { $this->siret = $siret; }
    protected function setCreatedAt($created_at) { $this->created_at = $created_at; }
    protected function setUpdatedAt($updated_at) { $this->updated_at = $updated_at; }
    protected function setDeletedAt($deleted_at) { $this->deleted_at = $deleted_at; }

    /**
     * Crée un objet Owner à partir des données de la base
     * @param array $data
     * @return Owner
     */
    protected static function fromData(array $data): Owner
    {
        $owner = new self();
        $owner->setId($data['id']);
        $owner->setUserId($data['user_id']);
        $owner->setType($data['type']);
        $owner->setName($data['name']);
        $owner->setSiret($data['siret'] ?? null);
        $owner->setCreatedAt($data['created_at']);
        $owner->setUpdatedAt($data['updated_at'] ?? null);
        $owner->setDeletedAt($data['deleted_at'] ?? null);
        return $owner;
    }

    /**
     * Trouve un propriétaire par ID
     * @param int $id
     * @return Owner|null
     */
    public static function find($id)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT * FROM owners WHERE id = ? AND deleted_at IS NULL');
            $stmt->execute([$id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? self::fromData($data) : null;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche du propriétaire : " . $e->getMessage());
        }
    }

    /**
     * Alias pour find
     * @param int $id
     * @return Owner|null
     */
    public static function findById($id)
    {
        return self::find($id);
    }

    /**
     * Récupère tous les propriétaires
     * @return array
     */
    public static function get()
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->query('SELECT * FROM owners WHERE deleted_at IS NULL ORDER BY name');
            $owners = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $owners[] = self::fromData($data);
            }
            return $owners;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération des propriétaires : " . $e->getMessage());
        }
    }

    /**
     * Récupère le premier propriétaire (par nom)
     * @return Owner|null
     */
    public static function first()
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->query('SELECT * FROM owners WHERE deleted_at IS NULL ORDER BY name ASC LIMIT 1');
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? self::fromData($data) : null;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération du premier propriétaire : " . $e->getMessage());
        }
    }

    /**
     * Crée un nouveau propriétaire
     * @param array $data
     * @return Owner
     */
    public static function create(array $data)
    {
        try {
            $pdo = Database::getInstance();
            // Vérifier l’existence de l’utilisateur
            if (!User::find($data['user_id'])) {
                throw new PDOException("User ID invalide");
            }
            $stmt = $pdo->prepare('
                INSERT INTO owners (user_id, type, name, siret, created_at, updated_at)
                VALUES (?, ?, ?, ?, NOW(), NOW())
            ');
            $stmt->execute([
                $data['user_id'],
                $data['type'],
                $data['name'],
                $data['siret'] ?? null
            ]);
            $id = $pdo->lastInsertId();
            return self::find($id);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la création du propriétaire : " . $e->getMessage());
        }
    }

    /**
     * Met à jour un propriétaire
     * @param int $id
     * @param array $data
     * @return Owner|null
     */
    public static function update($id, array $data)
    {
        try {
            $pdo = Database::getInstance();

                    // On récupère d'abord l'utilisateur existant
        $user = self::find($id);
        if (!$user) {
            throw new PDOException("Utilisateur introuvable.");
        }
            // Vérifier l’existence de l’utilisateur si modifié
            if (!empty($data['user_id']) && !User::find($data['user_id'])) {
                throw new PDOException("User ID invalide");
            }
            $stmt = $pdo->prepare('
                UPDATE owners SET user_id = ?, type = ?, name = ?, siret = ?, updated_at = NOW()
                WHERE id = ?
            ');
            $stmt->execute([
                $data['user_id'] ?? $user->getUserId(),
                $data['type'] ?? $user->getType(),
                $data['name'] ?? $user->getName(),
                $data['siret'] ?? $user->getSiret(),
                $id
            ]);
            return self::find($id);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la mise à jour du propriétaire : " . $e->getMessage());
        }
    }

    /**
     * Supprime un propriétaire (soft delete)
     * @param int $id
     * @return bool
     */
    public static function delete($id)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('UPDATE owners SET deleted_at = NOW() WHERE id = ?');
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la suppression du propriétaire : " . $e->getMessage());
        }
    }

    /**
     * Trouve un propriétaire par user_id
     * @param int $userId
     * @return Owner|null
     */
    public static function findByUserId($userId)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT * FROM owners WHERE user_id = ? AND deleted_at IS NULL');
            $stmt->execute([$userId]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? self::fromData($data) : null;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche du propriétaire par user_id : " . $e->getMessage());
        }
    }

    /**
     * Récupère l’utilisateur associé
     * @return User|null
     */
    public function user()
    {
        return User::find($this->user_id);
    }
}