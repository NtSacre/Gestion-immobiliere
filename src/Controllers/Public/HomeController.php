<?php
namespace App\Controllers\Public;

class HomeController {
    public function index() {
        // Charger la vue de la landing page
        require __DIR__ . '/../../Views/home/index.php';
    }
}
?>