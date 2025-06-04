<?php
namespace App\Models;

use App\Config\Database;
use PDO;
use PDOException;

/**
 * Modèle pour la table payment_methods
 * ImmoApp (L2GIB, 2024-2025)
 * Représente une méthode de paiement pour une agence (carte, virement, etc.)
 */
class PaymentMethods
{
    private $pdo;
    protected $id;
    protected $agency_id;
    protected $type;
    protected $details;
    protected $is_default;
    protected $created_at;
    protected $updated_at;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    // Getters
    public function getId() { return $this->id; }
    public function getAgencyId() { return $this->agency_id; }
    public function getType() { return $this->type; }
    public function getDetails() { return $this->details; }
    public function getIsDefault() { return $this->is_default; }
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedAt() { return $this->updated_at; }

    // Protected setters
    protected function setId($id) { $this->id = $id; }
    protected function setAgencyId($agency_id) { $this->agency_id = $agency_id; }
    protected function setType($type) { $this->type = $type; }
    protected function setDetails($details) { $this->details = $details; }
    protected function setIsDefault($is_default) { $this->is_default = $is_default; }
    protected function setCreatedAt($created_at) { $this->created_at = $created_at; }
    protected function setUpdatedAt($updated_at) { $this->updated_at = $updated_at; }

    /**
     * Crée un objet PaymentMethods à partir des données de la base
     * @param array $data
     * @return PaymentMethods
     */
    protected static function fromData(array $data): PaymentMethods
    {
        $paymentMethod = new self();
        $paymentMethod->setId($data['id']);
        $paymentMethod->setAgencyId($data['agency_id']);
        $paymentMethod->setType($data['type']);
        $paymentMethod->setDetails($data['details'] ? json_decode($data['details'], true) : null);
        $paymentMethod->setIsDefault($data['is_default'] ?? 0);
        $paymentMethod->setCreatedAt($data['created_at']);
        $paymentMethod->setUpdatedAt($data['updated_at'] ?? null);
        return $paymentMethod;
    }

    /**
     * Trouve une méthode de paiement par ID
     * @param int $id
     * @return PaymentMethods|null
     */
    public static function find($id)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT * FROM payment_methods WHERE id = ?');
            $stmt->execute([$id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? self::fromData($data) : null;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche de la méthode de paiement : " . $e->getMessage());
        }
    }

    /**
     * Alias pour find
     * @param int $id
     * @return PaymentMethods|null
     */
    public static function findById($id)
    {
        return self::find($id);
    }

    /**
     * Récupère toutes les méthodes de paiement
     * @return array
     */
    public static function get()
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->query('SELECT * FROM payment_methods ORDER BY created_at DESC');
            $paymentMethods = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $paymentMethods[] = self::fromData($data);
            }
            return $paymentMethods;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération des méthodes de paiement : " . $e->getMessage());
        }
    }

    /**
     * Récupère la première méthode de paiement (par date de création)
     * @return PaymentMethods|null
     */
    public static function first()
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->query('SELECT * FROM payment_methods ORDER BY created_at ASC LIMIT 1');
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? self::fromData($data) : null;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération de la première méthode de paiement : " . $e->getMessage());
        }
    }

    /**
     * Crée une nouvelle méthode de paiement
     * @param array $data
     * @return PaymentMethods
     */
    public static function create(array $data)
    {
        try {
            $pdo = Database::getInstance();
            // Vérifier l’existence de l’agence
            if (!Agency::find($data['agency_id'])) {
                throw new PDOException("Agency ID invalide");
            }
            // Validation basique de type
            $validTypes = ['credit_card', 'bank_transfer', 'paypal']; // À ajuster
            if (!in_array($data['type'], $validTypes)) {
                throw new PDOException("Type de méthode de paiement invalide");
            }
            $stmt = $pdo->prepare('
                INSERT INTO payment_methods (
                    agency_id, type, details, is_default, created_at, updated_at
                ) VALUES (?, ?, ?, ?, NOW(), NOW())
            ');
            $stmt->execute([
                $data['agency_id'],
                $data['type'],
                json_encode($data['details']),
                $data['is_default'] ?? 0
            ]);
            $id = $pdo->lastInsertId();
            return self::find($id);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la création de la méthode de paiement : " . $e->getMessage());
        }
    }

    /**
     * Met à jour une méthode de paiement
     * @param int $id
     * @param array $data
     * @return PaymentMethods|null
     */
    public static function update($id, array $data)
    {
        try {
            $pdo = Database::getInstance();
            $existing = self::find($id);
            if (!$existing) {
                throw new PDOException("Méthode de paiement introuvable");
            }
            // Vérifier l’existence de l’agence si modifié
            if (!empty($data['agency_id']) && !Agency::find($data['agency_id'])) {
                throw new PDOException("Agency ID invalide");
            }
            // Validation basique de type si modifié
            if (!empty($data['type'])) {
                $validTypes = ['credit_card', 'bank_transfer', 'paypal']; // À ajuster
                if (!in_array($data['type'], $validTypes)) {
                    throw new PDOException("Type de méthode de paiement invalide");
                }
            }
            $stmt = $pdo->prepare('
                UPDATE payment_methods
                SET agency_id = ?, type = ?, details = ?, is_default = ?, updated_at = NOW()
                WHERE id = ?
            ');
            $stmt->execute([
                $data['agency_id'] ?? $existing->getAgencyId(),
                $data['type'] ?? $existing->getType(),
                isset($data['details']) ? json_encode($data['details']) : json_encode($existing->getDetails()),
                $data['is_default'] ?? $existing->getIsDefault(),
                $id
            ]);
            return self::find($id);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la mise à jour de la méthode de paiement : " . $e->getMessage());
        }
    }

    /**
     * Supprime une méthode de paiement
     * @param int $id
     * @return bool
     */
    public static function delete($id)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('DELETE FROM payment_methods WHERE id = ?');
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la suppression de la méthode de paiement : " . $e->getMessage());
        }
    }

    /**
     * Trouve les méthodes de paiement par agency_id
     * @param int $agencyId
     * @return array
     */
    public static function findByAgencyId($agencyId)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT * FROM payment_methods WHERE agency_id = ?');
            $stmt->execute([$agencyId]);
            $paymentMethods = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $paymentMethods[] = self::fromData($data);
            }
            return $paymentMethods;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche des méthodes de paiement par agency_id : " . $e->getMessage());
        }
    }

    /**
     * Trouve la méthode de paiement par défaut pour une agence
     * @param int $agencyId
     * @return PaymentMethods|null
     */
    public static function findDefaultByAgencyId($agencyId)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT * FROM payment_methods WHERE agency_id = ? AND is_default = 1');
            $stmt->execute([$agencyId]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? self::fromData($data) : null;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche de la méthode de paiement par défaut : " . $e->getMessage());
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
}