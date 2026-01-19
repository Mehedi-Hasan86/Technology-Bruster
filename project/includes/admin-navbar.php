<?php
if (!defined('SITE_URL')) {
    define('SITE_URL', 'http://localhost/project');
}
?>
<nav style="background: #1a1a2e; color: white; padding: 12px 24px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">
    <div style="font-weight: bold; font-size: 18px;">
        <a href="<?php echo SITE_URL; ?>/?page=admin" style="color: white; text-decoration: none;">
            ⚙️ Admin Panel
        </a>
    </div>
    
    <div style="display: flex; gap: 20px; align-items: center;">
        <span style="font-size: 14px;">👤 <?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></span>
        <a href="<?php echo SITE_URL; ?>" style="color: white; text-decoration: none; font-size: 14px;">View Site</a>
        <a href="<?php echo SITE_URL; ?>/api/auth.php?action=admin_logout" style="color: #ff6b6b; text-decoration: none; font-size: 14px; font-weight: 600;">Logout</a>
    </div>
</nav>
