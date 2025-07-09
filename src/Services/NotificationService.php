<?php
namespace App\Services;

use App\Config\Database;
use PDO;

class NotificationService
{
    protected $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance();
    }

    public function create($userId, $agencyId, $type, $title, $message, $link = null)
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO notifications (user_id, agency_id, type, title, message, link)
            VALUES (:user_id, :agency_id, :type, :title, :message, :link)
        ");
        $stmt->execute([
            ':user_id' => $userId,
            ':agency_id' => $agencyId,
            ':type' => $type,
            ':title' => $title,
            ':message' => $message,
            ':link' => $link
        ]);
    }

    public function getUnreadByUser($userId)
    {
        $stmt = $this->pdo->prepare("
            SELECT * FROM notifications
            WHERE user_id = :user_id AND is_read = 0
            ORDER BY created_at DESC
        ");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function markAsRead($id)
    {
        $stmt = $this->pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }
}
