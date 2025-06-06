<?php

namespace App\Controllers\Public;

use App\Utils\Auth;
use App\Utils\Logger;
use App\Utils\Helpers;
use App\Utils\Flash;

class ErrorController
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

    public function forbidden()
    {
        // Journaliser l'accès non autorisé
        $user_id = $this->auth->id() ?? 'guest';
        $this->logger->error("Accès interdit à l'URI: {uri}", [
            'uri' => $_SERVER['REQUEST_URI'],
            'user_id' => $user_id,
            'role' => $this->auth->user()['role'] ?? 'guest'
        ]);

        // Définir le message flash si aucun n'est défini
        if (!$this->flash->has('error')) {
            $this->flash->flash('error', 'Accès interdit : Vous n\'avez pas les permissions nécessaires.');
        }

        // Définir les variables pour la vue
        $error_code = 403;
        $error_message = $this->flash->get('error') ?: 'Accès non autorisé';
        $content_view = 'errors/403.php';

        // Charger la vue avec le layout public
        http_response_code(403);
        require_once dirname(__DIR__, 2) . '/Views/layouts/public_layout.php';
    }
}