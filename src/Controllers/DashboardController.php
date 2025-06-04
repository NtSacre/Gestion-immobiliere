<?php
namespace App\Controllers;

class DashboardController
{
    public function index()
    {
        // Définir la vue de contenu
        $content_view = 'admin/dashboard/index.php';
        
        // Charger le layout qui inclut la vue de contenu
        require_once dirname(__DIR__) . '/Views/layouts/admin_layout.php';
    }
}
?>