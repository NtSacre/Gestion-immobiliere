<?php
namespace App\Utils;

class Helpers
{
    private $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function generateCsrfToken(string $action): string
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['csrf_tokens'])) {
            $_SESSION['csrf_tokens'] = [];
        }
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_tokens'][$action] = $token;
        return $token;
    }

    public function verifyCsrfToken(string $action, string $token): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['csrf_tokens'][$action]) || !hash_equals($_SESSION['csrf_tokens'][$action], $token)) {
            return false;
        }
        // Optionnel : Supprimer le jeton après vérification pour éviter réutilisation
        unset($_SESSION['csrf_tokens'][$action]);
        return true;
    }

    public function redirect($url)
    {
        header("Location: $url");
        exit;
    }
}