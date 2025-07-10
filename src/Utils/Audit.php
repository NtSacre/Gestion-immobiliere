<?php
namespace App\Utils;

use App\Models\AuditLog;

class Audit
{
    /**
     * Enregistre une action dans le journal d'audit.
     *
     * @param string $action 'create', 'update', 'delete', 'view'
     * @param string $table Le nom de la table concernée
     * @param int $recordId L'identifiant de l'enregistrement concerné
     * @param array|null $oldData Les anciennes données (avant l'action)
     * @param array|null $newData Les nouvelles données (après l'action)
     */
    public static function log(string $action, string $table, int $recordId, int $agencyId, ?array $oldData = null, ?array $newData = null): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userId = $_SESSION['user_id'] ?? null;
        

        try {
            AuditLog::create([
                'user_id'    => $userId,
                'agency_id'  => $agencyId,
                'action'     => $action,
                'table_name' => $table,
                'record_id'  => $recordId,
                'old_data'   => $oldData,
                'new_data'   => $newData
            ]);
        } catch (\Exception $e) {
            error_log("Erreur Audit Log: " . $e->getMessage());
        }
    }
}
