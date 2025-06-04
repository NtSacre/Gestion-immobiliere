<?php
namespace App\Models;

use App\Config\Database;
use PDO;
use PDOException;

/**
 * Modèle pour la table subscription_payments
 * ImmoApp (L2GIB, 2024-2025)
 * Représente un paiement effectué pour un abonnement d’agence
 */
class SubscriptionPayments
{
    private $pdo;
    protected $id;
    protected $subscription_id;
    protected $payment_method_id;
    protected $amount;
    protected $payment_date;
    protected $status;
    protected $transaction_id;
    protected $created_at;
    protected $updated_at;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    // Getters
    public function getId() { return $this->id; }
    public function getSubscriptionId() { return $this->subscription_id; }
    public function getPaymentMethodId() { return $this->payment_method_id; }
    public function getAmount() { return $this->amount; }
    public function getPaymentDate() { return $this->payment_date; }
    public function getStatus() { return $this->status; }
    public function getTransactionId() { return $this->transaction_id; }
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedAt() { return $this->updated_at; }

    // Protected setters
    protected function setId($id) { $this->id = $id; }
    protected function setSubscriptionId($subscription_id) { $this->subscription_id = $subscription_id; }
    protected function setPaymentMethodId($payment_method_id) { $this->payment_method_id = $payment_method_id; }
    protected function setAmount($amount) { $this->amount = $amount; }
    protected function setPaymentDate($payment_date) { $this->payment_date = $payment_date; }
    protected function setStatus($status) { $this->status = $status; }
    protected function setTransactionId($transaction_id) { $this->transaction_id = $transaction_id; }
    protected function setCreatedAt($created_at) { $this->created_at = $created_at; }
    protected function setUpdatedAt($updated_at) { $this->updated_at = $updated_at; }

    /**
     * Crée un objet SubscriptionPayments à partir des données de la base
     * @param array $data
     * @return SubscriptionPayments
     */
    protected static function fromData(array $data): SubscriptionPayments
    {
        $payment = new self();
        $payment->setId($data['id']);
        $payment->setSubscriptionId($data['subscription_id']);
        $payment->setPaymentMethodId($data['payment_method_id']);
        $payment->setAmount($data['amount']);
        $payment->setPaymentDate($data['payment_date']);
        $payment->setStatus($data['status']);
        $payment->setTransactionId($data['transaction_id'] ?? null);
        $payment->setCreatedAt($data['created_at']);
        $payment->setUpdatedAt($data['updated_at'] ?? null);
        return $payment;
    }

    /**
     * Trouve un paiement d’abonnement par ID
     * @param int $id
     * @return SubscriptionPayments|null
     */
    public static function find($id)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT * FROM subscription_payments WHERE id = ?');
            $stmt->execute([$id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? self::fromData($data) : null;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche du paiement d’abonnement : " . $e->getMessage());
        }
    }

    /**
     * Alias pour find
     * @param int $id
     * @return SubscriptionPayments|null
     */
    public static function findById($id)
    {
        return self::find($id);
    }

    /**
     * Récupère tous les paiements d’abonnement
     * @return array
     */
    public static function get()
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->query('SELECT * FROM subscription_payments ORDER BY payment_date DESC');
            $payments = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $payments[] = self::fromData($data);
            }
            return $payments;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération des paiements d’abonnement : " . $e->getMessage());
        }
    }

    /**
     * Récupère le premier paiement d’abonnement (par date de paiement)
     * @return SubscriptionPayments|null
     */
    public static function first()
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->query('SELECT * FROM subscription_payments ORDER BY payment_date ASC LIMIT 1');
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? self::fromData($data) : null;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération du premier paiement d’abonnement : " . $e->getMessage());
        }
    }

    /**
     * Crée un nouveau paiement d’abonnement
     * @param array $data
     * @return SubscriptionPayments
     */
    public static function create(array $data)
    {
        try {
            $pdo = Database::getInstance();
            // Vérifier l’existence de l’abonnement et de la méthode de paiement
            if (!Subscriptions::find($data['subscription_id'])) {
                throw new PDOException("Subscription ID invalide");
            }
            if (!PaymentMethods::find($data['payment_method_id'])) {
                throw new PDOException("Payment Method ID invalide");
            }
            // Validation basique de status
            $validStatuses = ['pending', 'completed', 'failed']; // Exemple, à ajuster
            if (!in_array($data['status'], $validStatuses)) {
                throw new PDOException("Statut de paiement invalide");
            }
            $stmt = $pdo->prepare('
                INSERT INTO subscription_payments (
                    subscription_id, payment_method_id, amount, payment_date, status, transaction_id,
                    created_at, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
            ');
            $stmt->execute([
                $data['subscription_id'],
                $data['payment_method_id'],
                $data['amount'],
                $data['payment_date'],
                $data['status'],
                $data['transaction_id'] ?? null
            ]);
            $id = $pdo->lastInsertId();
            return self::find($id);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la création du paiement d’abonnement : " . $e->getMessage());
        }
    }

    /**
     * Met à jour un paiement d’abonnement
     * @param int $id
     * @param array $data
     * @return SubscriptionPayments|null
     */
    public static function update($id, array $data)
    {
        try {
            $pdo = Database::getInstance();
            $existing = self::find($id);
            if (!$existing) {
                throw new PDOException("Paiement d’abonnement introuvable");
            }
            // Vérifier l’existence de l’abonnement et de la méthode de paiement si modifiés
            if (!empty($data['subscription_id']) && !Subscriptions::find($data['subscription_id'])) {
                throw new PDOException("Subscription ID invalide");
            }
            if (!empty($data['payment_method_id']) && !PaymentMethods::find($data['payment_method_id'])) {
                throw new PDOException("Payment Method ID invalide");
            }
            // Validation basique de status si modifié
            if (!empty($data['status'])) {
                $validStatuses = ['pending', 'completed', 'failed']; // Exemple, à ajuster
                if (!in_array($data['status'], $validStatuses)) {
                    throw new PDOException("Statut de paiement invalide");
                }
            }
            $stmt = $pdo->prepare('
                UPDATE subscription_payments
                SET subscription_id = ?, payment_method_id = ?, amount = ?, payment_date = ?,
                    status = ?, transaction_id = ?, updated_at = NOW()
                WHERE id = ?
            ');
            $stmt->execute([
                $data['subscription_id'] ?? $existing->getSubscriptionId(),
                $data['payment_method_id'] ?? $existing->getPaymentMethodId(),
                $data['amount'] ?? $existing->getAmount(),
                $data['payment_date'] ?? $existing->getPaymentDate(),
                $data['status'] ?? $existing->getStatus(),
                $data['transaction_id'] ?? $existing->getTransactionId(),
                $id
            ]);
            return self::find($id);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la mise à jour du paiement d’abonnement : " . $e->getMessage());
        }
    }

    /**
     * Supprime un paiement d’abonnement
     * @param int $id
     * @return bool
     */
    public static function delete($id)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('DELETE FROM subscription_payments WHERE id = ?');
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la suppression du paiement d’abonnement : " . $e->getMessage());
        }
    }

    /**
     * Trouve les paiements par subscription_id
     * @param int $subscriptionId
     * @return array
     */
    public static function findBySubscriptionId($subscriptionId)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT * FROM subscription_payments WHERE subscription_id = ? ORDER BY payment_date DESC');
            $stmt->execute([$subscriptionId]);
            $payments = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $payments[] = self::fromData($data);
            }
            return $payments;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche des paiements par subscription_id : " . $e->getMessage());
        }
    }

    /**
     * Trouve les paiements par payment_method_id
     * @param int $paymentMethodId
     * @return array
     */
    public static function findByPaymentMethodId($paymentMethodId)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT * FROM subscription_payments WHERE payment_method_id = ? ORDER BY payment_date DESC');
            $stmt->execute([$paymentMethodId]);
            $payments = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $payments[] = self::fromData($data);
            }
            return $payments;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche des paiements par payment_method_id : " . $e->getMessage());
        }
    }

    /**
     * Récupère l’abonnement associé
     * @return Subscriptions|null
     */
    public function subscription()
    {
        return Subscriptions::find($this->subscription_id);
    }

    /**
     * Récupère la méthode de paiement associée
     * @return PaymentMethods|null
     */
    public function paymentMethod()
    {
        return PaymentMethods::find($this->payment_method_id);
    }
}