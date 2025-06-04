<?php
// index.php
// Point d'entrée principal de l'application

require_once __DIR__ . '/../vendor/autoload.php';
$routes = require_once __DIR__ . '/../routes.php';

// Démarrage de la session
session_start();

// Récupérer l'URI et la méthode HTTP
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = trim(str_replace(dirname($_SERVER['SCRIPT_NAME']), '', $uri), '/');
$uri = $uri ?: ''; // Route par défaut si vide
$method = $_SERVER['REQUEST_METHOD'];

try {
    $matched_route = null;
    $params = [];

    // Parcourir les routes pour trouver une correspondance
    foreach ($routes as $route_pattern => $route) {
        // Remplacer :id par une regex pour capturer les paramètres numériques
        $pattern = preg_replace('/:[a-zA-Z]+/', '([0-9]+)', $route_pattern);
        $pattern = '^' . str_replace('/', '\/', $pattern) . '$';

        if (preg_match("/$pattern/", $uri, $matches) && $route['method'] === $method) {
            $matched_route = $route;
            array_shift($matches); // Supprimer la correspondance complète
            $params = $matches; // Capturer les paramètres (ex. :id)
            break;
        }
    }

    if (!$matched_route) {
        throw new Exception('Route non trouvée ou méthode non autorisée', 404);
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

    // Instancier le contrôleur
    $controller = new $controller_class();

    // Appeler l'action avec les paramètres
    call_user_func_array([$controller, $action], $params);

} catch (Exception $e) {
    $code = $e->getCode() === 404 ? 404 : 500;
    http_response_code($code);
    $content_view = 'errors/' . ($code === 404 ? '404.php' : '500.php');
    require_once dirname(__DIR__) . '/src/Views/layouts/public_layout.php';
}
?>