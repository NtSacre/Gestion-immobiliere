<?php
namespace App\Models;

use App\Config\Database;
use PDO;
use PDOException;

/**
 * Modèle pour la table subscriptions
 * ImmoApp (L2GIB, 2024-2025)
 * Représente un abonnement actif d’une agence à un plan
 */
class Subscriptions
{
    private $pdo;
    protected $id;
    protected $agency_id;
    protected $plan_id;
    protected $start_date;
    protected $end_date;
    protected $status;
    protected $created_at;
    protected $updated_at;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    // Getters
    public function getId() { return $this->id; }
    public function getAgencyId() { return $this->agency_id; }
    public function getPlanId() { return $this->plan_id; }
    public function getStartDate() { return $this->start_date; }
    public function getEndDate() { return $this->end_date; }
    public function getStatus() { return $this->status; }
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedAt() { return $this->updated_at; }

    // Protected setters
    protected function setId($id) { $this->id = $id; }
    protected function setAgencyId($agency_id) { $this->agency_id = $agency_id; }
    protected function setPlanId($plan_id) { $this->plan_id = $plan_id; }
    protected function setStartDate($start_date) { $this->start_date = $start_date; }
    protected function setEndDate($end_date) { $this->end_date = $end_date; }
    protected function setStatus($status) { $this->status = $status; }
    protected function setCreatedAt($created_at) { $this->created_at = $created_at; }
    protected function setUpdatedAt($updated_at) { $this->updated_at = $updated_at; }

    /**
     * Crée un objet Subscriptions à partir des données de la base
     * @param array $data
     * @return Subscriptions
     */
    protected static function fromData(array $data): Subscriptions
    {
        $subscription = new self();
        $subscription->setId($data['id']);
        $subscription->setAgencyId($data['agency_id']);
        $subscription->setPlanId($data['plan_id']);
        $subscription->setStartDate($data['start_date']);
        $subscription->setEndDate($data['end_date'] ?? null);
        $subscription->setStatus($data['status']);
        $subscription->setCreatedAt($data['created_at']);
        $subscription->setUpdatedAt($data['updated_at'] ?? null);
        return $subscription;
    }

    /**
     * Trouve un abonnement par ID
     * @param int $id
     * @return Subscriptions|null
     */
    public static function find($id)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT * FROM subscriptions WHERE id = ?');
            $stmt->execute([$id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? self::fromData($data) : null;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche de l’abonnement : " . $e->getMessage());
        }
    }

    /**
     * Alias pour find
     * @param int $id
     * @return Subscriptions|null
     */
    public static function findById($id)
    {
        return self::find($id);
    }

    /**
     * Récupère tous les abonnements
     * @return array
     */
    public static function get()
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->query('SELECT * FROM subscriptions ORDER BY created_at DESC');
            $subscriptions = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $subscriptions[] = self::fromData($data);
            }
            return $subscriptions;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération des abonnements : " . $e->getMessage());
        }
    }

    /**
     * Récupère le premier abonnement (par date de création)
     * @return Subscriptions|null
     */
    public static function first()
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->query('SELECT * FROM subscriptions ORDER BY created_at ASC LIMIT 1');
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? self::fromData($data) : null;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération du premier abonnement : " . $e->getMessage());
        }
    }

    /**
     * Crée un nouvel abonnement
     * @param array $data
     * @return Subscriptions
     */
    public static function create(array $data)
    {
        try {
            $pdo = Database::getInstance();
            // Vérifier l’existence de l’agence et du plan
            if (!Agency::find($data['agency_id'])) {
                throw new PDOException("Agency ID invalide");
            }
            if (!SubscriptionPlans::find($data['plan_id'])) {
                throw new PDOException("Plan ID invalide");
            }
            // Validation basique de status
            $validStatuses = ['active', 'pending', 'expired', 'cancelled']; // À ajuster
            if (!in_array($data['status'], $validStatuses)) {
                throw new PDOException("Statut d’abonnement invalide");
            }
            $stmt = $pdo->prepare('
                INSERT INTO subscriptions (
                    agency_id, plan_id, start_date, end_date, status, created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, NOW(), NOW())
            ');
            $stmt->execute([
                $data['agency_id'],
                $data['plan_id'],
                $data['start_date'],
                $data['end_date'] ?? null,
                $data['status']
            ]);
            $id = $pdo->lastInsertId();
            return self::find($id);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la création de l’abonnement : " . $e->getMessage());
        }
    }

    /**
     * Met à jour un abonnement
     * @param int $id
     * @param array $data
     * @return Subscriptions|null
     */
    public static function update($id, array $data)
    {
        try {
            $pdo = Database::getInstance();
            $existing = self::find($id);
            if (!$existing) {
                throw new PDOException("Abonnement introuvable");
            }
            // Vérifier l’existence de l’agence et du plan si modifiés
            if (!empty($data['agency_id']) && !Agency::find($data['agency_id'])) {
                throw new PDOException("Agency ID invalide");
            }
            if (!empty($data['plan_id']) && !SubscriptionPlans::find($data['plan_id'])) {
                throw new PDOException("Plan ID invalide");
            }
            // Validation basique de status si modifié
            if (!empty($data['status'])) {
                $validStatuses = ['active', 'pending', 'expired', 'cancelled']; // À ajuster
                if (!in_array($data['status'], $validStatuses)) {
                    throw new PDOException("Statut d’abonnement invalide");
                }
            }
            $stmt = $pdo->prepare('
                UPDATE subscriptions
                SET agency_id = ?, plan_id = ?, start_date = ?, end_date = ?, status = ?, updated_at = NOW()
                WHERE id = ?
            ');
            $stmt->execute([
                $data['agency_id'] ?? $existing->getAgencyId(),
                $data['plan_id'] ?? $existing->getPlanId(),
                $data['start_date'] ?? $existing->getStartDate(),
                $data['end_date'] ?? $existing->getEndDate(),
                $data['status'] ?? $existing->getStatus(),
                $id
            ]);
            return self::find($id);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la mise à jour de l’abonnement : " . $e->getMessage());
        }
    }

    /**
     * Supprime un abonnement
     * @param int $id
     * @return bool
     */
    public static function delete($id)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('DELETE FROM subscriptions WHERE id = ?');
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la suppression de l’abonnement : " . $e->getMessage());
        }
    }

    /**
     * Trouve un abonnement par agency_id
     * @param int $agencyId
     * @return array
     */
    public static function findByAgencyId($agencyId)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT * FROM subscriptions WHERE agency_id = ?');
            $stmt->execute([$agencyId]);
            $subscriptions = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $subscriptions[] = self::fromData($data);
            }
            return $subscriptions;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche des abonnements par agency_id : " . $e->getMessage());
        }
    }

    /**
     * Récupère l’agence associée
     * @return Agency|null
     */
    public function agency()
    {
        return Agency::find($this->agency_id);
    }

    /**
     * Récupère le plan associé
     * @return SubscriptionPlans|null
     */
    public function plan()
    {
        return SubscriptionPlans::find($this->plan_id);
    }
}