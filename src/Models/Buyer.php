<?php
namespace App\Models;

use App\Config\Database;
use PDO;
use PDOException;

/**
 * Modèle pour la table buyers
 * ImmoApp (L2GIB, 2024-2025)
 * Représente un acheteur lié à un utilisateur
 */
class Buyer
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
     * Crée un objet Buyer à partir des données de la base
     * @param array $data
     * @return Buyer
     */
    protected static function fromData(array $data): Buyer
    {
        $buyer = new self();
        $buyer->setId($data['id']);
        $buyer->setUserId($data['user_id']);
        $buyer->setCreatedAt($data['created_at']);
        $buyer->setUpdatedAt($data['updated_at'] ?? null);
        $buyer->setDeletedAt($data['deleted_at'] ?? null);
        return $buyer;
    }

    /**
     * Trouve un acheteur par ID
     * @param int $id
     * @return Buyer|null
     */
    public static function find($id)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT * FROM buyers WHERE id = ? AND deleted_at IS NULL');
            $stmt->execute([$id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? self::fromData($data) : null;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche de l’acheteur : " . $e->getMessage());
        }
    }

    /**
     * Alias pour find
     * @param int $id
     * @return Buyer|null
     */
    public static function findById($id)
    {
        return self::find($id);
    }

    /**
     * Récupère tous les acheteurs
     * @return array
     */
    public static function get()
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->query('SELECT * FROM buyers WHERE deleted_at IS NULL ORDER BY created_at DESC');
            $buyers = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $buyers[] = self::fromData($data);
            }
            return $buyers;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération des acheteurs : " . $e->getMessage());
        }
    }

    /**
     * Récupère le premier acheteur (par date de création)
     * @return Buyer|null
     */
    public static function first()
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->query('SELECT * FROM buyers WHERE deleted_at IS NULL ORDER BY created_at ASC LIMIT 1');
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? self::fromData($data) : null;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération du premier acheteur : " . $e->getMessage());
        }
    }

    /**
     * Crée un nouvel acheteur
     * @param array $data
     * @return Buyer
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
                INSERT INTO buyers (user_id, created_at, updated_at)
                VALUES (?, NOW(), NOW())
            ');
            $stmt->execute([$data['user_id']]);
            $id = $pdo->lastInsertId();
            return self::find($id);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la création de l’acheteur : " . $e->getMessage());
        }
    }

    /**
     * Met à jour un acheteur
     * @param int $id
     * @param array $data
     * @return Buyer|null
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
                throw new PDOException("Acheteur introuvable");
            }
            $stmt = $pdo->prepare('
                UPDATE buyers SET user_id = ?, updated_at = NOW()
                WHERE id = ?
            ');
            $stmt->execute([
                $data['user_id'] ?? $existing->getUserId(),
                $id
            ]);
            return self::find($id);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la mise à jour de l’acheteur : " . $e->getMessage());
        }
    }

    /**
     * Supprime un acheteur (soft delete)
     * @param int $id
     * @return bool
     */
    public static function delete($id)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('UPDATE buyers SET deleted_at = NOW() WHERE id = ?');
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la suppression de l’acheteur : " . $e->getMessage());
        }
    }

    /**
     * Trouve un acheteur par user_id
     * @param int $userId
     * @return Buyer|null
     */
    public static function findByUserId($userId)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT * FROM buyers WHERE user_id = ? AND deleted_at IS NULL');
            $stmt->execute([$userId]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? self::fromData($data) : null;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche de l’acheteur par user_id : " . $e->getMessage());
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