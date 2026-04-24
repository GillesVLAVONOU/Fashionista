<?php
// ============================================================
//  config/database.php — PDO Database Connection
//  Modify DB_HOST, DB_NAME, DB_USER, DB_PASS for your env.
// ============================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'fashionista_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

/**
 * Returns a singleton PDO connection.
 */
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // In production, log this instead of displaying
            die(json_encode(['error' => 'Connexion à la base de données impossible.']));
        }
    }
    return $pdo;
}
