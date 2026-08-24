<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/helpers.php';

date_default_timezone_set(TIMEZONE);

/**
 * True once the admin has replaced the placeholder DB values in config.php.
 */
function isDbConfigured(): bool
{
    return DB_NAME !== '' && DB_USER !== ''
        && DB_NAME !== 'your_database_name'
        && DB_USER !== 'your_database_user'
        && DB_PASS !== 'your_database_password';
}

/**
 * Turns a DB exception into a short, actionable message (never leaks credentials).
 */
function dbErrorMessage(Throwable $e): string
{
    if (!isDbConfigured()) {
        return 'Database is not configured yet. Edit includes/config.php with your Hostinger database name, user and password, then reload this page.';
    }
    if ($e instanceof PDOException) {
        return 'Could not connect to the database. Double-check DB_HOST, DB_NAME, DB_USER and DB_PASS in includes/config.php. (Details logged on the server.)';
    }
    return 'Something went wrong loading data. Check the server error log for details.';
}

function db(): PDO
{
    static $pdo = null;
    static $initialized = false;

    if ($pdo === null) {
        if (!isDbConfigured()) {
            throw new RuntimeException(
                'Database is not configured. Edit includes/config.php with your real DB_HOST, DB_NAME, DB_USER and DB_PASS.'
            );
        }

        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $e) {
            error_log('[DB connect error] ' . $e->getMessage());
            throw $e;
        }
    }

    if (!$initialized) {
        $initialized = true;
        initDatabase($pdo);
    }

    return $pdo;
}

function initDatabase(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS visits (
            id INT AUTO_INCREMENT PRIMARY KEY,
            visitorName VARCHAR(255) NOT NULL,
            visitorEmail VARCHAR(255) DEFAULT '',
            visitorPhone VARCHAR(50) DEFAULT '',
            organization VARCHAR(255) DEFAULT '',
            whomToMeet VARCHAR(255) NOT NULL,
            date VARCHAR(50) NOT NULL,
            purpose TEXT,
            numPeople INT DEFAULT 1,
            status VARCHAR(50) DEFAULT 'Pending',
            meetingTime VARCHAR(100) DEFAULT NULL,
            note TEXT DEFAULT NULL,
            createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
    ");
}
