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
    protected $agent_id;
    protected $agency_id;
    protected $created_at;
    protected $updated_at;
    protected $is_deleted;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    // Getters
    public function getId() { return $this->id; }
    public function getUserId() { return $this->user_id; }
    public function getAgentId() { return $this->agent_id; }
    public function getAgencyId() { return $this->agency_id; }
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedAt() { return $this->updated_at; }
    public function getIsDeleted() { return $this->is_deleted; }

    // Protected setters
    protected function setId($id) { $this->id = $id; }
    protected function setUserId($user_id) { $this->user_id = $user_id; }
    protected function setAgentId($agent_id) { $this->agent_id = $agent_id; }
    protected function setAgencyId($agency_id) { $this->agency_id = $agency_id; }
    protected function setCreatedAt($created_at) { $this->created_at = $created_at; }
    protected function setUpdatedAt($updated_at) { $this->updated_at = $updated_at; }
    protected function setIsDeleted($is_deleted) { $this->is_deleted = $is_deleted; }

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
        $tenant->setAgentId($data['agent_id'] ?? null);
        $tenant->setAgencyId($data['agency_id'] ?? null);
        $tenant->setCreatedAt($data['created_at']);
        $tenant->setUpdatedAt($data['updated_at'] ?? null);
        $tenant->setIsDeleted($data['is_deleted'] ?? 0);
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
            $stmt = $pdo->prepare('SELECT * FROM tenants WHERE id = ? AND is_deleted = 0');
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
            $stmt = $pdo->query('SELECT * FROM tenants WHERE is_deleted = 0 ORDER BY created_at');
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
     * Alias pour get
     * @return array
     */
    public static function all()
    {
        return self::get();
    }

    /**
     * Récupère le premier locataire (par date de création)
     * @return Tenant|null
     */
    public static function first()
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->query('SELECT * FROM tenants WHERE is_deleted = 0 ORDER BY created_at ASC LIMIT 1');
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
            if (!User::find($data['user_id'])) {
                throw new PDOException("User ID invalide : {$data['user_id']}");
            }
            if (isset($data['agent_id']) && !User::find($data['agent_id'])) {
                throw new PDOException("Agent ID invalide : {$data['agent_id']}");
            }
            if (isset($data['agency_id']) && !Agency::find($data['agency_id'])) {
                throw new PDOException("Agency ID invalide : {$data['agency_id']}");
            }
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
            $tenant = self::find($id);
            if (!$tenant) {
                throw new PDOException("Locataire non trouvé après création, ID : $id");
            }
            return $tenant;
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
            $existing = self::find($id);
            if (!$existing) {
                throw new PDOException("Locataire introuvable");
            }
            if (isset($data['user_id']) && !User::find($data['user_id'])) {
                throw new PDOException("User ID invalide");
            }
            if (isset($data['agent_id']) && !User::find($data['agent_id'])) {
                throw new PDOException("Agent ID invalide");
            }
            if (isset($data['agency_id']) && !Agency::find($data['agency_id'])) {
                throw new PDOException("Agency ID invalide");
            }
            $stmt = $pdo->prepare('
                UPDATE tenants SET user_id = ?, agent_id = ?, agency_id = ?, updated_at = NOW()
                WHERE id = ?
            ');
            $stmt->execute([
                $data['user_id'] ?? $existing->getUserId(),
                $data['agent_id'] ?? $existing->getAgentId(),
                $data['agency_id'] ?? $existing->getAgencyId(),
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
            $stmt = $pdo->prepare('UPDATE tenants SET is_deleted = 1, updated_at = NOW() WHERE id = ?');
            $stmt->execute([$id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la suppression du locataire : " . $e->getMessage());
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
     * Trouve un locataire par user_id
     * @param int $userId
     * @return Tenant|null
     */
    public static function findByUserId($userId)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT * FROM tenants WHERE user_id = ? AND is_deleted = 0');
            $stmt->execute([$userId]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? self::fromData($data) : null;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche du locataire par user_id : " . $e->getMessage());
        }
    }

    /**
     * Trouve un locataire par lease_id
     * @param int $leaseId
     * @return Tenant|null
     */
    public static function findByLeaseId($leaseId)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('
                SELECT t.* 
                FROM tenants t
                JOIN leases l ON l.tenant_id = t.id
                WHERE l.id = ? AND t.is_deleted = 0
            ');
            $stmt->execute([$leaseId]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? self::fromData($data) : null;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche du locataire par lease_id : " . $e->getMessage());
        }
    }

    /**
     * Récupère tous les locataires associés à une agence spécifique
     * @param int $agencyId
     * @return array
     */
    public static function findByAgencyId($agencyId)
    {
        try {
            $pdo = Database::getInstance();
            $query = "
                SELECT t.*, u.first_name, u.last_name
                FROM tenants t
                JOIN users u ON t.user_id = u.id
                WHERE t.agency_id = ? AND t.is_deleted = 0 AND u.is_deleted = 0
                ORDER BY u.last_name, u.first_name
            ";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$agencyId]);
            $tenants = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $tenants[] = self::fromData($data);
            }
            return $tenants;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération des locataires par agence : " . $e->getMessage());
        }
    }

    /**
     * Récupère les locataires sans bail actif
     * @return array
     */
    public static function findAvailable()
    {
        try {
            $pdo = Database::getInstance();
            $query = "
                SELECT t.*, u.first_name, u.last_name
                FROM tenants t
                JOIN users u ON t.user_id = u.id
                WHERE t.is_deleted = 0 AND u.is_deleted = 0
                AND t.id NOT IN (
                    SELECT tenant_id FROM leases WHERE is_active = 1 AND is_deleted = 0
                )
                ORDER BY u.last_name, u.first_name
            ";
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $tenants = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $tenants[] = self::fromData($data);
            }
            return $tenants;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération des locataires disponibles : " . $e->getMessage());
        }
    }

    /**
     * Vérifie si un locataire a un bail actif
     * @return bool
     */
    public function hasActiveLease()
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('
                SELECT COUNT(*) 
                FROM leases 
                WHERE tenant_id = ? AND is_active = 1 AND is_deleted = 0
            ');
            $stmt->execute([$this->id]);
            return (int) $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la vérification du bail actif : " . $e->getMessage());
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
     * Compte le nombre de locataires associés à une agence spécifique
     * @param int $agency_id
     * @return int
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
                WHERE b.agency_id = ? AND t.is_deleted = 0
            ";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$agency_id]);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors du comptage des locataires pour une agence spécifique : " . $e->getMessage());
        }
    }

    /**
     * Compte le nombre de locataires gérés par un agent spécifique
     * @param int $agent_id
     * @return int
     */
    public static function countByAgent($agent_id)
    {
        try {
            $pdo = Database::getInstance();
            $query = "
                SELECT COUNT(*)
                FROM tenants
                WHERE agent_id = ? AND is_deleted = 0
            ";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$agent_id]);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors du comptage des locataires pour un agent spécifique : " . $e->getMessage());
        }
    }

    /**
     * Compte le nombre de nouveaux locataires ce mois pour les appartements d'un propriétaire spécifique
     * @param int $owner_id
     * @return int
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
                WHERE a.owner_id = ? AND l.created_at >= ? AND t.is_deleted = 0
            ";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$owner_id, date('Y-m-01')]);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors du comptage des nouveaux locataires ce mois pour un propriétaire spécifique : " . $e->getMessage());
        }
    }
}