<?php
// Load loader first - MUST be at the very top
require_once __DIR__ . '/loader.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include config files
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/settings.php';

// Get page from GET parameter
$page = isset($_GET['page']) ? $_GET['page'] : 'home';

// Sanitize page input
$page = basename($page);
$page = preg_replace('/[^a-zA-Z0-9_-]/', '', $page);

// Route pages
$page_map = [
    'login' => 'pages/auth/login.php',
    'signup' => 'pages/auth/signup.php',
    'products' => 'pages/user/products.php',
    'product' => 'pages/user/product-detail.php',
    'cart' => 'pages/user/cart.php',
    'checkout' => 'pages/user/checkout.php',
    'payment' => 'pages/user/payment.php',
    'orders' => 'pages/user/orders.php',
    'order-detail' => 'pages/user/order-detail.php',
    'profile' => 'pages/user/profile.php',
    'contact' => 'pages/user/contact.php',
    'faq' => 'pages/user/faq.php',
    'support' => 'pages/user/support.php',
];

// Handle admin routes
if ($page === 'admin') {
    if (!isset($_SESSION['admin_id'])) {
        require 'pages/auth/admin-login.php';
    } else {
        $action = isset($_GET['action']) ? basename($_GET['action']) : 'dashboard';
        $action = preg_replace('/[^a-zA-Z0-9_-]/', '', $action);

        $admin_pages = [
            'dashboard' => 'pages/admin/dashboard.php',
            'categories' => 'pages/admin/categories.php',
            'products' => 'pages/admin/products.php',
            'orders' => 'pages/admin/orders.php',
            'order_detail' => 'pages/admin/order-detail.php',
            'users' => 'pages/admin/users.php',
            'coupons' => 'pages/admin/coupons.php',
            'contact-messages' => 'pages/admin/contact-messages.php',
            'reports' => 'pages/admin/reports.php',
            'settings' => 'pages/admin/settings.php',
        ];

        $file = $admin_pages[$action] ?? 'pages/admin/dashboard.php';
        if (file_exists($file)) {
            require $file;
        } else {
            require 'pages/admin/dashboard.php';
        }
    }
} else {
    // User pages
    $file = $page_map[$page] ?? 'pages/user/index.php';
    
    if (file_exists($file)) {
        require $file;
    } else {
        require 'pages/user/index.php';
    }
}
?>
