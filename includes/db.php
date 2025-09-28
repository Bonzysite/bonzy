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

    // Ensure schema exists (handles cases where DB file exists but tables do not)
    try {
        $hasProducts = (bool)$pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='products'")?->fetchColumn();
        if (!$hasProducts) {
            $schemaPath = AGT_BASE_PATH . '/data/schema.sql';
            if (is_readable($schemaPath)) {
                $sql = file_get_contents($schemaPath) ?: '';
                if ($sql !== '') {
                    $pdo->exec($sql);
                }
            } else {
                // Fallback minimal schema if schema.sql not available
                $pdo->exec(<<<SQL
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    created_at TEXT NOT NULL DEFAULT (datetime('now'))
);
CREATE TABLE IF NOT EXISTS products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    title TEXT NOT NULL,
    description TEXT,
    price_cents INTEGER NOT NULL,
    thumbnail_path TEXT,
    is_active INTEGER NOT NULL DEFAULT 1,
    created_at TEXT NOT NULL DEFAULT (datetime('now')),
    updated_at TEXT NOT NULL DEFAULT (datetime('now')),
    FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    buyer_id INTEGER NOT NULL,
    total_cents INTEGER NOT NULL,
    status TEXT NOT NULL DEFAULT 'pending',
    created_at TEXT NOT NULL DEFAULT (datetime('now')),
    FOREIGN KEY(buyer_id) REFERENCES users(id) ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS order_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id INTEGER NOT NULL,
    product_id INTEGER NOT NULL,
    title TEXT NOT NULL,
    price_cents INTEGER NOT NULL,
    quantity INTEGER NOT NULL DEFAULT 1,
    FOREIGN KEY(order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE
);
SQL);
            }
        }
    } catch (Throwable $e) {
        // Log but do not crash hard here; subsequent queries will surface errors
        file_put_contents(AGT_DATA_PATH . '/php_errors.log', '[' . date('c') . "] Schema ensure error: " . $e->getMessage() . "\n", FILE_APPEND);
    }
    return $pdo;
}

