<?php
namespace App\Models;

use App\Config\Database;
use PDO;
use PDOException;

/**
 * Modèle pour la table audit_log
 * ImmoApp (L2GIB, 2024-2025)
 * Représente un journal d’audit pour les actions sur les données
 */
class AuditLog
{
    private $pdo;
    protected $id;
    protected $user_id;
    protected $action;
    protected $table_name;
    protected $record_id;
    protected $old_data;
    protected $new_data;
    protected $created_at;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    // Getters
    public function getId() { return $this->id; }
    public function getUserId() { return $this->user_id; }
    public function getAction() { return $this->action; }
    public function getTableName() { return $this->table_name; }
    public function getRecordId() { return $this->record_id; }
    public function getOldData() { return $this->old_data; }
    public function getNewData() { return $this->new_data; }
    public function getCreatedAt() { return $this->created_at; }

    // Protected setters
    protected function setId($id) { $this->id = $id; }
    protected function setUserId($user_id) { $this->user_id = $user_id; }
    protected function setAction($action) { $this->action = $action; }
    protected function setTableName($table_name) { $this->table_name = $table_name; }
    protected function setRecordId($record_id) { $this->record_id = $record_id; }
    protected function setOldData($old_data) { $this->old_data = $old_data; }
    protected function setNewData($new_data) { $this->new_data = $new_data; }
    protected function setCreatedAt($created_at) { $this->created_at = $created_at; }

    /**
     * Crée un objet AuditLog à partir des données de la base
     * @param array $data
     * @return AuditLog
     */
    protected static function fromData(array $data): AuditLog
    {
        $log = new self();
        $log->setId($data['id']);
        $log->setUserId($data['user_id'] ?? null);
        $log->setAction($data['action']);
        $log->setTableName($data['table_name']);
        $log->setRecordId($data['record_id']);
        $log->setOldData($data['old_data'] ? json_decode($data['old_data'], true) : null);
        $log->setNewData($data['new_data'] ? json_decode($data['new_data'], true) : null);
        $log->setCreatedAt($data['created_at']);
        return $log;
    }

    /**
     * Trouve une entrée de journal par ID
     * @param int $id
     * @return AuditLog|null
     */
    public static function find($id)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT * FROM audit_log WHERE id = ?');
            $stmt->execute([$id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? self::fromData($data) : null;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche de l’entrée de journal : " . $e->getMessage());
        }
    }

    /**
     * Alias pour find
     * @param int $id
     * @return AuditLog|null
     */
    public static function findById($id)
    {
        return self::find($id);
    }

    /**
     * Récupère toutes les entrées de journal
     * @return array
     */
    public static function get()
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->query('SELECT * FROM audit_log ORDER BY created_at DESC');
            $logs = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $logs[] = self::fromData($data);
            }
            return $logs;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération des entrées de journal : " . $e->getMessage());
        }
    }

    /**
     * Récupère la première entrée de journal (par date de création)
     * @return AuditLog|null
     */
    public static function first()
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->query('SELECT * FROM audit_log ORDER BY created_at ASC LIMIT 1');
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data ? self::fromData($data) : null;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération de la première entrée de journal : " . $e->getMessage());
        }
    }

    /**
     * Crée une nouvelle entrée de journal
     * @param array $data
     * @return AuditLog
     */
    public static function create(array $data)
    {
        try {
            $pdo = Database::getInstance();
            // Vérifier l’existence de l’utilisateur si fourni
            if (!empty($data['user_id']) && !User::find($data['user_id'])) {
                throw new PDOException("User ID invalide");
            }
            // Validation basique de action
            $validActions = ['create', 'update', 'delete']; // Exemple, à ajuster
            if (!in_array($data['action'], $validActions)) {
                throw new PDOException("Action invalide");
            }
            $stmt = $pdo->prepare('
                INSERT INTO audit_log (
                    user_id, action, table_name, record_id, old_data, new_data, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, NOW())
            ');
            $stmt->execute([
                $data['user_id'] ?? null,
                $data['action'],
                $data['table_name'],
                $data['record_id'],
                $data['old_data'] ? json_encode($data['old_data']) : null,
                $data['new_data'] ? json_encode($data['new_data']) : null
            ]);
            $id = $pdo->lastInsertId();
            return self::find($id);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la création de l’entrée de journal : " . $e->getMessage());
        }
    }

    /**
     * Met à jour une entrée de journal
     * @param int $id
     * @param array $data
     * @return AuditLog|null
     */
    public static function update($id, array $data)
    {
        try {
            $pdo = Database::getInstance();
            $existing = self::find($id);
            if (!$existing) {
                throw new PDOException("Entrée de journal introuvable");
            }
            // Vérifier l’existence de l’utilisateur si modifié
            if (!empty($data['user_id']) && !User::find($data['user_id'])) {
                throw new PDOException("User ID invalide");
            }
            // Validation basique de action si modifié
            if (!empty($data['action'])) {
                $validActions = ['create', 'update', 'delete']; // Exemple, à ajuster
                if (!in_array($data['action'], $validActions)) {
                    throw new PDOException("Action invalide");
                }
            }
            $stmt = $pdo->prepare('
                UPDATE audit_log
                SET user_id = ?, action = ?, table_name = ?, record_id = ?, old_data = ?, new_data = ?
                WHERE id = ?
            ');
            $stmt->execute([
                $data['user_id'] ?? $existing->getUserId(),
                $data['action'] ?? $existing->getAction(),
                $data['table_name'] ?? $existing->getTableName(),
                $data['record_id'] ?? $existing->getRecordId(),
                isset($data['old_data']) ? json_encode($data['old_data']) : ($existing->getOldData() ? json_encode($existing->getOldData()) : null),
                isset($data['new_data']) ? json_encode($data['new_data']) : ($existing->getNewData() ? json_encode($existing->getNewData()) : null),
                $id
            ]);
            return self::find($id);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la mise à jour de l’entrée de journal : " . $e->getMessage());
        }
    }

    /**
     * Supprime une entrée de journal
     * @param int $id
     * @return bool
     */
    public static function delete($id)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('DELETE FROM audit_log WHERE id = ?');
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la suppression de l’entrée de journal : " . $e->getMessage());
        }
    }

    /**
     * Trouve les entrées de journal par user_id
     * @param int $userId
     * @return array
     */
    public static function findByUserId($userId)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT * FROM audit_log WHERE user_id = ? ORDER BY created_at DESC');
            $stmt->execute([$userId]);
            $logs = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $logs[] = self::fromData($data);
            }
            return $logs;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche des entrées de journal par user_id : " . $e->getMessage());
        }
    }

    /**
     * Trouve les entrées de journal par table_name et record_id
     * @param string $tableName
     * @param int $recordId
     * @return array
     */
    public static function findByTableAndRecord($tableName, $recordId)
    {
        try {
            $pdo = Database::getInstance();
            $stmt = $pdo->prepare('SELECT * FROM audit_log WHERE table_name = ? AND record_id = ? ORDER BY created_at DESC');
            $stmt->execute([$tableName, $recordId]);
            $logs = [];
            while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $logs[] = self::fromData($data);
            }
            return $logs;
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la recherche des entrées de journal par table et record_id : " . $e->getMessage());
        }
    }

    /**
     * Récupère l’utilisateur associé
     * @return User|null
     */
    public function user()
    {
        return User::find($this->user_id);
    }

/**
 * Récupère les activités récentes (3 dernières, triées par date)
 * @param int $limit Nombre maximum d'activités à retourner
 * @return array Liste des activités récentes
 */
public static function getRecentActivities($limit = 3)
{
    try {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('
            SELECT a.*, u.first_name, u.last_name
            FROM audit_log a
            LEFT JOIN users u ON a.user_id = u.id
            WHERE a.action IN ("create", "add")
            AND a.table_name IN ("buildings", "leases", "tenants")
            AND a.is_deleted = 0
            ORDER BY a.created_at DESC
            LIMIT ?
        ');
        $stmt->execute([$limit]);
        $logs = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $log = [
                'id' => $row['id'],
                'type' => $row['table_name'],
                'description' => '',
                'details' => '',
                'timestamp' => $row['created_at'],
                'icon_class' => 'bg-gray-100',
                'icon_text_class' => 'text-gray-600',
                'icon' => '📋'
            ];
            if ($row['table_name'] === 'buildings') {
                $log['description'] = 'Nouveau bâtiment ajouté';
                $log['details'] = 'Bâtiment ID: ' . $row['record_id'];
                $log['icon_class'] = 'bg-green-100';
                $log['icon_text_class'] = 'text-green-600';
                $log['icon'] = '🏢';
            } elseif ($row['table_name'] === 'leases') {
                $log['description'] = 'Contrat signé';
                $log['details'] = 'Bail ID: ' . $row['record_id'];
                $log['icon_class'] = 'bg-blue-100';
                $log['icon_text_class'] = 'text-blue-600';
                $log['icon'] = '📄';
            } elseif ($row['table_name'] === 'tenants') {
                $log['description'] = 'Locataire ajouté';
                $log['details'] = $row['first_name'] . ' ' . $row['last_name'];
                $log['icon_class'] = 'bg-purple-100';
                $log['icon_text_class'] = 'text-purple-600';
                $log['icon'] = '👤';
            }
            $logs[] = $log;
        }
        return $logs;
    } catch (PDOException $e) {
        throw new PDOException("Erreur lors de la récupération des activités récentes : " . $e->getMessage());
    }
}

    /**
     * Récupère les 3 dernières activités de l'audit log pour une agence spécifique.
     *
     * @param int $agency_id ID de l'agence
     * @return array Tableau associatif des 3 dernières activités
     */
    public static function getRecentActivitiesByAgency($agency_id)
    {
        try {
           
        
        $pdo = Database::getInstance();
        $query = "
            SELECT al.action, al.table_name, al.created_at
            FROM audit_log al
            LEFT JOIN leases l ON al.table_name = 'leases' AND al.record_id = l.id
            LEFT JOIN apartments a ON al.table_name = 'apartments' AND al.record_id = a.id OR l.apartment_id = a.id
            LEFT JOIN buildings b ON al.table_name = 'buildings' AND al.record_id = b.id OR a.building_id = b.id
            WHERE b.agency_id = ?
            ORDER BY al.created_at DESC
            LIMIT 3
        ";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$agency_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
        throw new PDOException("Erreur lors de la récupèration des 3 dernières activités de l'audit log pour une agence spécifique : " . $e->getMessage());

        }
    }

    /**
     * Récupère les tâches urgentes (baux en attente) pour une agence spécifique.
     *
     * @param int $agency_id ID de l'agence
     * @return array Tableau associatif avec le nombre de baux en attente
     */
    public static function getUrgentTasksByAgency($agency_id)
    {
        try {

                $pdo = Database::getInstance();
                $query = "
                    SELECT COUNT(*) as pendingLeases
                    FROM leases l
                    JOIN apartments a ON l.apartment_id = a.id
                    JOIN buildings b ON a.building_id = b.id
                    WHERE l.is_active = 0 AND b.agency_id = ?
                ";
                $stmt = $pdo->prepare($query);
                $stmt->execute([$agency_id]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                return ['pendingLeases' => (int) $result['pendingLeases']];

        } catch (PDOException $e) {
        throw new PDOException("Erreur lors de la récupèration des tâches urgentes (baux en attente) pour une agence spécifique : " . $e->getMessage());

        }
    }

        /**
     * Récupère les tâches urgentes (baux en attente) pour un agent spécifique dans une agence.
     *
     * @param int $agent_id ID de l'agent
     * @param int $agency_id ID de l'agence
     * @return array Tableau associatif avec le nombre de baux en attente
     */
    public static function getUrgentTasksByAgent($agent_id, $agency_id)
    {
        try {
            $pdo = Database::getInstance();
            $query = "
                SELECT COUNT(*) as pendingLeases
                FROM leases l
                JOIN apartments a ON l.apartment_id = a.id
                JOIN buildings b ON a.building_id = b.id
                WHERE l.is_active = 0 AND l.agent_id = ? AND b.agency_id = ?
            ";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$agent_id, $agency_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return ['pendingLeases' => (int) $result['pendingLeases']];
        } catch (PDOException $e) {
            throw new PDOException("Erreur lors de la récupération des tâches urgentes pour un agent spécifique : " . $e->getMessage());
        }
    }

/**
 * Récupère les tâches urgentes (contrats à valider uniquement)
 * @return array
 */
public static function getUrgentTasks()
{
    try {
        $pdo = Database::getInstance();
        // Contrats en attente (suppose is_active = 0 pour "en attente")
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM leases WHERE is_active = 0 AND is_deleted = 0');
        $stmt->execute();
        $pendingLeases = (int) $stmt->fetchColumn();

        return [
            'pendingLeases' => $pendingLeases,
            'pendingInspections' => 0 // Pas d'inspections, car inspection_date n'existe pas
        ];
    } catch (PDOException $e) {
        throw new PDOException("Erreur lors de la récupération des tâches urgentes : " . $e->getMessage());
    }
}
}