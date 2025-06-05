<?php

namespace App\Controllers;

use App\Utils\Auth;
use App\Utils\Logger;
use App\Utils\Helpers;
use App\Utils\Flash;

class DashboardController
{
    protected $auth;
    protected $logger;
    protected $helpers;
    protected $flash;

    public function __construct(Auth $auth, Logger $logger, Helpers $helpers, Flash $flash)
    {
        $this->auth = $auth;
        $this->logger = $logger;
        $this->helpers = $helpers;
        $this->flash = $flash;
    }

    public function index()
    {
        // Vérifier l'authentification
        if (!$this->auth->check()) {
            $this->flash->flash('error', 'Vous devez être connecté pour accéder au tableau de bord.');
            $this->helpers->redirect('/auth/login');
            return;
        }

        // Récupérer les informations de l'utilisateur
        $user = $this->auth->user();
        $role = $user['role'] ?? 'guest';

        // Charger la vue du dashboard
        $content_view = 'admin/dashboard/index.php';
        require_once dirname(__DIR__, 1) . '/Views/layouts/admin_layout.php';
    }
}