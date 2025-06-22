<?php
namespace App\Models;

use App\Config\Database;
use PDO;
use PDOException;

/**
 * Modèle pour la table agencies
 * ImmoApp (L2GIB, 2024-2025)
 * Représente une agence immobilière
 */
class Agency
{
    private $pdo;
    protected $id = null;
    protected $name = null;
    protected $siret = null;
    protected $address = null;
    protected $phone = null;
    protected $email = null;
    protected $created_at = null;
    protected $updated_at = null;
    protected $deleted_at = null;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    // Getters
    public function getId() { return $this->id; }
    public function getName() { return $this->name; }
    public function getSiret() { return $this->siret; }
    public function getAddress() { return $this->address; }
    public function getPhone() { return $this->phone; }
    public function getEmail() { return $this->email; }
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedAt() { return $this->updated_at; }
    public function getDeletedAt() { return $this->deleted_at; }

    // Protected setters
    protected function setId($id) { $this->id = $id; }
    protected function setName($name) { $this->name = $name; }
    protected function setSiret($siret) { $this->siret = $siret; }
    protected function setAddress($address) { $this->address = $address; }
    protected function setPhone($phone) { $this->phone = $phone; }
    protected function setEmail($email) { $this->email = $email; }
    protected function setCreatedAt($created_at) { $this->created_at = $created_at; }
    protected function setUpdatedAt($updated_at) { $this->updated_at = $updated_at; }
    protected function setDeletedAt($deleted_at) { $this->deleted_at = $deleted_at; }

    /**
     * Crée un objet Agency à partir des données de la base
     * @param array $data
     * @return Agency|null
     */
    protected static function fromData(array $data): ?Agency
    {
        // Vérification initiale des champs essentiels
        if (!isset($data['id']) || $data['id'] === null || !isset($data['name']) || $data['name'] === null) {
            error_log("Ligne rejetée dans Agency::fromData() car invalide (pré-initialisation) : " . json_encode($data));
            return null;
        }

        $agency = new self();
        $agency->setId($data['id']);
        $agency->setName($data['name']);
        $agency->setSiret($data['siret'] ?? null);
        $agency->setAddress($data['address'] ?? null);
        $agency->setPhone($data['phone'] ?? null);
        $agency->setEmail($data['email'] ?? null);
        $agency->setCreatedAt($data['created_at'] ?? null);
        $agency->setUpdatedAt($data['updated_at'] ?? null);
        $agency->setDeletedAt($data['deleted_at'] ?? null);

        // Vérification finale après initialisation
        if ($agency->getId() === null || $agency->getName() === null) {
            error_log("Objet Agency mal initialisé dans Agency::fromData() : " . json_encode($data));
            return null;
        }

        return $agency;
    }

    /**
     * Trouve une agence par ID
     * @param int $id
     * @return Agency|null
     */
    public static function find($id)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT * FROM agencies WHERE id = ? AND is_deleted IS FALSE');
            $stmt->execute([$id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? self::fromData($data) : null;
        } catch (PDOException $e) {
            error_log("Erreur dans Agency::find() : " . $e->getMessage());
            return null;
        }
    }

    /**
     * Alias pour find
     * @param int $id
     * @return Agency|null
     */
    public static function findById($id)
    {
        return self::find($id);
    }

    /**
     * Récupère toutes les agences
     * @return array
     */
    public static function get()
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->query('SELECT * FROM agencies WHERE is_deleted IS FALSE AND id IS NOT NULL AND name IS NOT NULL ORDER BY name ASC');
            $agencies = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $agency = self::fromData($data);
                if ($agency !== null) {
                    $agencies[] = $agency;
                } else {
                    error_log("Ligne ignorée dans Agency::get() après fromData : " . json_encode($data));
                }
            }
            return $agencies;
        } catch (PDOException $e) {
            error_log("Erreur dans Agency::get() : " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère toutes les agences (alias de get)
     * @return array
     */
    public static function all()
    {
        return self::get();
    }

    /**
     * Récupère la première agence (par nom)
     * @return Agency|null
     */
    public static function first()
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->query('SELECT * FROM agencies WHERE is_deleted IS FALSE AND id IS NOT NULL AND name IS NOT NULL ORDER BY name ASC LIMIT 1');
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? self::fromData($data) : null;
        } catch (PDOException $e) {
            error_log("Erreur dans Agency::first() : " . $e->getMessage());
            return null;
        }
    }

    /**
     * Crée une nouvelle agence
     * @param array $data
     * @return Agency
     */
    public static function create(array $data)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('
                INSERT INTO agencies (name, siret, address, phone, email, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, NOW(), NOW())
            ');
            $stmt->execute([
                $data['name'],
                $data['siret'],
                $data['address'],
                $data['phone'] ?? null,
                $data['email']
            ]);
            $id = $pdo->lastInsertId();
            return self::find($id);
        } catch (PDOException $e) {
            error_log("Erreur dans Agency::create() : " . $e->getMessage());
            return null;
        }
    }

    /**
     * Met à jour une agence
     * @param int $id
     * @param array $data
     * @return Agency|null
     */
    public static function update($id, array $data)
    {
        try {
            $pdo = Database::getInstance();
            $existing = self::find($id);
            if (!$existing) {
                throw new PDOException("Agence introuvable");
            }
            $stmt = $pdo->prepare('
                UPDATE agencies
                SET name = ?, siret = ?, address = ?, phone = ?, email = ?, updated_at = NOW()
                WHERE id = ?
            ');
            $stmt->execute([
                $data['name'] ?? $existing->getName(),
                $data['siret'] ?? $existing->getSiret(),
                $data['address'] ?? $existing->getAddress(),
                $data['phone'] ?? $existing->getPhone(),
                $data['email'] ?? $existing->getEmail(),
                $id
            ]);
            return self::find($id);
        } catch (PDOException $e) {
            error_log("Erreur dans Agency::update() : " . $e->getMessage());
            return null;
        }
    }

    /**
     * Supprime une agence (soft delete)
     * @param int $id
     * @return bool
     */
    public static function delete($id)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('UPDATE agencies SET deleted_at = NOW() WHERE id = ?');
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Erreur dans Agency::delete() : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Trouve une agence par email
     * @param string $email
     * @return Agency|null
     */
    public static function findByEmail($email)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT * FROM agencies WHERE email = ? AND is_deleted IS FALSE');
            $stmt->execute([$email]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? self::fromData($data) : null;
        } catch (PDOException $e) {
            error_log("Erreur dans Agency::findByEmail() : " . $e->getMessage());
            return null;
        }
    }

    /**
     * Récupère les utilisateurs de l’agence
     * @return array
     */
    public function users()
    {
        return User::findByAgencyId($this->id);
    }
}