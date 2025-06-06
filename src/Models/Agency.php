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
    protected $id;
    protected $name;
    protected $siret;
    protected $address;
    protected $phone;
    protected $email;
    protected $created_at;
    protected $updated_at;
    protected $deleted_at;

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
     * @return Agency
     */
    protected static function fromData(array $data): Agency
    {
        $agency = new self();
        $agency->setId($data['id']);
        $agency->setName($data['name']);
        $agency->setSiret($data['siret']);
        $agency->setAddress($data['address']);
        $agency->setPhone($data['phone'] ?? null);
        $agency->setEmail($data['email']);
        $agency->setCreatedAt($data['created_at']);
        $agency->setUpdatedAt($data['updated_at'] ?? null);
        $agency->setDeletedAt($data['deleted_at'] ?? null);
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
            throw new PDOException("Erreur lors de la recherche de l’agence : " . $e->getMessage());
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
            $stmt = $pdo->query('SELECT * FROM agencies WHERE is_deleted IS FALSE ORDER BY name ASC');
            $agencies = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $agencies[] = self::fromData($data);
            }
            return $agencies;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération des agences : " . $e->getMessage());
        }
    }

    /**
     * Récupère la première agence (par nom)
     * @return Agency|null
     */
    public static function first()
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->query('SELECT * FROM agencies WHERE is_deleted IS FALSE ORDER BY name ASC LIMIT 1');
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? self::fromData($data) : null;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération de la première agence : " . $e->getMessage());
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
            throw new PDOException("Erreur lors de la création de l’agence : " . $e->getMessage());
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
            throw new PDOException("Erreur lors de la mise à jour de l’agence : " . $e->getMessage());
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
            throw new PDOException("Erreur lors de la suppression de l’agence : " . $e->getMessage());
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
            throw new PDOException("Erreur lors de la recherche de l’agence par email : " . $e->getMessage());
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