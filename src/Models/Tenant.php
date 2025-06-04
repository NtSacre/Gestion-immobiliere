<?php
namespace App\Models;

use App\Config\Database;
use PDO;
use PDOException;

/**
 * Modèle pour la table tenants
 * ImmoApp (L2GIB, 2024-2025)
 * Représente un locataire lié à un utilisateur
 */
class Tenant
{
    private $pdo;
    protected $id;
    protected $user_id;
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
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedAt() { return $this->updated_at; }
    public function getDeletedAt() { return $this->deleted_at; }

    // Protected setters
    protected function setId($id) { $this->id = $id; }
    protected function setUserId($user_id) { $this->user_id = $user_id; }
    protected function setCreatedAt($created_at) { $this->created_at = $created_at; }
    protected function setUpdatedAt($updated_at) { $this->updated_at = $updated_at; }
    protected function setDeletedAt($deleted_at) { $this->deleted_at = $deleted_at; }

    /**
     * Crée un objet Tenant à partir des données de la base
     * @param array $data
     * @return Tenant
     */
    protected static function fromData(array $data): Tenant
    {
        $tenant = new self();
        $tenant->setId($data['id']);
        $tenant->setUserId($data['user_id']);
        $tenant->setCreatedAt($data['created_at']);
        $tenant->setUpdatedAt($data['updated_at'] ?? null);
        $tenant->setDeletedAt($data['deleted_at'] ?? null);
        return $tenant;
    }

    /**
     * Trouve un locataire par ID
     * @param int $id
     * @return Tenant|null
     */
    public static function find($id)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT * FROM tenants WHERE id = ? AND deleted_at IS NULL');
            $stmt->execute([$id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? self::fromData($data) : null;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche du locataire : " . $e->getMessage());
        }
    }

    /**
     * Alias pour find
     * @param int $id
     * @return Tenant|null
     */
    public static function findById($id)
    {
        return self::find($id);
    }

    /**
     * Récupère tous les locataires
     * @return array
     */
    public static function get()
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->query('SELECT * FROM tenants WHERE deleted_at IS NULL ORDER BY created_at');
            $tenants = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $tenants[] = self::fromData($data);
            }
            return $tenants;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération des locataires : " . $e->getMessage());
        }
    }

    /**
     * Récupère le premier locataire (par date de création)
     * @return Tenant|null
     */
    public static function first()
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->query('SELECT * FROM tenants WHERE deleted_at IS NULL ORDER BY created_at ASC LIMIT 1');
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? self::fromData($data) : null;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération du premier locataire : " . $e->getMessage());
        }
    }

    /**
     * Crée un nouveau locataire
     * @param array $data
     * @return Tenant
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
                INSERT INTO tenants (user_id, created_at, updated_at)
                VALUES (?, NOW(), NOW())
            ');
            $stmt->execute([$data['user_id']]);
            $id = $pdo->lastInsertId();
            return self::find($id);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la création du locataire : " . $e->getMessage());
        }
    }

    /**
     * Met à jour un locataire
     * @param int $id
     * @param array $data
     * @return Tenant|null
     */
    public static function update($id, array $data)
    {
        try {
            $pdo = Database::getInstance();
            // Vérifier l’existence de l’utilisateur si modifié
            if (!empty($data['user_id']) && !User::find($data['user_id'])) {
                throw new PDOException("User ID invalide");
            }
            $existing = self::find($id);
            if (!$existing) {
                throw new PDOException("Locataire introuvable");
            }
            $stmt = $pdo->prepare('
                UPDATE tenants SET user_id = ?, updated_at = NOW()
                WHERE id = ?
            ');
            $stmt->execute([
                $data['user_id'] ?? $existing->getUserId(),
                $id
            ]);
            return self::find($id);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la mise à jour du locataire : " . $e->getMessage());
        }
    }

    /**
     * Supprime un locataire (soft delete)
     * @param int $id
     * @return bool
     */
    public static function delete($id)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('UPDATE tenants SET deleted_at = NOW() WHERE id = ?');
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la suppression du locataire : " . $e->getMessage());
        }
    }

    /**
     * Trouve un locataire par user_id
     * @param int $userId
     * @return Tenant|null
     */
    public static function findByUserId($userId)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT * FROM tenants WHERE user_id = ? AND deleted_at IS NULL');
            $stmt->execute([$userId]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? self::fromData($data) : null;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche du locataire par user_id : " . $e->getMessage());
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