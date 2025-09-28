<?php
declare(strict_types=1);

function get_db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $needInit = !file_exists(AGT_DB_PATH);
    $pdo = new PDO('sqlite:' . AGT_DB_PATH, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    // Pragmas for better behavior
    $pdo->exec('PRAGMA foreign_keys = ON');
    $pdo->exec('PRAGMA journal_mode = WAL');
    if ($needInit) {
        // If DB newly created, ensure permissions are relaxed for dev
        @chmod(AGT_DB_PATH, 0664);
    }
    return $pdo;
}

