<?php
namespace App\Utils;

class Helpers
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function sanitize(string $data): string
    {
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }

    public function csrf_token(string $form_id = 'default'): string
    {
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_tokens'][$form_id] = $token;
        $logger = new Logger();
        $logger->error("CSRF Token généré pour $form_id: $token");
        return $token;
    }

    public function csrf_verify(string $token, string $form_id = 'default'): bool
    {
        $session_token = $_SESSION['csrf_tokens'][$form_id] ?? null;
        $logger = new Logger();
        $logger->error("CSRF Verify - Form: '$form_id', Token envoyé: '$token', Token session: '$session_token'");
        if (isset($session_token) && hash_equals($session_token, $token)) {
            unset($_SESSION['csrf_tokens'][$form_id]); // Supprimer après vérification
            return true;
        }
        return false;
    }

    public function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }

    public function url(string $name, array $params = []): string
    {
        $routes = require dirname(__DIR__, 2) . '/routes.php';
        foreach ($routes as $pattern => $route) {
            if ($route['name'] === $name) {
                $url = $pattern;
                foreach ($params as $key => $value) {
                    $url = str_replace(":$key", $value, $url);
                }
                return '/' . ltrim($url, '/');
            }
        }
        return '/';
    }
}
?>