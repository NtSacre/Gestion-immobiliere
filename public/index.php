<?php

// Activer le débogage en développement seulement
if (getenv('APP_ENV') !== 'production') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

require_once __DIR__ . '/../vendor/autoload.php';
use App\Utils\Auth;
use App\Utils\Logger;
use App\Utils\Helpers;
use App\Utils\Flash;

$routes = require_once __DIR__ . '/../routes.php';

// Démarrage de la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Instanciation des utilitaires
$auth = new Auth();
$logger = new Logger();
$helpers = new Helpers();
$flash = new Flash();

// Journalisation de la requête
$logger->info("Démarrage de la requête pour URI: {uri}", ['uri' => $_SERVER['REQUEST_URI'], 'method' => $_SERVER['REQUEST_METHOD']]);

// Récupérer l'URI et la méthode HTTP
$request_uri = $_SERVER['REQUEST_URI'];
$script_name = $_SERVER['SCRIPT_NAME'];

// Nettoyer l'URI
$base_path = dirname($script_name);
if ($base_path === '/') {
    $base_path = '';
}

$uri = parse_url($request_uri, PHP_URL_PATH);
$uri = substr($uri, strlen($base_path));
$uri = trim($uri, '/');

// Route par défaut si aucune
if ($uri === '') {
    $uri = '';
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    $matched_route = null;
    $params = [];

    // Parcourir les routes pour trouver une correspondance
    foreach ($routes as $route_pattern => $route) {
        // Échapper les caractères spéciaux pour la regex
        $pattern = preg_quote($route_pattern, '/');
        
        // Remplacer les paramètres :param par des groupes de capture
        $pattern = preg_replace('/\\\\:([a-zA-Z_][a-zA-Z0-9_]*)/', '([^/]+)', $pattern);
        
        // Valider le motif pour éviter les caractères problématiques
        if (preg_match('/[\[\]\{\}\(\)\*\+]/', $pattern)) {
            $logger->error("Motif regex invalide pour la route: '$route_pattern'", ['pattern' => $pattern]);
            continue;
        }
        
        // Ancrer le pattern
        $pattern = '/^' . $pattern . '$/';
        
        try {
            if (preg_match($pattern, $uri, $matches) && $route['method'] === $method) {
                $matched_route = $route;
                array_shift($matches); // Supprimer la correspondance complète
                $params = $matches; // Capturer les paramètres
                $logger->info("Route trouvée: '$route_pattern' pour URI: '$uri'", ['params' => $params]);
                break;
            }
        } catch (Exception $e) {
            $logger->error("Erreur preg_match pour la route: '$route_pattern'", [
                'pattern' => $pattern,
                'error' => $e->getMessage()
            ]);
            continue;
        }
    }

    if (!$matched_route) {
        $logger->error("Aucune route trouvée pour URI: '$uri' avec méthode: $method");
        throw new Exception('Route non trouvée ou méthode non autorisée', 404);
    }

    // Middleware : Vérifier les restrictions d'accès
    if (isset($matched_route['allowed_roles']) && !in_array($uri, ['auth/login', 'auth/register', 'login', 'register', '403'])) {
        if (in_array('guest', $matched_route['allowed_roles']) && $auth->check() && $uri !== '') {
            // Rediriger les utilisateurs connectés vers le dashboard, sauf pour la route '/'
            $logger->info("Utilisateur connecté a tenté d'accéder à une route guest: '$uri'", [
                'user_id' => $auth->id(),
                'role' => $auth->user()['role'] ?? 'guest'
            ]);
            $flash->flash('info', 'Vous êtes déjà connecté.');
            $helpers->redirect('/dashboard');
        }
        $auth->restrict($matched_route['allowed_roles']);
        $logger->access($auth->id(), $matched_route['name'], 'success');
    } elseif (!isset($matched_route['allowed_roles'])) {
        throw new Exception('Rôles non définis pour la route ' . $uri, 500);
    }

    // Middleware : Vérifier CSRF pour les requêtes POST
    if ($method === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (!$helpers->csrf_verify($token)) {
            $flash->flash('error', 'Jeton CSRF invalide.');
            $logger->error('Jeton CSRF invalide pour ' . $uri, ['uri' => $uri]);
            $helpers->redirect('/403');
        }
    }

    // Vérifier si le contrôleur existe
    $controller_class = $matched_route['controller'];
    if (!class_exists($controller_class)) {
        throw new Exception("Contrôleur $controller_class introuvable", 500);
    }

    // Vérifier si l'action existe
    $action = $matched_route['action'];
    if (!method_exists($controller_class, $action)) {
        throw new Exception("Action $action introuvable dans $controller_class", 500);
    }

    // Instancier le contrôleur et injecter les dépendances
    $controller = new $controller_class($auth, $logger, $helpers, $flash);

    // Rendre $auth et $flash disponibles dans les vues
    $auth = $controller->auth ?? $auth;
    $flash = $controller->flash ?? $flash;

    // Appeler l'action
    if (!empty($params)) {
        call_user_func_array([$controller, $action], $params);
    } else {
        $controller->$action();
    }

} catch (Exception $e) {
    // Journaliser l'erreur
    $logger->error($e->getMessage(), [
        'exception' => $e,
        'uri' => $uri,
        'method' => $method,
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);

    // Déterminer le code d’erreur
    $code = $e->getCode();
    if (!in_array($code, [404, 403, 500])) {
        $code = 500;
    }

    // Afficher la page d'erreur
    http_response_code($code);
    
    // Définir les variables pour la vue
    $error_message = $e->getMessage();
    $error_code = $code;
    
    $content_view = 'errors/' . ($code === 404 ? '404.php' : ($code === 403 ? '403.php' : '500.php'));
    
    // Vérifier si la vue existe
    $view_path = dirname(__DIR__) . '/src/Views/' . $content_view;
    if (!file_exists($view_path)) {
        echo "<h1>Erreur $code</h1><p>" . htmlspecialchars($error_message) . "</p>";
    } else {
        require_once dirname(__DIR__) . '/src/Views/layouts/public_layout.php';
    }
}