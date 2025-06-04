<?php
namespace App\Controllers\Public;

use App\Config\Database;
use App\Utils;
use PDOException;

/**
 * Contrôleur pour l'authentification et l'inscription des agences
 * ImmoApp (L2GIB, 2024-2025)
 */
class AuthController
{
    private $pdo;

    public function __construct()
    {
        // Initialisation de la connexion PDO via Database singleton
        $this->pdo = Database::getInstance();
    }

    /**
     * Affiche le formulaire de connexion
     */
    public function showLogin()
    {
        // Redirige si déjà connecté
        if (Utils\isAuthenticated()) {
            Utils\redirect('/admin/dashboard');
        }

        // Génère un jeton CSRF pour la vue
        $csrf_token = Utils\generateCsrfToken();

        // Charge la vue login.php
        $content_view = 'auth/login.php';
        require_once dirname(__DIR__) . '/../Views/layouts/public_layout.php';
    }

    /**
     * Traite la connexion
     */
    public function login()
    {
        // Vérifie la méthode POST et le jeton CSRF
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Utils\verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            Utils\setFlash('error', 'Requête inappropriée.');
            Utils\redirect('/');
            return;
        }

        // Validation des entrées
        $email = Utils\sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? ''; // Mot de passe non sanitizé (brut pour vérification)

        if (empty($email) || empty($password)) {
            Utils\setFlash('error', 'Vérifiez votre adresse e-mail et/ou votre mot de passe.');
            Utils\redirect('/auth/login');
            return;
        }

        try {
            // Recherche l'utilisateur
            $stmt = $this->pdo->prepare('
                SELECT u.id, u.email, u.password, u.role_id, r.name AS role_name, u.agency_id
                FROM users u
                JOIN roles r ON u.role_id = r.id
                WHERE u.email = ? AND u.is_deleted = FALSE
            ');
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, $user['password'])) {
                Utils\setFlash('error', 'Adresse e-mail ou mot de passe incorrect.');
                Utils\redirect('/auth/login');
                return;
            }

            // Enregistre la session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role_name'];
            $_SESSION['agency_id'] = $user['agency_id'];

            // Redirection selon le rôle
            $redirect = match ($user['role_name']) {
                'admin' => '/admin/dashboard',
                'agent' => '/agent/dashboard',
                default => '/dashboard'
            };
            Utils\redirect($redirect);

        } catch (PDOException $e) {
            Utils\setFlash('error', 'Une erreur est survenue.');
            Utils\redirect('/auth/login');
        }
    }

    /**
     * Affiche le formulaire d'inscription pour une agence
     */
    public function showRegister()
    {
        // Redirige si déjà connecté
        if (Utils\isAuthenticated()) {
            Utils\redirect('/admin/dashboard');
        }

        // Génère un jeton CSRF pour la vue
        $csrf_token = Utils\generateCsrfToken();

        // Charge la vue register.php
        $content_view = 'auth/register.php';
        require_once dirname(__DIR__) . '/../Views/layouts/public_layout.php';
    }

    /**
     * Traite l'inscription d'une agence et crée un utilisateur admin
     */
    public function register()
    {
        // Vérifie la méthode POST et le jeton CSRF
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Utils\verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            Utils\setFlash('error', 'Requête inappropriée.');
            Utils\redirect('/auth/register');
            return;
        }

        // Validation des entrées
        $agency_name = Utils\sanitize($_POST['agency_name'] ?? '');
        $address = Utils\sanitize($_POST['address'] ?? '');
        $agency_phone = Utils\sanitize($_POST['agency_phone'] ?? '');
        $siret = Utils\sanitize($_POST['siret'] ?? '');
        $username = Utils\sanitize($_POST['username'] ?? '');
        $email = Utils\sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? ''; // Mot de passe non sanitizé
        $first_name = Utils\sanitize($_POST['first_name'] ?? '');
        $last_name = Utils\sanitize($_POST['last_name'] ?? '');
        $phone = Utils\sanitize($_POST['phone'] ?? '');

        if (empty($agency_name) || empty($username) || empty($email) || empty($password) || empty($first_name) || empty($last_name)) {
            Utils\setFlash('error', 'Veuillez remplir tous les champs obligatoires.');
            Utils\redirect('/auth/register');
            return;
        }

        // Validation du mot de passe (minimum 8 caractères)
        if (strlen($password) < 8) {
            Utils\setFlash('error', 'Le mot de passe doit contenir au moins 8 caractères.');
            Utils\redirect('/auth/register');
            return;
        }

        try {
            // Vérifie si l'email ou le username existe déjà
            $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM users WHERE email = ? OR username = ?');
            $stmt->execute([$email, $username]);
            if ($stmt->fetchColumn() > 0) {
                Utils\setFlash('error', 'Cet e-mail ou ce nom d’utilisateur est déjà utilisé.');
                Utils\redirect('/auth/register');
                return;
            }

            // Vérifie si l'email de l'agence existe
            $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM agencies WHERE email = ?');
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                Utils\setFlash('error', 'Cet e-mail est déjà associé à une agence.');
                Utils\redirect('/auth/register');
                return;
            }

            // Vérifie si le SIRET existe (si fourni)
            if ($siret) {
                $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM agencies WHERE siret = ?');
                $stmt->execute([$siret]);
                if ($stmt->fetchColumn() > 0) {
                    Utils\setFlash('error', 'Ce SIRET est déjà utilisé.');
                    Utils\redirect('/auth/register');
                    return;
                }
            }

            // Récupère l'ID du rôle admin
            $stmt = $this->pdo->prepare('SELECT id FROM roles WHERE name = ?');
            $stmt->execute(['admin']);
            $role = $stmt->fetch();
            if (!$role) {
                Utils\setFlash('error', 'Rôle admin non trouvé.');
                Utils\redirect('/auth/register');
                return;
            }

            // Démarre une transaction
            $this->pdo->beginTransaction();

            // Insère l'agence
            $stmt = $this->pdo->prepare('
                INSERT INTO agencies (name, address, phone, email, siret, created_at, is_deleted)
                VALUES (?, ?, ?, ?, ?, NOW(), FALSE)
            ');
            $stmt->execute([$agency_name, $address ?: null, $agency_phone ?: null, $email, $siret ?: null]);
            $agency_id = $this->pdo->lastInsertId();

            // Insère l'utilisateur admin
            $password_hashed = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $this->pdo->prepare('
                INSERT INTO users (username, email, password, role_id, agency_id, first_name, last_name, phone, created_at, is_deleted)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), FALSE)
            ');
            $stmt->execute([$username, $email, $password_hashed, $role['id'], $agency_id, $first_name, $last_name, $phone ?: null]);
            $user_id = $this->pdo->lastInsertId();

            // Valide la transaction
            $this->pdo->commit();

            // Connexion automatique
            $_SESSION['user_id'] = $user_id;
            $_SESSION['role'] = 'admin';
            $_SESSION['agency_id'] = $agency_id;

            Utils\setFlash('success', 'Inscription de l’agence réussie !');
            Utils\redirect('/admin/dashboard');

        } catch (PDOException $e) {
            $this->pdo->rollBack();
            Utils\setFlash('error', 'Une erreur est survenue.');
            Utils\redirect('/auth/register');
        }
    }

    /**
     * Déconnexion de l'utilisateur
     */
    public function logout()
    {
        // Détruit la session
        session_unset();
        session_destroy();
        Utils\setFlash('success', 'Déconnexion réussie.');
        Utils\redirect('/auth/login');
    }
}