<?php
namespace App\Models;

use App\Config\Database;
use PDO;
use PDOException;

/**
 * Modèle pour la table profile_images
 * ImmoApp (L2GIB, 2024-2025)
 * Représente une image de profil pour un utilisateur
 */
class ProfileImages
{
    private $pdo;
    protected $id;
    protected $user_id;
    protected $path;
    protected $alt_text;
    protected $created_at;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    // Getters
    public function getId() { return $this->id; }
    public function getUserId() { return $this->user_id; }
    public function getPath() { return $this->path; }
    public function getAltText() { return $this->alt_text; }
    public function getCreatedAt() { return $this->created_at; }

    // Protected setters
    protected function setId($id) { $this->id = $id; }
    protected function setUserId($user_id) { $this->user_id = $user_id; }
    protected function setPath($path) { $this->path = $path; }
    protected function setAltText($alt_text) { $this->alt_text = $alt_text; }
    protected function setCreatedAt($created_at) { $this->created_at = $created_at; }

    /**
     * Crée un objet ProfileImages à partir des données de la base
     * @param array $data
     * @return ProfileImages
     */
    protected static function fromData(array $data): ProfileImages
    {
        $profileImage = new self();
        $profileImage->setId($data['id']);
        $profileImage->setUserId($data['user_id']);
        $profileImage->setPath($data['path']);
        $profileImage->setAltText($data['alt_text'] ?? null);
        $profileImage->setCreatedAt($data['created_at']);
        return $profileImage;
    }

    /**
     * Trouve une image de profil par ID
     * @param int $id
     * @return ProfileImages|null
     */
    public static function find($id)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT * FROM profile_images WHERE id = ?');
            $stmt->execute([$id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? self::fromData($data) : null;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche de l’image de profil : " . $e->getMessage());
        }
    }

    /**
     * Alias pour find
     * @param int $id
     * @return ProfileImages|null
     */
    public static function findById($id)
    {
        return self::find($id);
    }

    /**
     * Récupère toutes les images de profil
     * @return array
     */
    public static function get()
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->query('SELECT * FROM profile_images ORDER BY created_at DESC');
            $profileImages = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $profileImages[] = self::fromData($data);
            }
            return $profileImages;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération des images de profil : " . $e->getMessage());
        }
    }

    /**
     * Récupère la première image de profil (par date de création)
     * @return ProfileImages|null
     */
    public static function first()
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->query('SELECT * FROM profile_images ORDER BY created_at ASC LIMIT 1');
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? self::fromData($data) : null;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération de la première image de profil : " . $e->getMessage());
        }
    }

    /**
     * Crée une nouvelle image de profil
     * @param array $data
     * @return ProfileImages
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
                INSERT INTO profile_images (
                    user_id, path, alt_text, created_at
                ) VALUES (?, ?, ?, NOW())
            ');
            $stmt->execute([
                $data['user_id'],
                $data['path'],
                $data['alt_text'] ?? null
            ]);
            $id = $pdo->lastInsertId();
            return self::find($id);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la création de l’image de profil : " . $e->getMessage());
        }
    }

    /**
     * Met à jour une image de profil
     * @param int $id
     * @param array $data
     * @return ProfileImages|null
     */
    public static function update($id, array $data)
    {
        try {
            $pdo = Database::getInstance();
            $existing = self::find($id);
            if (!$existing) {
                throw new PDOException("Image de profil introuvable");
            }
            // Vérifier l’existence de l’utilisateur si modifié
            if (!empty($data['user_id']) && !User::find($data['user_id'])) {
                throw new PDOException("User ID invalide");
            }
            $stmt = $pdo->prepare('
                UPDATE profile_images
                SET user_id = ?, path = ?, alt_text = ?
                WHERE id = ?
            ');
            $stmt->execute([
                $data['user_id'] ?? $existing->getUserId(),
                $data['path'] ?? $existing->getPath(),
                $data['alt_text'] ?? $existing->getAltText(),
                $id
            ]);
            return self::find($id);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la mise à jour de l’image de profil : " . $e->getMessage());
        }
    }

    /**
     * Supprime une image de profil
     * @param int $id
     * @return bool
     */
    public static function delete($id)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('DELETE FROM profile_images WHERE id = ?');
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la suppression de l’image de profil : " . $e->getMessage());
        }
    }

    /**
     * Trouve une image de profil par user_id
     * @param int $userId
     * @return ProfileImages|null
     */
    public static function findByUserId($userId)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT * FROM profile_images WHERE user_id = ?');
            $stmt->execute([$userId]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? self::fromData($data) : null;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche de l’image de profil par user_id : " . $e->getMessage());
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