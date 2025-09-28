<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';

echo "<h1>AGT Shop Database Initialization</h1>";

$db = get_db();

try {
    // Check if products table exists
    $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='products'");
    $hasProducts = $stmt ? $stmt->fetchColumn() : false;
    
    if ($hasProducts) {
        echo "<p style='color: green;'>✓ Database already initialized. Products table exists.</p>";
    } else {
        echo "<p style='color: orange;'>⚠ Products table not found. Initializing schema...</p>";
        
        // Load schema from file
        $schemaPath = AGT_BASE_PATH . '/data/schema.sql';
        if (is_readable($schemaPath)) {
            $sql = file_get_contents($schemaPath);
            if ($sql !== '') {
                $db->exec($sql);
                echo "<p style='color: green;'>✓ Schema loaded from data/schema.sql</p>";
            } else {
                throw new Exception("Schema file is empty");
            }
        } else {
            throw new Exception("Schema file not found at: " . $schemaPath);
        }
        
        // Verify tables were created
        $stmt = $db->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name");
        $tables = $stmt ? $stmt->fetchAll(PDO::FETCH_COLUMN) : [];
        
        echo "<p style='color: green;'>✓ Database initialized successfully!</p>";
        echo "<p>Tables created: " . implode(', ', $tables) . "</p>";
    }
    
    // Test a simple query
    $stmt = $db->query("SELECT COUNT(*) as count FROM products");
    $count = $stmt ? $stmt->fetchColumn() : 0;
    echo "<p>Products in database: " . $count . "</p>";
    
    echo "<p><a href='/'>← Back to Homepage</a></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<p>Database path: " . htmlspecialchars(AGT_DB_PATH) . "</p>";
    echo "<p>Schema path: " . htmlspecialchars(AGT_BASE_PATH . '/data/schema.sql') . "</p>";
}