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
    protected $is_deleted;

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
    public function getIsDeleted() { return $this->is_deleted; }

    // Protected setters
    protected function setId($id) { $this->id = $id; }
    protected function setUsername($username) { return $this->username = $username; }
    protected function setEmail($email) { return $this->email = $email; }
    protected function setPassword($password) { return $this->password = password_hash($password, PASSWORD_BCRYPT); }
    protected function setRoleId($role_id) { $this->role_id = $role_id; }
    protected function setAgencyId($agency_id) { $this->agency_id = $agency_id; }
    protected function setFirstName($first_name) { $this->first_name = $first_name; }
    protected function setLastName($last_name) { $this->last_name = $last_name; }
    protected function setPhone($phone) { $this->phone = $phone; }
    protected function setCreatedAt($created_at) { $this->created_at = $created_at; }
    protected function setUpdatedAt($updated_at) { $this->updated_at = $updated_at; }
    protected function setIsDeleted($is_deleted) { $this->is_deleted = $is_deleted; }

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
        $user->setIsDeleted($data['is_deleted'] ?? null);
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
             $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ? AND is_deleted IS FALSE');
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
            $stmt = $pdo->query('SELECT * FROM users WHERE is_deleted IS FALSE ORDER BY created_at DESC');
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
             $stmt = $pdo->query('SELECT * FROM users WHERE is_deleted IS FALSE ORDER BY created_at ASC LIMIT 1');
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
             $stmt = $pdo->prepare('UPDATE users SET updated_at = NOW() WHERE id = ?');
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
             $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? AND is_deleted IS FALSE');
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
    public  function role()
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
             $stmt = $pdo->prepare('SELECT * FROM users WHERE agency_id = ? AND is_deleted IS FALSE');
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

/**
 * Compte le nombre total de propriétaires (rôle "proprietaire", non supprimés)
 * @return int
 */
public static function countProprietaires()
{
    try {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('
            SELECT COUNT(*) 
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE r.name = "proprietaire" 
            AND u.is_deleted = 0
        ');
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    } catch (PDOException $e) {
        throw new PDOException("Erreur lors du comptage des propriétaires : " . $e->getMessage());
    }
}

/**
 * Compte les nouveaux propriétaires ajoutés ce mois
 * @return int
 */
public static function countNewProprietairesThisMonth()
{
    try {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('
            SELECT COUNT(*) 
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE r.name = "proprietaire" 
            AND u.is_deleted = 0 
            AND u.created_at >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)
        ');
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    } catch (PDOException $e) {
        throw new PDOException("Erreur lors du comptage des nouveaux propriétaires : " . $e->getMessage());
    }
}

    /**
     * Compte le nombre d'agents pour une agence spécifique.
     *
     * @param int $agency_id ID de l'agence
     * @return int Nombre d'agents
     */
    public static function countAgentsByAgency($agency_id)
    {
        try {
            $pdo = Database::getInstance();
            $query = "
                SELECT COUNT(*)
                FROM users
                WHERE role_id = (SELECT id FROM roles WHERE name = 'agent') AND agency_id = ?
            ";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$agency_id]);
            $result = $stmt->fetchColumn();
            return (int) $result;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors du comptage des agents pour une agence spécifique : " . $e->getMessage());
        }
    }

    /**
 * Récupère tous les utilisateurs avec recherche et pagination.
 *
 * @param string $search Terme de recherche (username, email, first_name, last_name)
 * @param int $limit Nombre d'utilisateurs par page
 * @param int $offset Décalage pour la pagination
 * @return array Liste des utilisateurs
 */
public static function getAll($search = '', $limit = 10, $offset = 0)
{
    try {
        $pdo = Database::getInstance();
        $query = "
            SELECT * FROM users 
            WHERE is_deleted = 0 
            AND (username LIKE ? OR email LIKE ? OR first_name LIKE ? OR last_name LIKE ?)
            ORDER BY created_at DESC
            LIMIT ? OFFSET ?
        ";
        $stmt = $pdo->prepare($query);
        $searchTerm = "%{$search}%";
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm, $limit, $offset]);
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
 * Compte tous les utilisateurs avec recherche.
 *
 * @param string $search Terme de recherche
 * @return int Nombre total d'utilisateurs
 */
public static function countAll($search = '')
{
    try {
        $pdo = Database::getInstance();
        $query = "
            SELECT COUNT(*) 
            FROM users 
            WHERE is_deleted = 0 
            AND (username LIKE ? OR email LIKE ? OR first_name LIKE ? OR last_name LIKE ?)
        ";
        $stmt = $pdo->prepare($query);
        $searchTerm = "%{$search}%";
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        return (int) $stmt->fetchColumn();
    } catch (PDOException $e) {
        throw new PDOException("Erreur lors du comptage des utilisateurs : " . $e->getMessage());
    }
}

/**
 * Récupère les utilisateurs d'une agence avec rôles spécifiques, recherche et pagination.
 *
 * @param int $agencyId ID de l'agence
 * @param string $search Terme de recherche
 * @param int $limit Nombre d'utilisateurs par page
 * @param int $offset Décalage pour la pagination
 * @param array $roles Liste des noms de rôles autorisés
 * @return array Liste des utilisateurs
 */
public static function getByAgency($agencyId, $search = '', $limit = 10, $offset = 0, $roles = [])
{
    try {
        $pdo = Database::getInstance();
        $query = "
            SELECT u.* 
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE u.is_deleted = 0 
            AND u.agency_id = ?
            AND (u.username LIKE ? OR u.email LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)
        ";
        $params = [$agencyId, "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%"];
        
        if (!empty($roles)) {
            $placeholders = implode(',', array_fill(0, count($roles), '?'));
            $query .= " AND r.name IN ($placeholders)";
            $params = array_merge($params, $roles);
        }
        
        $query .= " ORDER BY u.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $users = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $users[] = self::fromData($data);
        }
        return $users;
    } catch (PDOException $e) {
        throw new PDOException("Erreur lors de la récupération des utilisateurs par agence : " . $e->getMessage());
    }
}

/**
 * Compte les utilisateurs d'une agence avec rôles spécifiques et recherche.
 *
 * @param int $agencyId ID de l'agence
 * @param string $search Terme de recherche
 * @param array $roles Liste des noms de rôles autorisés
 * @return int Nombre total d'utilisateurs
 */
public static function countByAgency($agencyId, $search = '', $roles = [])
{
    try {
        $pdo = Database::getInstance();
        $query = "
            SELECT COUNT(*) 
            FROM users u
            JOIN roles r ON u.role_id = r.id
            WHERE u.is_deleted = 0 
            AND u.agency_id = ?
            AND (u.username LIKE ? OR u.email LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)
        ";
        $params = [$agencyId, "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%"];
        
        if (!empty($roles)) {
            $placeholders = implode(',', array_fill(0, count($roles), '?'));
            $query .= " AND r.name IN ($placeholders)";
            $params = array_merge($params, $roles);
        }

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    } catch (PDOException $e) {
        throw new PDOException("Erreur lors du comptage des utilisateurs par agence : " . $e->getMessage());
    }
}

/**
 * Récupère les utilisateurs créés par un agent dans une agence avec rôles spécifiques.
 *
 * @param int $agentId ID de l'agent
 * @param int $agencyId ID de l'agence
 * @param string $search Terme de recherche
 * @param int $limit Nombre d'utilisateurs par page
 * @param int $offset Décalage pour la pagination
 * @param array $roles Liste des noms de rôles autorisés
 * @return array Liste des utilisateurs
 */
public static function getByAgent($agentId, $agencyId, $search = '', $limit = 10, $offset = 0, $roles = [])
{
    try {
        $pdo = Database::getInstance();
        $query = "
            SELECT u.* 
            FROM users u
            JOIN roles r ON u.role_id = r.id
            LEFT JOIN owners o ON u.id = o.user_id AND o.agent_id = ?
            LEFT JOIN tenants t ON u.id = t.user_id AND t.agent_id = ?
            LEFT JOIN buyers b ON u.id = b.user_id AND b.agent_id = ?
            WHERE u.is_deleted = 0 
            AND u.agency_id = ?
            AND (o.id IS NOT NULL OR t.id IS NOT NULL OR b.id IS NOT NULL)
            AND (u.username LIKE ? OR u.email LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)
        ";
        $params = [$agentId, $agentId, $agentId, $agencyId, "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%"];
        
        if (!empty($roles)) {
            $placeholders = implode(',', array_fill(0, count($roles), '?'));
            $query .= " AND r.name IN ($placeholders)";
            $params = array_merge($params, $roles);
        }
        
        $query .= " ORDER BY u.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $users = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $users[] = self::fromData($data);
        }
        return $users;
    } catch (PDOException $e) {
        throw new PDOException("Erreur lors de la récupération des utilisateurs par agent : " . $e->getMessage());
    }
}

/**
 * Compte les utilisateurs créés par un agent dans une agence avec rôles spécifiques.
 *
 * @param int $agentId ID de l'agent
 * @param int $agencyId ID de l'agence
 * @param string $search Terme de recherche
 * @param array $roles Liste des noms de rôles autorisés
 * @return int Nombre total d'utilisateurs
 */
public static function countByAgent($agentId, $agencyId, $search = '', $roles = [])
{
    try {
        $pdo = Database::getInstance();
        $query = "
            SELECT COUNT(*) 
            FROM users u
            JOIN roles r ON u.role_id = r.id
            LEFT JOIN owners o ON u.id = o.user_id AND o.agent_id = ?
            LEFT JOIN tenants t ON u.id = t.user_id AND t.agent_id = ?
            LEFT JOIN buyers b ON u.id = b.user_id AND b.agent_id = ?
            WHERE u.is_deleted = 0 
            AND u.agency_id = ?
            AND (o.id IS NOT NULL OR t.id IS NOT NULL OR b.id IS NOT NULL)
            AND (u.username LIKE ? OR u.email LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)
        ";
        $params = [$agentId, $agentId, $agentId, $agencyId, "%{$search}%", "%{$search}%", "%{$search}%", "%{$search}%"];
        
        if (!empty($roles)) {
            $placeholders = implode(',', array_fill(0, count($roles), '?'));
            $query .= " AND r.name IN ($placeholders)";
            $params = array_merge($params, $roles);
        }

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    } catch (PDOException $e) {
        throw new PDOException("Erreur lors du comptage des utilisateurs par agent : " . $e->getMessage());
    }
}

/**
 * Vérifie si un email existe déjà, en excluant un ID spécifique.
 *
 * @param string $email Email à vérifier
 * @param int|null $excludeId ID à exclure (pour mise à jour)
 * @return bool
 */
public static function emailExists($email, $excludeId = null)
{
    try {
        $pdo = Database::getInstance();
        $query = "SELECT COUNT(*) FROM users WHERE email = ? AND is_deleted = 0";
        $params = [$email];
        
        if ($excludeId !== null) {
            $query .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
        throw new PDOException("Erreur lors de la vérification de l'email : " . $e->getMessage());
    }
}

/**
 * Effectue une suppression logique d'un utilisateur.
 *
 * @param int $id ID de l'utilisateur
 * @return bool
 */
public static function softDelete($id)
{
    try {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('UPDATE users SET is_deleted = 1, updated_at = NOW() WHERE id = ?');
        return $stmt->execute([$id]);
    } catch (PDOException $e) {
        throw new PDOException("Erreur lors de la suppression logique de l’utilisateur : " . $e->getMessage());
    }
}
}