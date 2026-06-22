<?php
// ============================================================
//  Forest Trove — PDO Database Helper
// ============================================================

require_once __DIR__ . '/config.php';

function get_db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', DB_HOST, DB_NAME, DB_CHARSET);
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // In production, log this and show a friendly error.
            error_log('Database connection failed: ' . $e->getMessage());
            die('<p style="font-family:sans-serif;color:#c0392b;padding:2rem;">Unable to connect to the database. Please try again later.</p>');
        }
    }
    return $pdo;
}
