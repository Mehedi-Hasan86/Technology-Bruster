<?php
// Prevent multiple inclusions
if (defined('PROJECT_ROOT')) {
    return;
}

// Get project root
$project_root = dirname(__FILE__);

// Define paths (only once)
define('PROJECT_ROOT', $project_root);
define('CONFIG_PATH', $project_root . '/config');
define('PAGES_PATH', $project_root . '/pages');
define('INCLUDES_PATH', $project_root . '/includes');
define('API_PATH', $project_root . '/api');
define('CLASSES_PATH', $project_root . '/classes');
define('PUBLIC_PATH', $project_root . '/public');

// Load config files
if (!file_exists(CONFIG_PATH . '/database.php')) {
    die('❌ Config file not found: ' . CONFIG_PATH . '/database.php');
}

require_once CONFIG_PATH . '/database.php';
require_once CONFIG_PATH . '/settings.php';

// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
