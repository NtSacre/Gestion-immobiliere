<?php
namespace App\Models;

use App\Config\Database;
use PDO;
use PDOException;

/**
 * Modèle pour la table leases
 * ImmoApp (L2GIB, 2024-2025)
 * Représente un contrat de location entre un locataire et un appartement
 */
class Lease
{
    private $pdo;
    protected $id;
    protected $apartment_id;
    protected $tenant_id;
    protected $agent_id;
    protected $agency_id;
    protected $start_date;
    protected $end_date;
    protected $rent_amount;
    protected $charges_amount;
    protected $deposit_amount;
    protected $payment_frequency;
 
    protected $is_active;
    protected $created_at;
    protected $updated_at;
    protected $deleted_at;
    protected $is_deleted;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    // Getters
    public function getId() { return $this->id; }
    public function getApartmentId() { return $this->apartment_id; }
    public function getTenantId() { return $this->tenant_id; }
    public function getAgentId() { return $this->agent_id; }
    public function getAgencyId() { return $this->agency_id; }
    public function getStartDate() { return $this->start_date; }
    public function getEndDate() { return $this->end_date; }
    public function getRentAmount() { return $this->rent_amount; }
    public function getChargesAmount() { return $this->charges_amount; }
    public function getDepositAmount() { return $this->deposit_amount; }
    public function getPaymentFrequency() { return $this->payment_frequency; }

    public function getIsActive() { return $this->is_active; }
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedAt() { return $this->updated_at; }
    public function getDeletedAt() { return $this->deleted_at; }
    public function getIsDeleted() { return $this->is_deleted; }

    // Protected setters
    protected function setId($id) { $this->id = $id; }
    protected function setApartmentId($apartment_id) { $this->apartment_id = $apartment_id; }
    protected function setTenantId($tenant_id) { $this->tenant_id = $tenant_id; }
    protected function setAgentId($agent_id) { $this->agent_id = $agent_id; }
    protected function setAgencyId($agency_id) { $this->agency_id = $agency_id; }
    protected function setStartDate($start_date) { $this->start_date = $start_date; }
    protected function setEndDate($end_date) { $this->end_date = $end_date; }
    protected function setRentAmount($rent_amount) { $this->rent_amount = $rent_amount; }
    protected function setChargesAmount($charges_amount) { $this->charges_amount = $charges_amount; }
    protected function setDepositAmount($deposit_amount) { $this->deposit_amount = $deposit_amount; }
    protected function setPaymentFrequency($payment_frequency) { $this->payment_frequency = $payment_frequency; }

    protected function setIsActive($is_active) { $this->is_active = $is_active; }
    protected function setCreatedAt($created_at) { $this->created_at = $created_at; }
    protected function setUpdatedAt($updated_at) { $this->updated_at = $updated_at; }
    protected function setDeletedAt($deleted_at) { $this->deleted_at = $deleted_at; }
    protected function setIsDeleted($is_deleted) { $this->is_deleted = $is_deleted; }

    /**
     * Crée un objet Lease à partir des données de la base
     * @param array $data
     * @return Lease
     */
    protected static function fromData(array $data): Lease
    {
        $lease = new self();
        $lease->setId($data['id']);
        $lease->setApartmentId($data['apartment_id']);
        $lease->setTenantId($data['tenant_id']);
        $lease->setAgentId($data['agent_id'] ?? null);
        $lease->setAgencyId($data['agency_id'] ?? null);
        $lease->setStartDate($data['start_date']);
        $lease->setEndDate($data['end_date'] ?? null);
        $lease->setRentAmount($data['rent_amount']);
        $lease->setChargesAmount($data['charges_amount']);
        $lease->setDepositAmount($data['deposit_amount']);
        $lease->setPaymentFrequency($data['payment_frequency'] ?? null);

        $lease->setIsActive($data['is_active'] ?? ($data['status'] === 'actif' ? 1 : 0));
        $lease->setCreatedAt($data['created_at']);
        $lease->setUpdatedAt($data['updated_at'] ?? null);
        $lease->setDeletedAt($data['deleted_at'] ?? null);
        $lease->setIsDeleted($data['is_deleted'] ?? 0);
        return $lease;
    }

    /**
     * Trouve un contrat de location par ID
     * @param int $id
     * @return Lease|null
     */
    public static function find($id)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT * FROM leases WHERE id = ? AND is_deleted = 0');
            $stmt->execute([$id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? self::fromData($data) : null;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche du contrat de location : " . $e->getMessage());
        }
    }

    /**
     * Alias pour find
     * @param int $id
     * @return Lease|null
     */
    public static function findById($id)
    {
        return self::find($id);
    }

    /**
     * Récupère tous les contrats de location avec recherche et pagination
     * @param string $search
     * @param int $perPage
     * @param int $offset
     * @return array
     */
    public static function get($search = '', $perPage = 10, $offset = 0)
    {
        try {
            $pdo = Database::getInstance();
            $query = 'SELECT l.* FROM leases l
                      JOIN tenants t ON l.tenant_id = t.id
                      JOIN users u ON t.user_id = u.id
                      JOIN apartments a ON l.apartment_id = a.id
                      WHERE l.is_deleted = 0';
            $params = [];
            if ($search) {
                $query .= ' AND (u.first_name LIKE ? OR u.last_name LIKE ? OR a.number LIKE ?)';
                $params = ["%$search%", "%$search%", "%$search%"];
            }
            $query .= ' ORDER BY l.created_at DESC LIMIT ? OFFSET ?';
            $params[] = $perPage;
            $params[] = $offset;

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $leases = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $leases[] = self::fromData($data);
            }
            return $leases;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération des contrats de location : " . $e->getMessage());
        }
    }

    /**
     * Compte tous les contrats de location avec recherche
     * @param string $search
     * @return int
     */
    public static function count($search = '')
    {
        try {
            $pdo = Database::getInstance();
            $query = 'SELECT COUNT(*) FROM leases l
                      JOIN tenants t ON l.tenant_id = t.id
                      JOIN users u ON t.user_id = u.id
                      JOIN apartments a ON l.apartment_id = a.id
                      WHERE l.is_deleted = 0';
            $params = [];
            if ($search) {
                $query .= ' AND (u.first_name LIKE ? OR u.last_name LIKE ? OR a.number LIKE ?)';
                $params = ["%$search%", "%$search%", "%$search%"];
            }
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors du comptage des contrats de location : " . $e->getMessage());
        }
    }

    /**
     * Récupère le premier contrat de location (par date de création)
     * @return Lease|null
     */
    public static function first()
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->query('SELECT * FROM leases WHERE is_deleted = 0 ORDER BY created_at ASC LIMIT 1');
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? self::fromData($data) : null;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération du premier contrat de location : " . $e->getMessage());
        }
    }

    /**
     * Crée un nouveau contrat de location
     * @param array $data
     * @return Lease
     */
    public static function create(array $data)
    {
        try {
            $pdo = Database::getInstance();
            // Vérifier les dépendances
            if (!Apartment::find($data['apartment_id'])) {
                throw new PDOException("Apartment ID invalide");
            }
            if (!Tenant::find($data['tenant_id'])) {
                throw new PDOException("Tenant ID invalide");
            }
            $stmt = $pdo->prepare('
                INSERT INTO leases (
                    apartment_id, tenant_id, agent_id, agency_id, start_date, end_date, rent_amount,
                    charges_amount, deposit_amount, payment_frequency, is_active, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ');
            $stmt->execute([
                $data['apartment_id'],
                $data['tenant_id'],
                $data['agent_id'] ?? null,
                $data['agency_id'] ?? null,
                $data['start_date'],
                $data['end_date'] ?? null,
                $data['rent_amount'],
                $data['charges_amount'],
                $data['deposit_amount'],
                $data['payment_frequency'],
                $data['is_active'] ?? 1
            ]);
            $id = $pdo->lastInsertId();
            return self::find($id);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la création du contrat de location : " . $e->getMessage());
        }
    }

    /**
     * Met à jour un contrat de location
     * @param int $id
     * @param array $data
     * @return Lease|null
     */
    public static function update($id, array $data)
    {
        try {
            $pdo = Database::getInstance();
            // Vérifier les dépendances si modifiées
            if (!empty($data['apartment_id']) && !Apartment::find($data['apartment_id'])) {
                throw new PDOException("Apartment ID invalide");
            }
            if (!empty($data['tenant_id']) && !Tenant::find($data['tenant_id'])) {
                throw new PDOException("Tenant ID invalide");
            }
            $existing = self::find($id);
            if (!$existing) {
                throw new PDOException("Contrat de location introuvable");
            }
            $stmt = $pdo->prepare('
                UPDATE leases
                SET apartment_id = ?, tenant_id = ?, agent_id = ?, agency_id = ?, start_date = ?, end_date = ?,
                    rent_amount = ?, charges_amount = ?, deposit_amount = ?, payment_frequency = ?, 
                    is_active = ?, updated_at = NOW()
                WHERE id = ?
            ');
            $stmt->execute([
                $data['apartment_id'] ?? $existing->getApartmentId(),
                $data['tenant_id'] ?? $existing->getTenantId(),
                $data['agent_id'] ?? $existing->getAgentId(),
                $data['agency_id'] ?? $existing->getAgencyId(),
                $data['start_date'] ?? $existing->getStartDate(),
                $data['end_date'] ?? $existing->getEndDate(),
                $data['rent_amount'] ?? $existing->getRentAmount(),
                $data['charges_amount'] ?? $existing->getChargesAmount(),
                $data['deposit_amount'] ?? $existing->getDepositAmount(),
                $data['payment_frequency'] ?? $existing->getPaymentFrequency(),
                $data['is_active'] ?? $existing->getIsActive(),
                $id
            ]);
            return self::find($id);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la mise à jour du contrat de location : " . $e->getMessage());
        }
    }

    /**
     * Supprime un contrat de location (soft delete)
     * @param int $id
     * @return bool
     */
    public static function delete($id)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('UPDATE leases SET deleted_at = NOW(), is_deleted = 1 WHERE id = ? AND is_deleted = 0');
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la suppression du contrat de location : " . $e->getMessage());
        }
    }

    /**
     * Trouve les contrats de location par tenant_id
     * @param int $tenantId
     * @return array
     */
    public static function findByTenantId($tenantId)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT * FROM leases WHERE tenant_id = ? AND is_deleted = 0');
            $stmt->execute([$tenantId]);
            $leases = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $leases[] = self::fromData($data);
            }
            return $leases;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche des contrats de location par tenant_id : " . $e->getMessage());
        }
    }

    /**
     * Trouve les contrats de location par apartment_id
     * @param int $apartmentId
     * @return array
     */
    public static function findByApartmentId($apartmentId)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT * FROM leases WHERE apartment_id = ? AND is_deleted = 0');
            $stmt->execute([$apartmentId]);
            $leases = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $leases[] = self::fromData($data);
            }
            return $leases;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche des contrats de location par apartment_id : " . $e->getMessage());
        }
    }

    /**
     * Trouve les contrats de location par agency_id avec recherche et pagination
     * @param int $agency_id
     * @param string $search
     * @param int $perPage
     * @param int $offset
     * @return array
     */
    public static function findByAgencyId($agency_id, $search = '', $perPage = 10, $offset = 0)
    {
        try {
            $pdo = Database::getInstance();
            $query = 'SELECT l.* FROM leases l
                      JOIN tenants t ON l.tenant_id = t.id
                      JOIN users u ON t.user_id = u.id
                      JOIN apartments a ON l.apartment_id = a.id
                      WHERE l.is_deleted = 0 AND l.agency_id = ?';
            $params = [$agency_id];
            if ($search) {
                $query .= ' AND (u.first_name LIKE ? OR u.last_name LIKE ? OR a.number LIKE ?)';
                $params[] = "%$search%";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            $query .= ' ORDER BY l.created_at DESC LIMIT ? OFFSET ?';
            $params[] = $perPage;
            $params[] = $offset;

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $leases = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $leases[] = self::fromData($data);
            }
            return $leases;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche des contrats de location par agency_id : " . $e->getMessage());
        }
    }

    /**
     * Compte les contrats de location par agency_id avec recherche
     * @param int $agency_id
     * @param string $search
     * @return int
     */
    public static function countByAgency($agency_id, $search = '')
    {
        try {
            $pdo = Database::getInstance();
            $query = 'SELECT COUNT(*) FROM leases l
                      JOIN tenants t ON l.tenant_id = t.id
                      JOIN users u ON t.user_id = u.id
                      JOIN apartments a ON l.apartment_id = a.id
                      WHERE l.is_deleted = 0 AND l.agency_id = ?';
            $params = [$agency_id];
            if ($search) {
                $query .= ' AND (u.first_name LIKE ? OR u.last_name LIKE ? OR a.number LIKE ?)';
                $params[] = "%$search%";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors du comptage des contrats de location par agency_id : " . $e->getMessage());
        }
    }

    /**
     * Trouve les contrats de location par agent_id et agency_id avec recherche et pagination
     * @param int $agent_id
     * @param int $agency_id
     * @param string $search
     * @param int $perPage
     * @param int $offset
     * @return array
     */
    public static function findByAgentId($agent_id, $agency_id, $search = '', $perPage = 10, $offset = 0)
    {
        try {
            $pdo = Database::getInstance();
            $query = 'SELECT l.* FROM leases l
                      JOIN tenants t ON l.tenant_id = t.id
                      JOIN users u ON t.user_id = u.id
                      JOIN apartments a ON l.apartment_id = a.id
                      WHERE l.is_deleted = 0 AND l.agent_id = ? AND l.agency_id = ?';
            $params = [$agent_id, $agency_id];
            if ($search) {
                $query .= ' AND (u.first_name LIKE ? OR u.last_name LIKE ? OR a.number LIKE ?)';
                $params[] = "%$search%";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            $query .= ' ORDER BY l.created_at DESC LIMIT ? OFFSET ?';
            $params[] = $perPage;
            $params[] = $offset;

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $leases = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $leases[] = self::fromData($data);
            }
            return $leases;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche des contrats de location par agent_id : " . $e->getMessage());
        }
    }

    /**
     * Trouve les contrats de location par owner_id avec recherche et pagination
     * @param int $owner_id
     * @param string $search
     * @param int $perPage
     * @param int $offset
     * @return array
     */
    public static function findByOwnerId($owner_id, $search = '', $perPage = 10, $offset = 0)
    {
        try {
            $pdo = Database::getInstance();
            $query = 'SELECT l.* FROM leases l
                      JOIN tenants t ON l.tenant_id = t.id
                      JOIN users u ON t.user_id = u.id
                      JOIN apartments a ON l.apartment_id = a.id
                      WHERE l.is_deleted = 0 AND a.owner_id = ?';
            $params = [$owner_id];
            if ($search) {
                $query .= ' AND (u.first_name LIKE ? OR u.last_name LIKE ? OR a.number LIKE ?)';
                $params[] = "%$search%";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            $query .= ' ORDER BY l.created_at DESC LIMIT ? OFFSET ?';
            $params[] = $perPage;
            $params[] = $offset;

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $leases = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $leases[] = self::fromData($data);
            }
            return $leases;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche des contrats de location par owner_id : " . $e->getMessage());
        }
    }

    /**
     * Compte les contrats de location par owner_id avec recherche
     * @param int $owner_id
     * @param string $search
     * @return int
     */
    public static function countByOwner($owner_id, $search = '')
    {
        try {
            $pdo = Database::getInstance();
            $query = 'SELECT COUNT(*) FROM leases l
                      JOIN tenants t ON l.tenant_id = t.id
                      JOIN users u ON t.user_id = u.id
                      JOIN apartments a ON l.apartment_id = a.id
                      WHERE l.is_deleted = 0 AND a.owner_id = ?';
            $params = [$owner_id];
            if ($search) {
                $query .= ' AND (u.first_name LIKE ? OR u.last_name LIKE ? OR a.number LIKE ?)';
                $params[] = "%$search%";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors du comptage des contrats de location par owner_id : " . $e->getMessage());
        }
    }

    /**
     * Récupère les paiements associés à un contrat de location
     * @param int $lease_id
     * @return array
     */
    public static function getPaymentsByLease($lease_id)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT * FROM payments WHERE lease_id = ? AND is_deleted = 0 ORDER BY payment_date DESC');
            $stmt->execute([$lease_id]);
            $payments = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $payments[] = $data; // À adapter avec Payment.php une fois créé
            }
            return $payments;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération des paiements pour le contrat : " . $e->getMessage());
        }
    }

    /**
     * Récupère l’appartement du contrat
     * @return Apartment|null
     */
    public function apartment()
    {
        return Apartment::find($this->apartment_id);
    }

    /**
     * Récupère le locataire du contrat
     * @return Tenant|null
     */
    public function tenant()
    {
        return Tenant::find($this->tenant_id);
    }

    /**
     * Vérifie si un acheteur a des baux associés via son tenant_id
     * @param int $userId
     * @return bool
     */
    public static function hasLeasesForBuyer($userId)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('
                SELECT COUNT(*) 
                FROM leases 
                WHERE tenant_id = (SELECT id FROM tenants WHERE user_id = ?)
                AND is_deleted = 0
            ');
            $stmt->execute([$userId]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la vérification des baux pour l'acheteur : " . $e->getMessage());
        }
    }

    /**
     * Compte les locations actives (non supprimées, is_active = 1)
     * @return int
     */
    public static function countActive()
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM leases WHERE is_active = 1 AND is_deleted = 0');
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors du comptage des locations actives : " . $e->getMessage());
        }
    }

    /**
     * Compte les nouvelles locations actives créées ce mois
     * @return int
     */
    public static function countNewActiveThisMonth()
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('
                SELECT COUNT(*) 
                FROM leases 
                WHERE is_active = 1 
                AND is_deleted = 0 
                AND created_at >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
            ');
            $stmt->execute();
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors du comptage des nouvelles locations actives : " . $e->getMessage());
        }
    }

    /**
     * Calcule le chiffre d'affaires total (somme des loyers actifs)
     * @return float
     */
    public static function calculateTotalRevenue()
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('
                SELECT SUM(rent_amount + charges_amount) 
                FROM leases 
                WHERE is_active = 1 
                AND is_deleted = 0
            ');
            $stmt->execute();
            return (float)($stmt->fetchColumn() ?? 0);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors du calcul du chiffre d'affaires : " . $e->getMessage());
        }
    }

    /**
     * Calcule le revenu total des baux pour une agence spécifique
     * @param int $agency_id
     * @return float
     */
    public static function calculateTotalRevenueByAgency($agency_id)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('
                SELECT SUM(rent_amount + charges_amount)
                FROM leases
                WHERE agency_id = ? AND is_active = 1 AND is_deleted = 0
            ');
            $stmt->execute([$agency_id]);
            return (float)($stmt->fetchColumn() ?? 0);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors du calcul du revenu total des baux pour une agence spécifique : " . $e->getMessage());
        }
    }

    /**
     * Compte le nombre de baux actifs pour une agence spécifique
     * @param int $agency_id
     * @return int
     */
    public static function countActiveByAgency($agency_id)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('
                SELECT COUNT(*)
                FROM leases
                WHERE is_active = 1 AND is_deleted = 0 AND agency_id = ?
            ');
            $stmt->execute([$agency_id]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors du comptage des baux actifs pour une agence spécifique : " . $e->getMessage());
        }
    }

    /**
     * Calcule le revenu total des baux pour un agent spécifique dans une agence
     * @param int $agent_id
     * @param int $agency_id
     * @return float
     */
    public static function calculateTotalRevenueByAgent($agent_id, $agency_id)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('
                SELECT SUM(rent_amount + charges_amount)
                FROM leases
                WHERE agent_id = ? AND agency_id = ? AND is_active = 1 AND is_deleted = 0
            ');
            $stmt->execute([$agent_id, $agency_id]);
            return (float)($stmt->fetchColumn() ?? 0);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors du calcul du revenu total des baux pour un agent spécifique : " . $e->getMessage());
        }
    }

    /**
     * Compte le nombre de baux actifs pour un agent spécifique dans une agence
     * @param int $agent_id
     * @param int $agency_id
     * @return int
     */
    public static function countActiveByAgent($agent_id, $agency_id)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('
                SELECT COUNT(*)
                FROM leases
                WHERE is_active = 1 AND is_deleted = 0 AND agent_id = ? AND agency_id = ?
            ');
            $stmt->execute([$agent_id, $agency_id]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors du comptage des baux actifs pour un agent spécifique : " . $e->getMessage());
        }
    }

    /**
     * Calcule le revenu total des baux pour un propriétaire spécifique
     * @param int $owner_id
     * @return float
     */
    public static function calculateRevenueByOwner($owner_id)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('
                SELECT SUM(l.rent_amount + l.charges_amount)
                FROM leases l
                JOIN apartments a ON l.apartment_id = a.id
                WHERE a.owner_id = ? AND l.is_active = 1 AND l.is_deleted = 0
            ');
            $stmt->execute([$owner_id]);
            return (float)($stmt->fetchColumn() ?? 0);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors du calcul du revenu total des baux pour un propriétaire spécifique : " . $e->getMessage());
        }
    }

    /**
     * Récupère le montant du loyer d’un bail actif pour un locataire spécifique
     * @param int $tenant_id
     * @return float
     */
    public static function getRentAmountByTenant($tenant_id)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('
                SELECT SUM(rent_amount + charges_amount)
                FROM leases
                WHERE tenant_id = ? AND is_active = 1 AND is_deleted = 0
            ');
            $stmt->execute([$tenant_id]);
            return (float)($stmt->fetchColumn() ?? 0);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération du montant du loyer pour un locataire spécifique : " . $e->getMessage());
        }
    }

    /**
     * Compte le nombre de baux actifs pour un locataire spécifique
     * @param int $tenant_id
     * @return int
     */
    public static function countActiveByTenant($tenant_id)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('
                SELECT COUNT(*)
                FROM leases
                WHERE tenant_id = ? AND is_active = 1 AND is_deleted = 0
            ');
            $stmt->execute([$tenant_id]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors du comptage des baux actifs pour un locataire spécifique : " . $e->getMessage());
        }
    }

    /**
     * Compte le nombre de baux créés ce mois (global)
     * @return int
     */
    public static function countNewThisMonth()
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('
                SELECT COUNT(*)
                FROM leases
                WHERE created_at >= ? AND is_deleted = 0
            ');
            $stmt->execute([date('Y-m-01')]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors du comptage des baux créés ce mois : " . $e->getMessage());
        }
    }

    /**
     * Compte le nombre de baux créés ce mois pour une agence spécifique
     * @param int $agency_id
     * @return int
     */
    public static function countNewByAgencyThisMonth($agency_id)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('
                SELECT COUNT(*)
                FROM leases
                WHERE created_at >= ? AND agency_id = ? AND is_deleted = 0
            ');
            $stmt->execute([date('Y-m-01'), $agency_id]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors du comptage des baux créés ce mois pour une agence spécifique : " . $e->getMessage());
        }
    }

    /**
     * Compte le nombre de baux créés ce mois pour un agent spécifique dans une agence
     * @param int $agent_id
     * @param int $agency_id
     * @return int
     */
    public static function countNewByAgentThisMonth($agent_id, $agency_id)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('
                SELECT COUNT(*)
                FROM leases
                WHERE created_at >= ? AND agent_id = ? AND agency_id = ? AND is_deleted = 0
            ');
            $stmt->execute([date('Y-m-01'), $agent_id, $agency_id]);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors du comptage des baux créés ce mois pour un agent spécifique : " . $e->getMessage());
        }
    }
}
?>