<?php
// Ensure constants are defined
if (!defined('SITE_URL')) {
    define('SITE_URL', 'http://localhost/project');
}
if (!defined('SITE_NAME')) {
    define('SITE_NAME', 'Technology-Bruster');
}
?>
<?php
$is_mobile = isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/mobile|android|iphone/i', $_SERVER['HTTP_USER_AGENT']);
?>

<?php if ($is_mobile): ?>
<!-- Mobile AppBar -->
<div class="appbar">
    <div class="appbar-logo"><?php echo SITE_NAME; ?></div>
    <div class="appbar-menu">☰</div>
</div>

<!-- Mobile Bottom Navigation -->
<div class="bottom-nav">
    <a href="<?php echo SITE_URL; ?>" class="nav-item <?php echo (!isset($_GET['page']) || $_GET['page'] == 'home') ? 'active' : ''; ?>">
        <span>🏠</span><span>Home</span>
    </a>
    <a href="<?php echo SITE_URL; ?>/?page=products" class="nav-item <?php echo ($_GET['page'] ?? '') == 'products' ? 'active' : ''; ?>">
        <span>🛍️</span><span>Products</span>
    </a>
    <a href="<?php echo SITE_URL; ?>/?page=cart" class="nav-item <?php echo ($_GET['page'] ?? '') == 'cart' ? 'active' : ''; ?>">
        <span>🛒</span><span>Cart</span>
    </a>
    <a href="<?php echo isset($_SESSION['user_id']) ? SITE_URL . '/?page=profile' : SITE_URL . '/?page=login'; ?>" class="nav-item <?php echo ($_GET['page'] ?? '') == 'profile' || ($_GET['page'] ?? '') == 'login' ? 'active' : ''; ?>">
        <span>👤</span><span><?php echo isset($_SESSION['user_id']) ? 'Profile' : 'Login'; ?></span>
    </a>
</div>

<?php else: ?>
<!-- Desktop Navbar -->
<nav style="background: #2e7ed3ff; color: white; padding: 12px 16px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
    <div style="font-weight: bold; font-size: 18px;">
        <a href="<?php echo SITE_URL; ?>" style="color: white; text-decoration: none;">
            🚀 <?php echo SITE_NAME; ?>
        </a>
    </div>
    
    <div style="display: flex; gap: 16px; flex-wrap: wrap; align-items: center;">
        <a href="<?php echo SITE_URL; ?>" style="color: white; text-decoration: none; font-size: 14px;">Home</a>
        <a href="<?php echo SITE_URL; ?>/?page=products" style="color: white; text-decoration: none; font-size: 14px;">Products</a>
        <a href="<?php echo SITE_URL; ?>/?page=help" style="color: white; text-decoration: none; font-size: 14px;">Help</a>
        
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="<?php echo SITE_URL; ?>/?page=cart" style="color: white; text-decoration: none; font-size: 14px;">Cart</a>
            <a href="<?php echo SITE_URL; ?>/?page=orders" style="color: white; text-decoration: none; font-size: 14px;">Orders</a>
            <a href="<?php echo SITE_URL; ?>/?page=profile" style="color: white; text-decoration: none; font-size: 14px;">Profile</a>
            <a href="<?php echo SITE_URL; ?>/api/auth.php?action=logout" style="color: white; text-decoration: none; font-size: 14px;">Logout</a>
        <?php else: ?>
            <a href="<?php echo SITE_URL; ?>/?page=login" style="color: white; text-decoration: none; font-size: 14px;">Login</a>
            <a href="<?php echo SITE_URL; ?>/?page=signup" style="color: white; text-decoration: none; font-size: 14px;">Sign Up</a>
        <?php endif; ?>

        <?php if (isset($_SESSION['admin_id'])): ?>
            <div style="border-left: 1px solid rgba(255,255,255,0.3); padding-left: 16px; margin-left: 8px;">
                <a href="<?php echo SITE_URL; ?>/?page=admin" style="color: #ffeb3b; text-decoration: none; font-size: 14px; font-weight: bold;">
                    <i class="fas fa-shield-alt"></i> Admin Panel
                </a>
                <a href="<?php echo SITE_URL; ?>/api/auth.php?action=admin_logout" style="color: #ffeb3b; text-decoration: none; font-size: 14px; margin-left: 12px;">
                    Logout
                </a>
            </div>
        <?php else: ?>
            <div style="border-left: 1px solid rgba(255,255,255,0.3); padding-left: 16px; margin-left: 8px;">
                <a href="<?php echo SITE_URL; ?>/?page=admin" style="color: #ffeb3b; text-decoration: none; font-size: 14px; font-weight: bold;">
                    <i class="fas fa-shield-alt"></i> Admin
                </a>
            </div>
        <?php endif; ?>
    </div>
</nav>
<?php endif; ?>
