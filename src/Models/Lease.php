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
    protected $start_date;
    protected $end_date;
    protected $rent_amount;
    protected $charges_amount;
    protected $deposit_amount;
    protected $payment_frequency;
    protected $status;
    protected $created_at;
    protected $updated_at;
    protected $deleted_at;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    // Getters
    public function getId() { return $this->id; }
    public function getApartmentId() { return $this->apartment_id; }
    public function getTenantId() { return $this->tenant_id; }
    public function getStartDate() { return $this->start_date; }
    public function getEndDate() { return $this->end_date; }
    public function getRentAmount() { return $this->rent_amount; }
    public function getChargesAmount() { return $this->charges_amount; }
    public function getDepositAmount() { return $this->deposit_amount; }
    public function getPaymentFrequency() { return $this->payment_frequency; }
    public function getStatus() { return $this->status; }
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedAt() { return $this->updated_at; }
    public function getDeletedAt() { return $this->deleted_at; }

    // Protected setters
    protected function setId($id) { $this->id = $id; }
    protected function setApartmentId($apartment_id) { $this->apartment_id = $apartment_id; }
    protected function setTenantId($tenant_id) { $this->tenant_id = $tenant_id; }
    protected function setStartDate($start_date) { $this->start_date = $start_date; }
    protected function setEndDate($end_date) { $this->end_date = $end_date; }
    protected function setRentAmount($rent_amount) { $this->rent_amount = $rent_amount; }
    protected function setChargesAmount($charges_amount) { $this->charges_amount = $charges_amount; }
    protected function setDepositAmount($deposit_amount) { $this->deposit_amount = $deposit_amount; }
    protected function setPaymentFrequency($payment_frequency) { $this->payment_frequency = $payment_frequency; }
    protected function setStatus($status) { $this->status = $status; }
    protected function setCreatedAt($created_at) { $this->created_at = $created_at; }
    protected function setUpdatedAt($updated_at) { $this->updated_at = $updated_at; }
    protected function setDeletedAt($deleted_at) { $this->deleted_at = $deleted_at; }

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
        $lease->setStartDate($data['start_date']);
        $lease->setEndDate($data['end_date'] ?? null);
        $lease->setRentAmount($data['rent_amount']);
        $lease->setChargesAmount($data['charges_amount']);
        $lease->setDepositAmount($data['deposit_amount']);
        $lease->setPaymentFrequency($data['payment_frequency']);
        $lease->setStatus($data['status']);
        $lease->setCreatedAt($data['created_at']);
        $lease->setUpdatedAt($data['updated_at'] ?? null);
        $lease->setDeletedAt($data['deleted_at'] ?? null);
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
            $stmt = $pdo->prepare('SELECT * FROM leases WHERE id = ? AND deleted_at IS NULL');
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
     * Récupère tous les contrats de location
     * @return array
     */
    public static function get()
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->query('SELECT * FROM leases WHERE deleted_at IS NULL ORDER BY created_at DESC');
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
     * Récupère le premier contrat de location (par date de création)
     * @return Lease|null
     */
    public static function first()
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->query('SELECT * FROM leases WHERE deleted_at IS NULL ORDER BY created_at ASC LIMIT 1');
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
                    apartment_id, tenant_id, start_date, end_date, rent_amount, charges_amount,
                    deposit_amount, payment_frequency, status, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
            ');
            $stmt->execute([
                $data['apartment_id'],
                $data['tenant_id'],
                $data['start_date'],
                $data['end_date'] ?? null,
                $data['rent_amount'],
                $data['charges_amount'],
                $data['deposit_amount'],
                $data['payment_frequency'],
                $data['status']
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
                SET apartment_id = ?, tenant_id = ?, start_date = ?, end_date = ?, rent_amount = ?,
                    charges_amount = ?, deposit_amount = ?, payment_frequency = ?, status = ?, updated_at = NOW()
                WHERE id = ?
            ');
            $stmt->execute([
                $data['apartment_id'] ?? $existing->getApartmentId(),
                $data['tenant_id'] ?? $existing->getTenantId(),
                $data['start_date'] ?? $existing->getStartDate(),
                $data['end_date'] ?? $existing->getEndDate(),
                $data['rent_amount'] ?? $existing->getRentAmount(),
                $data['charges_amount'] ?? $existing->getChargesAmount(),
                $data['deposit_amount'] ?? $existing->getDepositAmount(),
                $data['payment_frequency'] ?? $existing->getPaymentFrequency(),
                $data['status'] ?? $existing->getStatus(),
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
            $stmt = $pdo->prepare('UPDATE leases SET deleted_at = NOW() WHERE id = ?');
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
            $stmt = $pdo->prepare('SELECT * FROM leases WHERE tenant_id = ? AND deleted_at IS NULL');
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
            $stmt = $pdo->prepare('SELECT * FROM leases WHERE apartment_id = ? AND deleted_at IS NULL');
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
}