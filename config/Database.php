<?php
declare(strict_types=1);

/**
 * config/Database.php
 * Singleton PDO sécurisé pour la base de données AgriPulse (MySQL / UTF-8).
 */
class Database
{
    private static ?PDO $instance = null;

    private const HOST = '127.0.0.1';
    private const DB_NAME = 'agripulse';
    private const USERNAME = 'root';
    private const PASSWORD = '';

    private function __construct() {}

    public static function getConnexion(): PDO
    {
        if (self::$instance === null) {
            $dsn = 'mysql:host=' . self::HOST . ';dbname=' . self::DB_NAME . ';charset=utf8mb4';
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            self::$instance = new PDO($dsn, self::USERNAME, self::PASSWORD, $options);
        }
        return self::$instance;
    }
}