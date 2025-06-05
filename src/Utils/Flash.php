<?php
namespace App\Utils;

class Flash
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function flash(string $key, string $value): void
    {
        $_SESSION['flash'][$key] = $value;
    }

    public function get(string $key): ?string
    {
        if (isset($_SESSION['flash'][$key])) {
            $message = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $message;
        }
        return null;
    }

    public function has(string $key): bool
    {
        return isset($_SESSION['flash'][$key]);
    }
}
?>