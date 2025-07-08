<?php
namespace App\Models;

use App\Config\Database;
use PDO;
use PDOException;

/**
 * Modèle pour la table payments
 * ImmoApp (L2GIB, 2024-2025)
 * Représente un paiement lié à un contrat de location (loyer, charges, dépôt, etc.)
 */
class Payment
{
    private $pdo;
    protected $id;
    protected $lease_id;
    protected $agent_id;
    protected $agency_id;
    protected $amount;
    protected $payment_date;
    protected $due_date;
    protected $type;
    protected $mode;
    protected $status;
    protected $quittance_path;
    protected $created_at;
    protected $updated_at;
    protected $is_deleted;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    // Getters
    public function getId() { return $this->id; }
    public function getLeaseId() { return $this->lease_id; }
    public function getAgentId() { return $this->agent_id; }
    public function getAgencyId() { return $this->agency_id; }
    public function getAmount() { return $this->amount; }
    public function getPaymentDate() { return $this->payment_date; }
    public function getDueDate() { return $this->due_date; }
    public function getType() { return $this->type; }
    public function getMode() { return $this->mode; }
    public function getStatus() { return $this->status; }
    public function getQuittancePath() { return $this->quittance_path; }
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedAt() { return $this->updated_at; }
    public function getIsDeleted() { return $this->is_deleted; }

    // Protected setters
    protected function setId($id) { $this->id = $id; }
    protected function setLeaseId($lease_id) { $this->lease_id = $lease_id; }
    protected function setAgentId($agent_id) { $this->agent_id = $agent_id; }
    protected function setAgencyId($agency_id) { $this->agency_id = $agency_id; }
    protected function setAmount($amount) { $this->amount = $amount; }
    protected function setPaymentDate($payment_date) { $this->payment_date = $payment_date; }
    protected function setDueDate($due_date) { $this->due_date = $due_date; }
    protected function setType($type) { $this->type = $type; }
    protected function setMode($mode) { $this->mode = $mode; }
    protected function setStatus($status) { $this->status = $status; }
    protected function setQuittancePath($quittance_path) { $this->quittance_path = $quittance_path; }
    protected function setCreatedAt($created_at) { $this->created_at = $created_at; }
    protected function setUpdatedAt($updated_at) { $this->updated_at = $updated_at; }
    protected function setIsDeleted($is_deleted) { $this->is_deleted = $is_deleted; }

    /**
     * Crée un objet Payment à partir des données de la base
     * @param array $data
     * @return Payment
     */
    protected static function fromData(array $data): Payment
    {
        $payment = new self();
        $payment->setId($data['id']);
        $payment->setLeaseId($data['lease_id']);
        $payment->setAgentId($data['agent_id'] ?? null);
        $payment->setAgencyId($data['agency_id'] ?? null);
        $payment->setAmount($data['amount']);
        $payment->setPaymentDate($data['payment_date']);
        $payment->setDueDate($data['due_date']);
        $payment->setType($data['type']);
        $payment->setMode($data['mode']);
        $payment->setStatus($data['status']);
        $payment->setQuittancePath($data['quittance_path'] ?? null);
        $payment->setCreatedAt($data['created_at']);
        $payment->setUpdatedAt($data['updated_at'] ?? null);
        $payment->setIsDeleted($data['is_deleted'] ?? 0);
        return $payment;
    }

    /**
     * Trouve un paiement par ID
     * @param int $id
     * @return Payment|null
     */
    public static function find($id)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT * FROM payments WHERE id = ? AND is_deleted = 0');
            $stmt->execute([$id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? self::fromData($data) : null;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche du paiement : " . $e->getMessage());
        }
    }

    /**
     * Alias pour find
     * @param int $id
     * @return Payment|null
     */
    public static function findById($id)
    {
        return self::find($id);
    }

    /**
     * Récupère tous les paiements
     * @return array
     */
    public static function get()
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->query('SELECT * FROM payments WHERE is_deleted = 0 ORDER BY payment_date DESC');
            $payments = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $payments[] = self::fromData($data);
            }
            return $payments;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération des paiements : " . $e->getMessage());
        }
    }

    /**
     * Récupère le premier paiement (par date de paiement)
     * @return Payment|null
     */
    public static function first()
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->query('SELECT * FROM payments WHERE is_deleted = 0 ORDER BY payment_date ASC LIMIT 1');
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? self::fromData($data) : null;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération du premier paiement : " . $e->getMessage());
        }
    }

    /**
     * Crée un nouveau paiement
     * @param array $data
     * @return Payment
     */
    public static function create(array $data)
    {
        try {
            $pdo = Database::getInstance();
            if (!Lease::find($data['lease_id'])) {
                throw new PDOException("ID de bail invalide : {$data['lease_id']}");
            }
            if (isset($data['agent_id']) && !\App\Models\User::find($data['agent_id'])) {
                throw new PDOException("ID d'agent invalide : {$data['agent_id']}");
            }
            if (isset($data['agency_id']) && !\App\Models\Agency::find($data['agency_id'])) {
                throw new PDOException("ID d'agence invalide : {$data['agency_id']}");
            }
            $validTypes = ['payer', 'charges', 'depot', 'autre'];
            if (!in_array($data['type'], $validTypes)) {
                throw new PDOException("Type de paiement invalide : {$data['type']}");
            }
            $validModes = ['cash', 'mobile', 'carte'];
            if (!in_array($data['mode'], $validModes)) {
                throw new PDOException("Mode de paiement invalide : {$data['mode']}");
            }
            $validStatuses = ['en_attente', 'payé', 'en_retard', 'annulé'];
            if (!in_array($data['status'], $validStatuses)) {
                throw new PDOException("Statut de paiement invalide : {$data['status']}");
            }
            if ($data['amount'] <= 0) {
                throw new PDOException("Le montant doit être positif");
            }
            if (empty($data['payment_date']) || !strtotime($data['payment_date'])) {
                throw new PDOException("Date de paiement invalide");
            }
            if (empty($data['due_date']) || !strtotime($data['due_date'])) {
                throw new PDOException("Date d'échéance invalide");
            }

            $stmt = $pdo->prepare('
                INSERT INTO payments (
                    lease_id, agent_id, agency_id, amount, payment_date, due_date, type, mode, status, quittance_path, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ');
            $stmt->execute([
                $data['lease_id'],
                $data['agent_id'] ?? null,
                $data['agency_id'] ?? null,
                $data['amount'],
                $data['payment_date'],
                $data['due_date'],
                $data['type'],
                $data['mode'],
                $data['status'],
                $data['quittance_path'] ?? null
            ]);
            $id = $pdo->lastInsertId();
            $payment = self::find($id);
            if (!$payment) {
                throw new PDOException("Paiement non trouvé après création, ID : $id");
            }
            return $payment;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la création du paiement : " . $e->getMessage());
        }
    }

    /**
     * Met à jour un paiement
     * @param int $id
     * @param array $data
     * @return Payment|null
     */
    public static function update($id, array $data)
    {
        try {
            $pdo = Database::getInstance();
            $existing = self::find($id);
            if (!$existing) {
                throw new PDOException("Paiement introuvable");
            }
            if (isset($data['lease_id']) && !Lease::find($data['lease_id'])) {
                throw new PDOException("ID de bail invalide");
            }
            if (isset($data['agent_id']) && !\App\Models\User::find($data['agent_id'])) {
                throw new PDOException("ID d'agent invalide");
            }
            if (isset($data['agency_id']) && !\App\Models\Agency::find($data['agency_id'])) {
                throw new PDOException("ID d'agence invalide");
            }
            if (isset($data['type'])) {
                $validTypes = ['rent', 'charges', 'deposit'];
                if (!in_array($data['type'], $validTypes)) {
                    throw new PDOException("Type de paiement invalide");
                }
            }
            if (isset($data['status'])) {
                $validStatuses = ['pending', 'paid', 'late', 'canceled'];
                if (!in_array($data['status'], $validStatuses)) {
                    throw new PDOException("Statut de paiement invalide");
                }
            }
            if (isset($data['amount']) && $data['amount'] <= 0) {
                throw new PDOException("Le montant doit être positif");
            }
            if (isset($data['payment_date']) && (empty($data['payment_date']) || !strtotime($data['payment_date']))) {
                throw new PDOException("Date de paiement invalide");
            }
            if (isset($data['due_date']) && (empty($data['due_date']) || !strtotime($data['due_date']))) {
                throw new PDOException("Date d'échéance invalide");
            }

            $stmt = $pdo->prepare('
                UPDATE payments
                SET lease_id = ?, agent_id = ?, agency_id = ?, amount = ?, payment_date = ?, due_date = ?, type = ?, mode = ?, status = ?, quittance_path = ?, updated_at = NOW()
                WHERE id = ?
            ');
            $stmt->execute([
                $data['lease_id'] ?? $existing->getLeaseId(),
                $data['agent_id'] ?? $existing->getAgentId(),
                $data['agency_id'] ?? $existing->getAgencyId(),
                $data['amount'] ?? $existing->getAmount(),
                $data['payment_date'] ?? $existing->getPaymentDate(),
                $data['due_date'] ?? $existing->getDueDate(),
                $data['type'] ?? $existing->getType(),
                $data['mode'] ?? $existing->getMode(),
                $data['status'] ?? $existing->getStatus(),
                $data['quittance_path'] ?? $existing->getQuittancePath(),
                $id
            ]);
            return self::find($id);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la mise à jour du paiement : " . $e->getMessage());
        }
    }

    /**
     * Supprime un paiement (soft delete)
     * @param int $id
     * @return bool
     */
    public static function delete($id)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('UPDATE payments SET is_deleted = 1, updated_at = NOW() WHERE id = ?');
            $stmt->execute([$id]);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la suppression du paiement : " . $e->getMessage());
        }
    }

    /**
     * Trouve les paiements par lease_id
     * @param int $leaseId
     * @return array
     */
    public static function findByLeaseId($leaseId)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT * FROM payments WHERE lease_id = ? AND is_deleted = 0 ORDER BY payment_date DESC');
            $stmt->execute([$leaseId]);
            $payments = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $payments[] = self::fromData($data);
            }
            return $payments;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche des paiements par lease_id : " . $e->getMessage());
        }
    }

    /**
     * Trouve les paiements par agency_id
     * @param int $agencyId
     * @return array
     */
    public static function findByAgencyId($agencyId)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('
                SELECT p.*
                FROM payments p
                JOIN leases l ON p.lease_id = l.id
                JOIN apartments a ON l.apartment_id = a.id
                JOIN buildings b ON a.building_id = b.id
                WHERE b.agency_id = ? AND p.is_deleted = 0
                ORDER BY p.payment_date DESC
            ');
            $stmt->execute([$agencyId]);
            $payments = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $payments[] = self::fromData($data);
            }
            return $payments;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche des paiements par agency_id : " . $e->getMessage());
        }
    }

    /**
     * Trouve les paiements par agent_id et agency_id
     * @param int $agentId
     * @param int $agencyId
     * @return array
     */
    public static function findByAgentId($agentId, $agencyId)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('
                SELECT p.*
                FROM payments p
                JOIN leases l ON p.lease_id = l.id
                JOIN apartments a ON l.apartment_id = a.id
                JOIN buildings b ON a.building_id = b.id
                WHERE l.agent_id = ? AND b.agency_id = ? AND p.is_deleted = 0
                ORDER BY p.payment_date DESC
            ');
            $stmt->execute([$agentId, $agencyId]);
            $payments = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $payments[] = self::fromData($data);
            }
            return $payments;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche des paiements par agent_id : " . $e->getMessage());
        }
    }

    /**
     * Récupère le contrat associé
     * @return Lease|null
     */
    public function lease()
    {
        return Lease::find($this->lease_id);
    }

    /**
     * Compte le nombre de paiements en attente (global).
     * @return int
     */
    public static function countPending()
    {
        try {
            $pdo = Database::getInstance();
            $query = "SELECT COUNT(*) FROM payments WHERE status = 'pending' AND is_deleted = 0";
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors du comptage des paiements en attente : " . $e->getMessage());
        }
    }

    /**
     * Compte le nombre de paiements en attente pour une agence spécifique.
     * @param int $agency_id
     * @return int
     */
    public static function countPendingByAgency($agency_id)
    {
        try {
            $pdo = Database::getInstance();
            $query = "
                SELECT COUNT(*)
                FROM payments p
                JOIN leases l ON p.lease_id = l.id
                JOIN apartments a ON l.apartment_id = a.id
                JOIN buildings b ON a.building_id = b.id
                WHERE p.status = 'pending' AND b.agency_id = ? AND p.is_deleted = 0
            ";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$agency_id]);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors du comptage des paiements en attente pour une agence spécifique : " . $e->getMessage());
        }
    }

    /**
     * Compte le nombre de paiements en attente pour un agent spécifique dans une agence.
     * @param int $agent_id
     * @param int $agency_id
     * @return int
     */
    public static function countPendingByAgent($agent_id, $agency_id)
    {
        try {
            $pdo = Database::getInstance();
            $query = "
                SELECT COUNT(*)
                FROM payments p
                JOIN leases l ON p.lease_id = l.id
                JOIN apartments a ON l.apartment_id = a.id
                JOIN buildings b ON a.building_id = b.id
                WHERE p.status = 'pending' AND l.agent_id = ? AND b.agency_id = ? AND p.is_deleted = 0
            ";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$agent_id, $agency_id]);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors du comptage des paiements en attente pour un agent spécifique : " . $e->getMessage());
        }
    }

    /**
     * Compte le nombre de paiements effectués pour un locataire spécifique.
     * @param int $tenant_id
     * @return int
     */
    public static function countPaidByTenant($tenant_id)
    {
        try {
            $pdo = Database::getInstance();
            $query = "
                SELECT COUNT(*)
                FROM payments p
                JOIN leases l ON p.lease_id = l.id
                WHERE l.tenant_id = ? AND p.status = 'paid' AND p.is_deleted = 0
            ";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$tenant_id]);
            return (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors du comptage des paiements effectués pour un locataire spécifique : " . $e->getMessage());
        }
    }



   /**
     * Récupère tous les paiements avec filtres et pagination
     * @param string $search
     * @param int $limit
     * @param int $offset
     * @param array $statuses
     * @return array
     */
    public static function getWithFilters($search = '', $limit = 10, $offset = 0, $statuses = [])
    {
        try {
            $pdo = Database::getInstance();
            $query = "SELECT * FROM payments WHERE is_deleted = 0";
            $params = [];

            if ($search) {
                $query .= " AND (id LIKE :search OR lease_id LIKE :search)";
                $params[':search'] = "%$search%";
            }

            if (!empty($statuses)) {
                $placeholders = implode(',', array_fill(0, count($statuses), '?'));
                $query .= " AND status IN ($placeholders)";
                $params = array_merge($params, $statuses);
            }

            $query .= " ORDER BY payment_date DESC LIMIT :limit OFFSET :offset";
            $params[':limit'] = (int)$limit;
            $params[':offset'] = (int)$offset;

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $payments = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $payments[] = self::fromData($data);
            }
            return $payments;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération des paiements avec filtres : " . $e->getMessage());
        }
    }

    /**
     * Compte tous les paiements avec filtres
     * @param string $search
     * @param array $statuses
     * @return int
     */
    public static function countWithFilters($search = '', $statuses = [])
    {
        try {
            $pdo = Database::getInstance();
            $query = "SELECT COUNT(*) FROM payments WHERE is_deleted = 0";
            $params = [];

            if ($search) {
                $query .= " AND (id LIKE :search OR lease_id LIKE :search)";
                $params[':search'] = "%$search%";
            }

            if (!empty($statuses)) {
                $placeholders = implode(',', array_fill(0, count($statuses), '?'));
                $query .= " AND status IN ($placeholders)";
                $params = array_merge($params, $statuses);
            }

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors du comptage des paiements avec filtres : " . $e->getMessage());
        }
    }

    /**
     * Récupère les paiements par agency_id avec filtres et pagination
     * @param int $agencyId
     * @param string $search
     * @param int $limit
     * @param int $offset
     * @param array $statuses
     * @return array
     */
    public static function findByAgencyIdWithFilters($agencyId, $search = '', $limit = 10, $offset = 0, $statuses = [])
    {
        try {
            $pdo = Database::getInstance();
            $query = "
                SELECT p.*
                FROM payments p
                JOIN leases l ON p.lease_id = l.id
                JOIN apartments a ON l.apartment_id = a.id
                JOIN buildings b ON a.building_id = b.id
                WHERE b.agency_id = :agency_id AND p.is_deleted = 0
            ";
            $params = [':agency_id' => $agencyId];

            if ($search) {
                $query .= " AND (p.id LIKE :search OR p.lease_id LIKE :search)";
                $params[':search'] = "%$search%";
            }

            if (!empty($statuses)) {
                $placeholders = implode(',', array_fill(0, count($statuses), '?'));
                $query .= " AND p.status IN ($placeholders)";
                $params = array_merge($params, $statuses);
            }

            $query .= " ORDER BY p.payment_date DESC LIMIT :limit OFFSET :offset";
            $params[':limit'] = (int)$limit;
            $params[':offset'] = (int)$offset;

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $payments = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $payments[] = self::fromData($data);
            }
            return $payments;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche des paiements par agency_id avec filtres : " . $e->getMessage());
        }
    }

    /**
     * Compte les paiements par agency_id avec filtres
     * @param int $agencyId
     * @param string $search
     * @param array $statuses
     * @return int
     */
    public static function countByAgencyIdWithFilters($agencyId, $search = '', $statuses = [])
    {
        try {
            $pdo = Database::getInstance();
            $query = "
                SELECT COUNT(*)
                FROM payments p
                JOIN leases l ON p.lease_id = l.id
                JOIN apartments a ON l.apartment_id = a.id
                JOIN buildings b ON a.building_id = b.id
                WHERE b.agency_id = :agency_id AND p.is_deleted = 0
            ";
            $params = [':agency_id' => $agencyId];

            if ($search) {
                $query .= " AND (p.id LIKE :search OR p.lease_id LIKE :search)";
                $params[':search'] = "%$search%";
            }

            if (!empty($statuses)) {
                $placeholders = implode(',', array_fill(0, count($statuses), '?'));
                $query .= " AND p.status IN ($placeholders)";
                $params = array_merge($params, $statuses);
            }

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors du comptage des paiements par agency_id avec filtres : " . $e->getMessage());
        }
    }

    /**
     * Récupère les paiements par agent_id et agency_id avec filtres et pagination
     * @param int $agentId
     * @param int $agencyId
     * @param string $search
     * @param int $limit
     * @param int $offset
     * @param array $statuses
     * @return array
     */
    public static function findByAgentIdWithFilters($agentId, $agencyId, $search = '', $limit = 10, $offset = 0, $statuses = [])
    {
        try {
            $pdo = Database::getInstance();
            $query = "
                SELECT p.*
                FROM payments p
                JOIN leases l ON p.lease_id = l.id
                JOIN apartments a ON l.apartment_id = a.id
                JOIN buildings b ON a.building_id = b.id
                WHERE l.agent_id = :agent_id AND b.agency_id = :agency_id AND p.is_deleted = 0
            ";
            $params = [':agent_id' => $agentId, ':agency_id' => $agencyId];

            if ($search) {
                $query .= " AND (p.id LIKE :search OR p.lease_id LIKE :search)";
                $params[':search'] = "%$search%";
            }

            if (!empty($statuses)) {
                $placeholders = implode(',', array_fill(0, count($statuses), '?'));
                $query .= " AND p.status IN ($placeholders)";
                $params = array_merge($params, $statuses);
            }

            $query .= " ORDER BY p.payment_date DESC LIMIT :limit OFFSET :offset";
            $params[':limit'] = (int)$limit;
            $params[':offset'] = (int)$offset;

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            $payments = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $payments[] = self::fromData($data);
            }
            return $payments;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche des paiements par agent_id avec filtres : " . $e->getMessage());
        }
    }

    /**
     * Compte les paiements par agent_id et agency_id avec filtres
     * @param int $agentId
     * @param int $agencyId
     * @param string $search
     * @param array $statuses
     * @return int
     */
    public static function countByAgentIdWithFilters($agentId, $agencyId, $search = '', $statuses = [])
    {
        try {
            $pdo = Database::getInstance();
            $query = "
                SELECT COUNT(*)
                FROM payments p
                JOIN leases l ON p.lease_id = l.id
                JOIN apartments a ON l.apartment_id = a.id
                JOIN buildings b ON a.building_id = b.id
                WHERE l.agent_id = :agent_id AND b.agency_id = :agency_id AND p.is_deleted = 0
            ";
            $params = [':agent_id' => $agentId, ':agency_id' => $agencyId];

            if ($search) {
                $query .= " AND (p.id LIKE :search OR p.lease_id LIKE :search)";
                $params[':search'] = "%$search%";
            }

            if (!empty($statuses)) {
                $placeholders = implode(',', array_fill(0, count($statuses), '?'));
                $query .= " AND p.status IN ($placeholders)";
                $params = array_merge($params, $statuses);
            }

            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
            return (int)$stmt->fetchColumn();
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors du comptage des paiements par agent_id avec filtres : " . $e->getMessage());
        }
    }

}