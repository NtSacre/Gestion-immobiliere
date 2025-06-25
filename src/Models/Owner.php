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
   
    protected $siret;
    protected $created_at;
    protected $updated_at;
    protected $deleted_at;
    protected $agency_id;
    protected $agent_id;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    // Getters
    public function getId() { return $this->id; }
    public function getUserId() { return $this->user_id; }
    public function getType() { return $this->type; }
   
    public function getSiret() { return $this->siret; }
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedAt() { return $this->updated_at; }
    public function getDeletedAt() { return $this->deleted_at; }
    public function getAgencyId() { return $this->agency_id; }
    public function getAgentId() { return $this->agent_id; }

    // Protected setters
    protected function setId($id) { $this->id = $id; }
    protected function setUserId($user_id) { $this->user_id = $user_id; }
    protected function setType($type) { $this->type = $type; }
   
    protected function setSiret($siret) { $this->siret = $siret; }
    protected function setCreatedAt($created_at) { $this->created_at = $created_at; }
    protected function setUpdatedAt($updated_at) { $this->updated_at = $updated_at; }
    protected function setDeletedAt($deleted_at) { $this->deleted_at = $deleted_at; }
    protected function setAgencyId($agency_id) { $this->agency_id = $agency_id; }
    protected function setAgentId($agent_id) { $this->agent_id = $agent_id; }

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
        $owner->setSiret($data['siret'] ?? null);
        $owner->setCreatedAt($data['created_at']);
        $owner->setUpdatedAt($data['updated_at'] ?? null);
        $owner->setDeletedAt($data['deleted_at'] ?? null);
       
        $owner->setAgencyId($data['agency_id'] ?? null);
        $owner->setAgentId($data['agent_id'] ?? null);
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
            $stmt = $pdo->prepare('SELECT * FROM owners WHERE id = ? AND is_deleted IS FALSE');
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
            $stmt = $pdo->query('SELECT * FROM owners WHERE is_deleted IS FALSE ORDER BY id');
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
     * Récupère toutes les propriétaires (alias de get)
     * @return array
     */
    public static function all()
    {
        return self::get();
    }

    /**
     * Récupère le premier propriétaire (par nom)
     * @return Owner|null
     */
    public static function first()
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->query('SELECT * FROM owners WHERE is_deleted IS FALSE ORDER BY id ASC LIMIT 1');
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
                INSERT INTO owners (user_id, type, siret, created_at, updated_at)
                VALUES (?, ?, ?, NOW(), NOW())
            ');
            $stmt->execute([
                $data['user_id'],
                $data['type'],
                $data['siret'] ?? null
            ]);
            $id = $pdo->lastInsertId();
            return self::find($id);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la création du propriétaire : " . $e->getMessage());
        }
    }

    /**
     * Supprime un propriétaire par user_id (suppression physique)
     * @param int $userId
     * @return bool
     */
    public static function deleteByUserId($userId)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('DELETE FROM owners WHERE user_id = ?');
            return $stmt->execute([$userId]);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la suppression du propriétaire : " . $e->getMessage());
        }
    }

    /**
     * Récupère tous les propriétaires associés à une agence spécifique.
     * @param int $agencyId ID de l'agence
     * @return array Liste des propriétaires
     */
    public static function findByAgencyId($agencyId)
    {
        try {
            $pdo = Database::getInstance();
            $query = "
                SELECT o.*, u.first_name, u.last_name
                FROM owners o
                JOIN users u ON o.user_id = u.id
                WHERE o.agency_id = ?
                AND o.is_deleted = 0
                AND u.is_deleted = 0
                ORDER BY u.last_name, u.first_name
            ";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$agencyId]);
            $owners = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $owners[] = self::fromData($data);
            }
            return $owners;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération des propriétaires par agence : " . $e->getMessage());
        }
    }

    /**
     * Effectue une suppression logique d’un propriétaire par user_id
     * @param int $userId
     * @return bool
     */
    public static function softDeleteByUserId($userId)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('UPDATE owners SET is_deleted = 1, updated_at = NOW() WHERE user_id = ?');
            return $stmt->execute([$userId]);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la suppression logique du propriétaire : " . $e->getMessage());
        }
    }

    /**
     * Vérifie si un propriétaire a été créé par un agent spécifique
     * @param int $agentId
     * @param int $userId
     * @return bool
     */
    public static function isCreatedByAgent($agentId, $userId)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM owners WHERE user_id = ? AND agent_id = ? AND is_deleted = 0');
            $stmt->execute([$userId, $agentId]);
            return (int) $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la vérification de l’agent du propriétaire : " . $e->getMessage());
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
            $user = self::find($id);
            if (!$user) {
                throw new PDOException("Utilisateur introuvable.");
            }
            if (!empty($data['user_id']) && !User::find($data['user_id'])) {
                throw new PDOException("User ID invalide");
            }
            $stmt = $pdo->prepare('
                UPDATE owners SET user_id = ?, type = ?, siret = ?, updated_at = NOW()
                WHERE id = ?
            ');
            $stmt->execute([
                $data['user_id'] ?? $user->getUserId(),
                $data['type'] ?? $user->getType(),
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
            $stmt = $pdo->prepare('SELECT * FROM owners WHERE user_id = ? AND is_deleted IS FALSE');
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

    /**
     * Compte le nombre de propriétaires associés à une agence spécifique.
     * @param int $agency_id ID de l'agence
     * @return int Nombre de propriétaires
     */
    public static function countByAgency($agency_id)
    {
        try {
            $pdo = Database::getInstance();
            $query = "
                SELECT COUNT(DISTINCT o.id)
                FROM owners o
                JOIN apartments a ON a.owner_id = o.id
                JOIN buildings b ON a.building_id = b.id
                WHERE b.agency_id = ?
            ";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$agency_id]);
            $result = $stmt->fetchColumn();
            return (int) $result;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors du comptage des propriétaires pour une agence spécifique : " . $e->getMessage());
        }
    }

    /**
     * Compte le nombre de propriétaires gérés par un agent spécifique.
     * @param int $agent_id ID de l'agent
     * @return int Nombre de propriétaires
     */
    public static function countByAgent($agent_id)
    {
        try {
            $pdo = Database::getInstance();
            $query = "
                SELECT COUNT(*)
                FROM owners
                WHERE agent_id = ?
            ";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$agent_id]);
            $result = $stmt->fetchColumn();
            return (int) $result;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors du comptage des propriétaires pour un agent spécifique : " . $e->getMessage());
        }
    }

    /**
     * Récupère tous les propriétaires associés à une agence spécifique.
     * @param int $agencyId ID de l'agence
     * @return array Liste des propriétaires
     */
    public static function findByAgencyIdWithDeletedAt($agencyId)
    {
        try {
            $pdo = Database::getInstance();
            $query = "
                SELECT o.*, u.first_name, u.last_name
                FROM owners o
                JOIN users u ON o.user_id = u.id
                WHERE o.agency_id = ?
                AND o.deleted_at IS NULL
                AND u.is_deleted = 0
                ORDER BY u.last_name, u.first_name
            ";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$agencyId]);
            $owners = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $owners[] = self::fromData($data);
            }
            return $owners;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération des propriétaires par agence : " . $e->getMessage());
        }
    }
}