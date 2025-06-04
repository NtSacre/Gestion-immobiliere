<?php
namespace App\Models;

use App\Config\Database;
use PDO;
use PDOException;

/**
 * Modèle pour la table subscription_plans
 * ImmoApp (L2GIB, 2024-2025)
 * Représente un plan d’abonnement pour les agences
 */
class SubscriptionPlans
{
    private $pdo;
    protected $id;
    protected $name;
    protected $price;
    protected $max_buildings;
    protected $max_users;
    protected $features;
    protected $description;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    // Getters
    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getPrice() { return $this->price; }
    public function getMaxBuildings() { return $this->max_buildings; }
    public function getMaxUsers() { return $this->max_users; }
    public function getFeatures() { return $this->features; }
    public function getDescription() { return $this->description; }

    // Protected setters
    protected function setId($id) { $this->id = $id; }
    protected function setName($name) { $this->name = $name; }
    protected function setPrice($price) { $this->price = $price; }
    protected function setMaxBuildings($max_buildings) { $this->max_buildings = $max_buildings; }
    protected function setMaxUsers($max_users) { $this->max_users = $max_users; }
    protected function setFeatures($features) { $this->features = $features; }
    protected function setDescription($description) { $this->description = $description; }

    /**
     * Crée un objet SubscriptionPlans à partir des données de la base
     * @param array $data
     * @return SubscriptionPlans
     */
    protected static function fromData(array $data): SubscriptionPlans
    {
        $plan = new self();
        $plan->setId($data['id']);
        $plan->setName($data['name']);
        $plan->setPrice($data['price']);
        $plan->setMaxBuildings($data['max_buildings'] ?? null);
        $plan->setMaxUsers($data['max_users'] ?? null);
        $plan->setFeatures($data['features'] ? json_decode($data['features'], true) : null);
        $plan->setDescription($data['description'] ?? null);
        return $plan;
    }

    /**
     * Trouve un plan d’abonnement par ID
     * @param int $id
     * @return SubscriptionPlans|null
     */
    public static function find($id)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT * FROM subscription_plans WHERE id = ?');
            $stmt->execute([$id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? self::fromData($data) : null;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche du plan d’abonnement : " . $e->getMessage());
        }
    }

    /**
     * Alias pour find
     * @param int $id
     * @return SubscriptionPlans|null
     */
    public static function findById($id)
    {
        return self::find($id);
    }

    /**
     * Récupère tous les plans d’abonnement
     * @return array
     */
    public static function get()
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->query('SELECT * FROM subscription_plans ORDER BY price ASC');
            $plans = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $plans[] = self::fromData($data);
            }
            return $plans;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération des plans d’abonnement : " . $e->getMessage());
        }
    }

    /**
     * Récupère le premier plan d’abonnement (par prix)
     * @return SubscriptionPlans|null
     */
    public static function first()
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->query('SELECT * FROM subscription_plans ORDER BY price ASC LIMIT 1');
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? self::fromData($data) : null;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération du premier plan d’abonnement : " . $e->getMessage());
        }
    }

    /**
     * Crée un nouveau plan d’abonnement
     * @param array $data
     * @return SubscriptionPlans
     */
    public static function create(array $data)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('
                INSERT INTO subscription_plans (
                    name, price, max_buildings, max_users, features, description
                ) VALUES (?, ?, ?, ?, ?, ?)
            ');
            $stmt->execute([
                $data['name'],
                $data['price'],
                $data['max_buildings'] ?? null,
                $data['max_users'] ?? null,
                $data['features'] ? json_encode($data['features']) : null,
                $data['description'] ?? null
            ]);
            $id = $pdo->lastInsertId();
            return self::find($id);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la création du plan d’abonnement : " . $e->getMessage());
        }
    }

    /**
     * Met à jour un plan d’abonnement
     * @param int $id
     * @param array $data
     * @return SubscriptionPlans|null
     */
    public static function update($id, array $data)
    {
        try {
            $pdo = Database::getInstance();
            $existing = self::find($id);
            if (!$existing) {
                throw new PDOException("Plan d’abonnement introuvable");
            }
            $stmt = $pdo->prepare('
                UPDATE subscription_plans
                SET name = ?, price = ?, max_buildings = ?, max_users = ?, features = ?, description = ?
                WHERE id = ?
            ');
            $stmt->execute([
                $data['name'] ?? $existing->getName(),
                $data['price'] ?? $existing->getPrice(),
                $data['max_buildings'] ?? $existing->getMaxBuildings(),
                $data['max_users'] ?? $existing->getMaxUsers(),
                isset($data['features']) ? json_encode($data['features']) : ($existing->getFeatures() ? json_encode($existing->getFeatures()) : null),
                $data['description'] ?? $existing->getDescription(),
                $id
            ]);
            return self::find($id);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la mise à jour du plan d’abonnement : " . $e->getMessage());
        }
    }

    /**
     * Supprime un plan d’abonnement
     * @param int $id
     * @return bool
     */
    public static function delete($id)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('DELETE FROM subscription_plans WHERE id = ?');
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la suppression du plan d’abonnement : " . $e->getMessage());
        }
    }

    /**
     * Trouve un plan d’abonnement par nom
     * @param string $name
     * @return SubscriptionPlans|null
     */
    public static function findByName($name)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT * FROM subscription_plans WHERE name = ?');
            $stmt->execute([$name]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? self::fromData($data) : null;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche du plan d’abonnement par nom : " . $e->getMessage());
        }
    }
}