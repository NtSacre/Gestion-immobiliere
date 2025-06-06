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
        // Vérifier d'abord si c'est une correspondance exacte (pour les routes sans paramètres)
        if ($route_pattern === $uri && $route['method'] === $method) {
            $matched_route = $route;
            $params = [];
            $logger->info("Route exacte trouvée: '$route_pattern' pour URI: '$uri'");
            break;
        }
        
        // Si la route contient des paramètres (:param)
        if (strpos($route_pattern, ':') !== false) {
            // Échapper les caractères spéciaux pour la regex, en gardant les tirets et slashes
            $pattern = str_replace(['/', '-'], ['\/', '\-'], $route_pattern);
            
            // Remplacer les paramètres :param par des groupes de capture
           $pattern = preg_replace('/:([a-zA-Z_][a-zA-Z0-9_]*)/', '([^\/]+)', $pattern);

            
            // Ancrer le pattern
            $pattern = '/^' . $pattern . '$/';
            
            try {
                if (preg_match($pattern, $uri, $matches) && $route['method'] === $method) {
                    $matched_route = $route;
                    array_shift($matches); // Supprimer la correspondance complète
                    $params = $matches; // Capturer les paramètres
                    $logger->info("Route avec paramètres trouvée: '$route_pattern' pour URI: '$uri'", [
                        'pattern' => $pattern,
                        'params' => $params
                    ]);
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
    }

    if (!$matched_route) {
        $logger->error("Aucune route trouvée pour URI: '$uri' avec méthode: $method");
        throw new Exception('Route non trouvée ou méthode non autorisée', 404);
    }

    // Middleware : Vérifier les restrictions d'accès
    if (isset($matched_route['allowed_roles'])) {
        // Autoriser les routes publiques sans redirection
        if (!in_array($uri, ['auth/login', 'auth/register', 'login', 'register', '403'])) {
            $auth->restrict($matched_route['allowed_roles']);
            $logger->access($auth->id(), $matched_route['name'], 'success');
        }
    } else {
        throw new Exception('Rôles non définis pour la route ' . $uri, 500);
    }

    // Middleware : Vérifier CSRF pour les requêtes POST
    if ($method === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        // Utiliser le nom de la route comme form_id
        $form_id = $matched_route['name'];
        if (!$helpers->csrf_verify($token, $form_id)) {
            $flash->flash('error', 'Jeton CSRF invalide.');
            $logger->error("Jeton CSRF invalide pour '$uri' (form_id: $form_id)", ['uri' => $uri, 'token' => $token]);
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

    // Déterminer le code d'erreur
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
?>