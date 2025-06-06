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
    protected $amount;
    protected $payment_date;
    protected $due_date;
    protected $type;
    protected $status;
    protected $quittance_path;
    protected $created_at;
    protected $updated_at;
    protected $deleted_at;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    // Getters
    public function getId() { return $this->id; }
    public function getLeaseId() { return $this->lease_id; }
    public function getAmount() { return $this->amount; }
    public function getPaymentDate() { return $this->payment_date; }
    public function getDueDate() { return $this->due_date; }
    public function getType() { return $this->type; }
    public function getStatus() { return $this->status; }
    public function getQuittancePath() { return $this->quittance_path; }
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedAt() { return $this->updated_at; }
    public function getDeletedAt() { return $this->deleted_at; }

    // Protected setters
    protected function setId($id) { $this->id = $id; }
    protected function setLeaseId($lease_id) { $this->lease_id = $lease_id; }
    protected function setAmount($amount) { $this->amount = $amount; }
    protected function setPaymentDate($payment_date) { $this->payment_date = $payment_date; }
    protected function setDueDate($due_date) { $this->due_date = $due_date; }
    protected function setType($type) { $this->type = $type; }
    protected function setStatus($status) { $this->status = $status; }
    protected function setQuittancePath($quittance_path) { $this->quittance_path = $quittance_path; }
    protected function setCreatedAt($created_at) { $this->created_at = $created_at; }
    protected function setUpdatedAt($updated_at) { $this->updated_at = $updated_at; }
    protected function setDeletedAt($deleted_at) { $this->deleted_at = $deleted_at; }

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
        $payment->setAmount($data['amount']);
        $payment->setPaymentDate($data['payment_date']);
        $payment->setDueDate($data['due_date']);
        $payment->setType($data['type']);
        $payment->setStatus($data['status']);
        $payment->setQuittancePath($data['quittance_path'] ?? null);
        $payment->setCreatedAt($data['created_at']);
        $payment->setUpdatedAt($data['updated_at'] ?? null);
        $payment->setDeletedAt($data['deleted_at'] ?? null);
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
            $stmt = $pdo->prepare('SELECT * FROM payments WHERE id = ? AND is_deleted IS FALSE');
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
            $stmt = $pdo->query('SELECT * FROM payments WHERE is_deleted IS FALSE ORDER BY payment_date DESC');
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
            $stmt = $pdo->query('SELECT * FROM payments WHERE is_deleted IS FALSE ORDER BY payment_date ASC LIMIT 1');
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
            // Vérifier l’existence du contrat
            if (!Lease::find($data['lease_id'])) {
                throw new PDOException("Lease ID invalide");
            }
            // Validation basique de type
            $validTypes = ['rent', 'charges', 'deposit']; // À ajuster selon vos besoins
            if (!in_array($data['type'], $validTypes)) {
                throw new PDOException("Type de paiement invalide");
            }
            $stmt = $pdo->prepare('
                INSERT INTO payments (
                    lease_id, amount, payment_date, due_date, type, status, quittance_path,
                    created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ');
            $stmt->execute([
                $data['lease_id'],
                $data['amount'],
                $data['payment_date'],
                $data['due_date'],
                $data['type'],
                $data['status'],
                $data['quittance_path'] ?? null
            ]);
            $id = $pdo->lastInsertId();
            return self::find($id);
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
            // Vérifier l’existence du contrat si modifié
            if (!empty($data['lease_id']) && !Lease::find($data['lease_id'])) {
                throw new PDOException("Lease ID invalide");
            }
            // Validation basique de type si modifié
            if (!empty($data['type'])) {
                $validTypes = ['rent', 'charges', 'deposit']; // À ajuster
                if (!in_array($data['type'], $validTypes)) {
                    throw new PDOException("Type de paiement invalide");
                }
            }
            $stmt = $pdo->prepare('
                UPDATE payments
                SET lease_id = ?, amount = ?, payment_date = ?, due_date = ?, type = ?, status = ?,
                    quittance_path = ?, updated_at = NOW()
                WHERE id = ?
            ');
            $stmt->execute([
                $data['lease_id'] ?? $existing->getLeaseId(),
                $data['amount'] ?? $existing->getAmount(),
                $data['payment_date'] ?? $existing->getPaymentDate(),
                $data['due_date'] ?? $existing->getDueDate(),
                $data['type'] ?? $existing->getType(),
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
            $stmt = $pdo->prepare('UPDATE payments SET deleted_at = NOW() WHERE id = ?');
            return $stmt->execute([$id]);
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
            $stmt = $pdo->prepare('SELECT * FROM payments WHERE lease_id = ? AND is_deleted IS FALSE');
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
     * Récupère le contrat associé
     * @return Lease|null
     */
    public function lease()
    {
        return Lease::find($this->lease_id);
    }

     /**
     * Compte le nombre de paiements en attente (global).
     *
     * @return int Nombre de paiements en attente
     */
    public static function countPending()
    {
        try {
            $pdo = Database::getInstance();
            $query = "
                SELECT COUNT(*)
                FROM payments
                WHERE status = 'pending'
            ";
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $result = $stmt->fetchColumn();
            return (int) $result;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors du comptage des paiements en attente : " . $e->getMessage());
        }
    }

    /**
     * Compte le nombre de paiements en attente pour une agence spécifique.
     *
     * @param int $agency_id ID de l'agence
     * @return int Nombre de paiements en attente
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
                WHERE p.status = 'pending' AND b.agency_id = ?
            ";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$agency_id]);
            $result = $stmt->fetchColumn();
            return (int) $result;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors du comptage des paiements en attente pour une agence spécifique : " . $e->getMessage());
        }
    }

    /**
     * Compte le nombre de paiements en attente pour un agent spécifique dans une agence.
     *
     * @param int $agent_id ID de l'agent
     * @param int $agency_id ID de l'agence
     * @return int Nombre de paiements en attente
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
                WHERE p.status = 'pending' AND l.agent_id = ? AND b.agency_id = ?
            ";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$agent_id, $agency_id]);
            $result = $stmt->fetchColumn();
            return (int) $result;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors du comptage des paiements en attente pour un agent spécifique : " . $e->getMessage());
        }
    }

    /**
     * Compte le nombre de paiements effectués pour un locataire spécifique.
     *
     * @param int $tenant_id ID du locataire
     * @return int Nombre de paiements effectués
     */
    public static function countPaidByTenant($tenant_id)
    {
        try {
            $pdo = Database::getInstance();
            $query = "
                SELECT COUNT(*)
                FROM payments p
                JOIN leases l ON p.lease_id = l.id
                WHERE l.tenant_id = ? AND p.status = 'paid'
            ";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$tenant_id]);
            $result = $stmt->fetchColumn();
            return (int) $result;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors du comptage des paiements effectués pour un locataire spécifique : " . $e->getMessage());
        }
    }
}