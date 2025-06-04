<?php
namespace App\Config;

class Database {
    private static $instance = null;
    private $pdo;

    // Constantes pour la configuration
    private const DB_HOST = 'localhost';
    private const DB_NAME = 'immo_db';
    private const DB_USER = 'root';
    private const DB_PASS = '';

    private function __construct() {
        try {
            $this->pdo = new \PDO(
                "mysql:host=" . self::DB_HOST . ";dbname=" . self::DB_NAME,
                self::DB_USER,
                self::DB_PASS,
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                    \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                    \PDO::ATTR_EMULATE_PREPARES => false // Sécurité renforcée
                ]
            );
        } catch (\PDOException $e) {
            die("Erreur de connexion : " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->pdo;
    }

    // Optionnel : méthode pour fermer la connexion
    public function closeConnection() {
        $this->pdo = null;
    }
}
?>