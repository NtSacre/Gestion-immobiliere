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
            $stmt = $pdo->prepare('SELECT * FROM tenants WHERE id = ? AND is_deleted IS FALSE');
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
            $stmt = $pdo->query('SELECT * FROM tenants WHERE is_deleted IS FALSE ORDER BY created_at');
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
            $stmt = $pdo->query('SELECT * FROM tenants WHERE is_deleted IS FALSE ORDER BY created_at ASC LIMIT 1');
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
            $stmt = $pdo->prepare('
                INSERT INTO tenants (user_id, agent_id, agency_id, created_at, updated_at)
                VALUES (?, ?, ?, NOW(), NOW())
            ');
            $stmt->execute([
                $data['user_id'],
                $data['agent_id'] ?? null,
                $data['agency_id'] ?? null
            ]);
            $id = $pdo->lastInsertId();
            $stmt = $pdo->prepare('SELECT * FROM tenants WHERE id = ?');
            $stmt->execute([$id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return self::fromData($data);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la création du locataire : " . $e->getMessage());
        }
    }

    /**
     * Supprime un locataire par user_id (suppression physique)
     * @param int $userId
     * @return bool
     */
    public static function deleteByUserId($userId)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('DELETE FROM tenants WHERE user_id = ?');
            return $stmt->execute([$userId]);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la suppression du locataire : " . $e->getMessage());
        }
    }

    /**
     * Effectue une suppression logique d’un locataire par user_id
     * @param int $userId
     * @return bool
     */
    public static function softDeleteByUserId($userId)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('UPDATE tenants SET is_deleted = 1, updated_at = NOW() WHERE user_id = ?');
            return $stmt->execute([$userId]);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la suppression logique du locataire : " . $e->getMessage());
        }
    }

    /**
     * Vérifie si un locataire a été créé par un agent spécifique
     * @param int $agentId
     * @param int $userId
     * @return bool
     */
    public static function isCreatedByAgent($agentId, $userId)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM tenants WHERE user_id = ? AND agent_id = ? AND is_deleted = 0');
            $stmt->execute([$userId, $agentId]);
            return (int) $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la vérification de l’agent du locataire : " . $e->getMessage());
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
            $stmt = $pdo->prepare('SELECT * FROM tenants WHERE user_id = ? AND is_deleted IS FALSE');
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

 /**
 * Compte le nombre total de locataires (non supprimés)
 * @return int
 */
public static function countAll()
{
    try {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM tenants WHERE is_deleted = 0');
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    } catch (PDOException $e) {
        throw new PDOException("Erreur lors du comptage des locataires : " . $e->getMessage());
    }
}

/**
 * Compte les nouveaux locataires ajoutés ce mois
 * @return int
 */
public static function countNewThisMonth()
{
    try {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('
            SELECT COUNT(*) 
            FROM tenants 
            WHERE is_deleted = 0 
            AND created_at >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
        ');
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    } catch (PDOException $e) {
        throw new PDOException("Erreur lors du comptage des nouveaux locataires : " . $e->getMessage());
    }
}

    /**
     * Compte le nombre de locataires associés à une agence spécifique.
     *
     * @param int $agency_id ID de l'agence
     * @return int Nombre de locataires
     */
    public static function countByAgency($agency_id)
    {
        try {
            $pdo = Database::getInstance();
            $query = "
                SELECT COUNT(DISTINCT t.id)
                FROM tenants t
                JOIN leases l ON l.tenant_id = t.id
                JOIN apartments a ON l.apartment_id = a.id
                JOIN buildings b ON a.building_id = b.id
                WHERE b.agency_id = ?
            ";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$agency_id]);
            $result = $stmt->fetchColumn();
            return (int) $result;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors du comptage des locataires pour une agence spécifique : " . $e->getMessage());
        }
    }

    /**
     * Compte le nombre de locataires gérés par un agent spécifique.
     *
     * @param int $agent_id ID de l'agent
     * @return int Nombre de locataires
     */
    public static function countByAgent($agent_id)
    {
        try {
            $pdo = Database::getInstance();
            $query = "
                SELECT COUNT(*)
                FROM tenants
                WHERE agent_id = ?
            ";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$agent_id]);
            $result = $stmt->fetchColumn();
            return (int) $result;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors du comptage des locataires pour un agent spécifique : " . $e->getMessage());
        }
    }

    /**
     * Compte le nombre de nouveaux locataires ce mois pour les appartements d'un propriétaire spécifique.
     *
     * @param int $owner_id ID du propriétaire
     * @return int Nombre de nouveaux locataires
     */
    public static function countNewByOwnerThisMonth($owner_id)
    {
        try {
            $pdo = Database::getInstance();
            $query = "
                SELECT COUNT(DISTINCT t.id)
                FROM tenants t
                JOIN leases l ON l.tenant_id = t.id
                JOIN apartments a ON l.apartment_id = a.id
                WHERE a.owner_id = ? AND l.created_at >= ?
            ";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$owner_id, date('Y-m-01')]);
            $result = $stmt->fetchColumn();
            return (int) $result;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors du comptage des nouveaux locataires ce mois pour un propriétaire spécifique : " . $e->getMessage());
        }
    }
}