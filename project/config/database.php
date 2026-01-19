<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'technology_bruster');

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        die("❌ Database Connection Error: " . $conn->connect_error);
    }

    $conn->set_charset("utf8mb4");
} catch (Exception $e) {
    die("❌ Database Error: " . $e->getMessage());
}
?>
