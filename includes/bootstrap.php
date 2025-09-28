<?php
declare(strict_types=1);

// Start session and basic configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('AGT_SHOP_NAME', 'AGT Shop');
define('AGT_BASE_PATH', dirname(__DIR__));
define('AGT_PUBLIC_PATH', AGT_BASE_PATH . '/public');
define('AGT_DATA_PATH', AGT_BASE_PATH . '/data');
define('AGT_UPLOADS_PATH', AGT_BASE_PATH . '/uploads');
define('AGT_DB_PATH', AGT_DATA_PATH . '/agt_shop.sqlite');

// Ensure required directories exist
@mkdir(AGT_DATA_PATH, 0775, true);
@mkdir(AGT_UPLOADS_PATH, 0775, true);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/util.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/render.php';

// Set default timezone
date_default_timezone_set('UTC');

// Basic error handling to a file
set_error_handler(function (int $severity, string $message, string $file, int $line): void {
    $logLine = sprintf("[%s] %s in %s:%d\n", date('c'), $message, $file, $line);
    file_put_contents(AGT_DATA_PATH . '/php_errors.log', $logLine, FILE_APPEND);
});

