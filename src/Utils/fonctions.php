<?php
namespace App\Utils;

/**
 * Nettoie une entrée utilisateur pour prévenir les attaques XSS.
 * @param string $data Donnée à nettoyer
 * @return string Donnée nettoyée
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

/**
 * Génère un jeton CSRF pour sécuriser les formulaires.
 * @return string Jeton CSRF
 */
function generateCsrfToken() {
    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_token'] = $token;
    return $token;
}

/**
 * Vérifie si un jeton CSRF est valide.
 * @param string $token Jeton à vérifier
 * @return bool True si valide, false sinon
 */
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Vérifie si un utilisateur est authentifié.
 * @return bool True si authentifié, false sinon
 */
function isAuthenticated() {
    return isset($_SESSION['user_id']);
}

/**
 * Vérifie si l'utilisateur a un rôle spécifique.
 * @param string $role Rôle à vérifier (ex. 'admin', 'agent')
 * @return bool True si l'utilisateur a le rôle, false sinon
 */
function hasRole($role) {
    return isAuthenticated() && isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

/**
 * Redirige vers une URL donnée.
 * @param string $url URL de redirection
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Affiche un message flash (succès, erreur, etc.) et le supprime après affichage.
 * @param string $key Clé du message (ex. 'success', 'error')
 * @return string|null Message flash ou null si aucun
 */
function flash($key) {
    if (isset($_SESSION['flash'][$key])) {
        $message = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return $message;
    }
    return null;
}

/**
 * Stocke un message flash dans la session.
 * @param string $key Clé du message
 * @param string $message Message à stocker
 */
function setFlash($key, $message) {
    $_SESSION['flash'][$key] = $message;
}
?>