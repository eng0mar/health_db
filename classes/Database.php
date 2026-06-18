<?php
// ============================================
// Database Class - Singleton Pattern
// Instance wa7da bs le kol el app
// ============================================

require_once __DIR__ . '/../config/database.php';

class Database {
    // Static instance - Singleton
    private static ?Database $instance = null;
    private PDO $connection;

    // ============================================
    // Constructor - private 3ashan ma7adsh ye3mel new Database()
    // ============================================
    private function __construct() {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,  // Real prepared statements
        ];

        try {
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Ma te3redsh el error le el user fe production
            die("Database connection failed. Please contact administrator.");
        }
    }

    // ============================================
    // Singleton getInstance - ygeeb nafs el connection
    // ============================================
    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    // ============================================
    // getConnection - returns el PDO object
    // ============================================
    public function getConnection(): PDO {
        return $this->connection;
    }

    // ============================================
    // Prevent cloning w unserialization
    // ============================================
    private function __clone() {}
    public function __wakeup() {
        throw new \Exception("Cannot unserialize a singleton.");
    }
}
