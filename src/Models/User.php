<?php
namespace App\Models;

use App\Config\Database;
use PDO;
use PDOException;

/**
 * Modèle pour la table users
 * ImmoApp (L2GIB, 2024-2025)
 * Représente un utilisateur (admin global, agent, propriétaire, locataire, acheteur)
 */
class User
{
    private $pdo;
    protected $id;
    protected $username;
    protected $email;
    protected $password;
    protected $role_id;
    protected $agency_id;
    protected $first_name;
    protected $last_name;
    protected $phone;
    protected $created_at;
    protected $updated_at;
    protected $deleted_at;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    // Getters
    public function getId() { return $this->id; }
    public function getUsername() { return $this->username; }
    public function getEmail() { return $this->email; }
    public function getRoleId() { return $this->role_id; }
    public function getAgencyId() { return $this->agency_id; }
    public function getFirstName() { return $this->first_name; }
    public function getLastName() { return $this->last_name; }
    public function getPhone() { return (int)$this->phone; }
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedAt() { return $this->updated_at; }
    public function getDeletedAt() { return $this->deleted_at; }

    // Protected setters
    protected function setId($id) { $this->id = $id; }
    protected function setUsername($username) { return $this->username = $username; }
    protected function setEmail($email) { return $this->email; }
    protected function setPassword($password) { return $this->password = password_hash($password, PASSWORD_BCRYPT); }
    protected function setRoleId($role_id) { $this->role_id = $role_id; }
    protected function setAgencyId($agency_id) { $this->agency_id = $agency_id; }
    protected function setFirstName($first_name) { $this->first_name = $first_name; }
    protected function setLastName($last_name) { $this->last_name = $last_name; }
    protected function setPhone($phone) { $this->phone = $phone; }
    protected function setCreatedAt($created_at) { $this->created_at = $created_at; }
    protected function setUpdatedAt($updated_at) { $this->updated_at = $updated_at; }
    protected function setDeletedAt($deleted_at) { $this->deleted_at = $deleted_at; }

    /**
     * Crée un objet User à partir des données de la base
     * @param array $data
     * @return User
     */
    protected static function fromData(array $data): User
    {
        $user = new self();
        $user->setId($data['id']);
        $user->setUsername($data['username']);
        $user->setEmail($data['email']);
        $user->password = $data['password']; // Déjà haché
        $user->setRoleId($data['role_id']);
        $user->setAgencyId($data['agency_id'] ?? null);
        $user->setFirstName($data['first_name']);
        $user->setLastName($data['last_name']);
        $user->setPhone($data['phone'] ?? null);
        $user->setCreatedAt($data['created_at']);
        $user->setUpdatedAt($data['updated_at'] ?? null);
        $user->setDeletedAt($data['deleted_at'] ?? null);
        return $user;
    }

    /**
     * Trouve un utilisateur par ID
     * @param int $id
     * @return User|null
     */
    public static function find($id)
    {
         try {
             $pdo = Database::getInstance();
             $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? AND deleted_at IS NULL');
             $stmt->execute([$id]);
             $data = $stmt->fetch(PDO::FETCH_ASSOC);
             return $data ? self::fromData($data) : null;
         } catch (PDOException $e) {
             throw new PDOException("Erreur lors de la recherche de l’utilisateur : " . $e->getMessage());
         }
     }

    /**
     * Alias pour find
     * @param int $id
     * @return User|null
     */
    public static function findById(int $id)
    {
        return self::find($id);
     }

    /**
     * Récupère tous les utilisateurs
     * @return array Array
     */
    public static function get()
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->query('SELECT * FROM users WHERE deleted_at IS NULL ORDER BY created_at DESC');
            $users = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $users[] = self::fromData($data);
            }
             return $users;
         } catch (PDOException $e) {
             throw new PDOException("Erreur lors de la récupération des utilisateurs : " . $e->getMessage());
         }
     }

    /**
     * Récupère le premier utilisateur (par date de création)
     * @return User|null
     */
    public static function first()
    {
         try {
             $pdo = Database::getInstance();
             $stmt = $pdo->query('SELECT * FROM users WHERE deleted_at IS NULL ORDER BY created_at ASC LIMIT 1');
             $data = $stmt->fetch(PDO::FETCH_ASSOC);
             return $data ? self::fromData($data) : null;
         } catch (PDOException $e) {
             throw new PDOException("Erreur lors de la récupération du premier utilisateur : " . $e->getMessage());
         }
     }

    /**
     * Crée un nouvel utilisateur
     * @param array $data
     * @return User
     */
    public static function create(array $data)
    {
         try {
             $pdo = Database::getInstance();
             // Vérifier l’existence du rôle
             if (!Role::find($data['role_id'])) {
                 throw new PDOException("Role ID invalide");
             }
             $stmt = $pdo->prepare('
                 INSERT INTO users (username, email, password, role_id, agency_id, first_name, last_name, phone, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
             ');
             $stmt->execute([
                 $data['username'],
                 $data['email'],
                 password_hash($data['password'], PASSWORD_BCRYPT),
                 $data['role_id'],
                 $data['agency_id'] ?? null,
                 $data['first_name'],
                 $data['last_name'],
                 $data['phone'] ?? null
             ]);
             $id = $pdo->lastInsertId();
             return self::find($id);
         } catch (PDOException $e) {
             throw new PDOException("Erreur lors de la création de l’utilisateur : " . $e->getMessage());
         }
     }

    /**
     * Met à jour un utilisateur
     * @param int $id
     * @param array $data
     * @return User|null
     */
public static function update($id, array $data)
{
    try {
        $pdo = Database::getInstance();

        // On récupère d'abord l'utilisateur existant
        $user = self::find($id);
        if (!$user) {
            throw new PDOException("Utilisateur introuvable.");
        }

        // Vérification de rôle valide si spécifié
        if (!empty($data['role_id']) && !Role::find($data['role_id'])) {
            throw new PDOException("Role ID invalide");
        }

        $sql = 'UPDATE users SET 
                    username = ?, 
                    email = ?, 
                    role_id = ?, 
                    agency_id = ?, 
                    first_name = ?, 
                    last_name = ?, 
                    phone = ?, 
                    updated_at = NOW()';
        
        $params = [
            $data['username'] ?? $user->getUsername(),
            $data['email'] ?? $user->getEmail(),
            $data['role_id'] ?? $user->getRoleId(),
            $data['agency_id'] ?? $user->getAgencyId(),
            $data['first_name'] ?? $user->getFirstName(),
            $data['last_name'] ?? $user->getLastName(),
            $data['phone'] ?? $user->getPhone()
        ];

        // Si mot de passe fourni, on l'ajoute à la requête
        if (!empty($data['password'])) {
            $sql .= ', password = ?';
            $params[] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        $sql .= ' WHERE id = ?';
        $params[] = $id;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        return self::find($id);

    } catch (PDOException $e) {
        throw new PDOException("Erreur lors de la mise à jour de l’utilisateur : " . $e->getMessage());
    }
}


    /**
     * Supprime un utilisateur (soft delete)
     * @param int $id
     * @return bool
     */
    public static function delete($id)
    {
         try {
             $pdo = Database::getInstance();
             $stmt = $pdo->prepare('UPDATE users SET deleted_at = NOW() WHERE id = ?');
             return $stmt->execute([$id]);
         } catch (PDOException $e) {
             throw new PDOException("Erreur lors de la suppression de l’utilisateur : " . $e->getMessage());
         }
     }

    /**
     * Trouve un utilisateur par email
     * @param string $email
     * @return User|null
     */
    public static function findByEmail($email)
    {
         try {
             $pdo = Database::getInstance();
             $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? AND deleted_at IS NULL');
             $stmt->execute([$email]);
             $data = $stmt->fetch(PDO::FETCH_ASSOC);
             return $data ? self::fromData($data) : null;
         } catch (PDOException $e) {
             throw new PDOException("Erreur lors de la recherche de l’utilisateur par email : " . $e->getMessage());
         }
     }

    /**
     * Vérifie les identifiants pour l’authentification
     * @param string $email
     * @param string $password
     * @return User|null
     */
    public static function attempt($email, $password)
    {
         $user = self::findByEmail($email);
         if ($user && password_verify($password, $user->password)) {
             return $user;
         }
         return null;
     }

    /**
     * Récupère le rôle de l’utilisateur
     * @return Role|null
     */
    public function role()
    {
         return Role::find($this->role_id);
     }

    /**
     * Trouve les utilisateurs par agency_id
     * @param int $agencyId
     * @return array
     */
    public static function findByAgencyId($agencyId)
    {
         try {
             $pdo = Database::getInstance();
             $stmt = $pdo->prepare('SELECT * FROM users WHERE agency_id = ? AND deleted_at IS NULL');
             $stmt->execute([$agencyId]);
             $users = [];
             while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                 $users[] = self::fromData($data);
             }
             return $users;
         } catch (PDOException $e) {
             throw new PDOException("Erreur lors de la recherche des utilisateurs par agency_id : " . $e->getMessage());
         }
     }
}