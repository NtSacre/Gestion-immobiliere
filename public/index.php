<?php
// index.php
// Point d'entrée principal de l'application

// Activer le débogage pour voir les erreurs directement
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../vendor/autoload.php';
$routes = require_once __DIR__ . '/../routes.php';

// Démarrage de la session
session_start();

// Fonction pour journaliser les erreurs
function logError(Exception $e) {
    $logDir = __DIR__ . '/../logs';
    $logFile = $logDir . '/app.log';

    // Créer le dossier logs s'il n'existe pas
    if (!is_dir($logDir)) {
        mkdir($logDir, 0755, true);
    }

    // Format du message de log
    $timestamp = date('Y-m-d H:i:s');
    $message = $e->getMessage();
    $code = $e->getCode();
    $file = $e->getFile();
    $line = $e->getLine();
    $trace = $e->getTraceAsString();

    $logMessage = "[$timestamp] ERROR: $message (Code: $code) in $file on line $line\n";
    $logMessage .= "Stack trace:\n$trace\n";
    $logMessage .= "----------------------------------------\n";

    // Écrire dans le fichier de log
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}

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
    // Journaliser l'erreur
    logError($e);

    // Afficher la page d'erreur
    $code = $e->getCode() === 404 ? 404 : 500;
    http_response_code($code);
    $content_view = 'errors/' . ($code === 404 ? '404.php' : '500.php');
    require_once dirname(__DIR__) . '/src/Views/layouts/public_layout.php';
}