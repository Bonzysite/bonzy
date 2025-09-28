<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

$db = get_db();

$db->beginTransaction();
try {
    $db->exec('CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        password_hash TEXT NOT NULL,
        created_at TEXT NOT NULL DEFAULT (datetime("now"))
    )');

    $db->exec('CREATE TABLE IF NOT EXISTS products (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        title TEXT NOT NULL,
        description TEXT,
        price_cents INTEGER NOT NULL,
        thumbnail_path TEXT,
        is_active INTEGER NOT NULL DEFAULT 1,
        created_at TEXT NOT NULL DEFAULT (datetime("now")),
        updated_at TEXT NOT NULL DEFAULT (datetime("now")),
        FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE
    )');

    $db->exec('CREATE TABLE IF NOT EXISTS orders (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        buyer_id INTEGER NOT NULL,
        total_cents INTEGER NOT NULL,
        status TEXT NOT NULL DEFAULT "pending",
        created_at TEXT NOT NULL DEFAULT (datetime("now")),
        FOREIGN KEY(buyer_id) REFERENCES users(id) ON DELETE CASCADE
    )');

    $db->exec('CREATE TABLE IF NOT EXISTS order_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        order_id INTEGER NOT NULL,
        product_id INTEGER NOT NULL,
        title TEXT NOT NULL,
        price_cents INTEGER NOT NULL,
        quantity INTEGER NOT NULL DEFAULT 1,
        FOREIGN KEY(order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY(product_id) REFERENCES products(id) ON DELETE CASCADE
    )');

    $db->commit();
    echo "Migration complete\n";
} catch (Throwable $e) {
    $db->rollBack();
    http_response_code(500);
    echo "Migration failed: " . $e->getMessage();
}

