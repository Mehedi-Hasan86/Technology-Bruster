<?php
// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Site Configuration - Only define if not already defined
if (!defined('SITE_NAME')) {
    define('SITE_NAME', 'Technology-Bruster');
}
if (!defined('SITE_URL')) {
    define('SITE_URL', 'http://localhost/project');
}

// PROJECT_ROOT is already defined in loader.php, don't redefine it

// Directory Paths - Use PROJECT_ROOT if available
if (!defined('UPLOAD_DIR')) {
    define('UPLOAD_DIR', (defined('PROJECT_ROOT') ? PROJECT_ROOT : __DIR__ . '/..') . '/uploads/');
}
if (!defined('PRODUCT_FILE_DIR')) {
    define('PRODUCT_FILE_DIR', (defined('PROJECT_ROOT') ? PROJECT_ROOT : __DIR__ . '/..') . '/uploads/products/');
}
if (!defined('PRODUCT_IMG_DIR')) {
    define('PRODUCT_IMG_DIR', (defined('PROJECT_ROOT') ? PROJECT_ROOT : __DIR__ . '/..') . '/uploads/product_images/');
}

// Create directories if they don't exist
$dirs = [UPLOAD_DIR, PRODUCT_FILE_DIR, PRODUCT_IMG_DIR];
foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// Payment Configuration
define('BKASH_API_KEY', 'your_bkash_key');
define('SSL_STORE_ID', 'your_ssl_store_id');

// Email Configuration
define('MAIL_FROM', 'noreply@technologybruster.com');
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);

// Security
define('PASSWORD_HASH_ALGO', PASSWORD_BCRYPT);
define('SESSION_TIMEOUT', 3600);

// Pagination
define('ITEMS_PER_PAGE', 12);

// File Upload Limits
define('MAX_FILE_SIZE', 500 * 1024 * 1024);
define('ALLOWED_EXTENSIONS', ['zip', 'rar', '7z', 'pdf', 'exe']);

// Tax & Currency
define('TAX_PERCENTAGE', 5);
define('CURRENCY', 'BDT');
define('CURRENCY_SYMBOL', '৳');

// Error suppression for production (comment out in development)
// error_reporting(0);
// ini_set('display_errors', 0);
?>
