<?php
require_once dirname(__DIR__) . '/loader.php';

$action = $_GET['action'] ?? '';

if ($action === 'logout') {
    unset($_SESSION['user_id']);
    unset($_SESSION['user_name']);
    unset($_SESSION['user_email']);
    header('Location: ' . SITE_URL);
    exit;
}
elseif ($action === 'admin_logout') {
    unset($_SESSION['admin_id']);
    unset($_SESSION['admin_name']);
    unset($_SESSION['admin_role']);
    header('Location: ' . SITE_URL);
    exit;
}
else {
    header('Location: ' . SITE_URL);
    exit;
}
?>
