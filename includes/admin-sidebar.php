<?php
$current_action = isset($_GET['action']) ? $_GET['action'] : 'dashboard';
?>
<div style="width: 250px; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.1); height: fit-content;">
    <nav style="list-style: none;">
        <a href="<?php echo SITE_URL; ?>/?page=admin" style="display: block; padding: 12px; margin-bottom: 8px; color: <?php echo $current_action === 'dashboard' || $current_action === '' ? '#007bff' : '#666'; ?>; text-decoration: none; font-weight: 600; border-left: 3px solid <?php echo $current_action === 'dashboard' || $current_action === '' ? '#007bff' : 'transparent'; ?>;">
            📊 Dashboard
        </a>
        <a href="<?php echo SITE_URL; ?>/?page=admin&action=categories" style="display: block; padding: 12px; margin-bottom: 8px; color: <?php echo $current_action === 'categories' ? '#007bff' : '#666'; ?>; text-decoration: none; font-weight: 600; border-left: 3px solid <?php echo $current_action === 'categories' ? '#007bff' : 'transparent'; ?>;">
            📂 Categories
        </a>
        <a href="<?php echo SITE_URL; ?>/?page=admin&action=products" style="display: block; padding: 12px; margin-bottom: 8px; color: <?php echo $current_action === 'products' ? '#007bff' : '#666'; ?>; text-decoration: none; font-weight: 600; border-left: 3px solid <?php echo $current_action === 'products' ? '#007bff' : 'transparent'; ?>;">
            📦 Products
        </a>
        <a href="<?php echo SITE_URL; ?>/?page=admin&action=orders" style="display: block; padding: 12px; margin-bottom: 8px; color: <?php echo $current_action === 'orders' ? '#007bff' : '#666'; ?>; text-decoration: none; font-weight: 600; border-left: 3px solid <?php echo $current_action === 'orders' ? '#007bff' : 'transparent'; ?>;">
            📋 Orders
        </a>
        <a href="<?php echo SITE_URL; ?>/?page=admin&action=users" style="display: block; padding: 12px; margin-bottom: 8px; color: <?php echo $current_action === 'users' ? '#007bff' : '#666'; ?>; text-decoration: none; font-weight: 600; border-left: 3px solid <?php echo $current_action === 'users' ? '#007bff' : 'transparent'; ?>;">
            👥 Users
        </a>
        <a href="<?php echo SITE_URL; ?>/?page=admin&action=admins" style="display: block; padding: 12px; margin-bottom: 8px; color: <?php echo $current_action === 'admins' ? '#007bff' : '#666'; ?>; text-decoration: none; font-weight: 600; border-left: 3px solid <?php echo $current_action === 'admins' ? '#007bff' : 'transparent'; ?>;">
            👨‍💼 Admins
        </a>
        <a href="<?php echo SITE_URL; ?>/?page=admin&action=coupons" style="display: block; padding: 12px; margin-bottom: 8px; color: <?php echo $current_action === 'coupons' ? '#007bff' : '#666'; ?>; text-decoration: none; font-weight: 600; border-left: 3px solid <?php echo $current_action === 'coupons' ? '#007bff' : 'transparent'; ?>;">
            🎟️ Coupons
        </a>
        <a href="<?php echo SITE_URL; ?>/?page=admin&action=contact-messages" style="display: block; padding: 12px; margin-bottom: 8px; color: <?php echo $current_action === 'contact-messages' ? '#007bff' : '#666'; ?>; text-decoration: none; font-weight: 600; border-left: 3px solid <?php echo $current_action === 'contact-messages' ? '#007bff' : 'transparent'; ?>;">
            📧 Contact Messages
        </a>
        <a href="<?php echo SITE_URL; ?>/?page=admin&action=reports" style="display: block; padding: 12px; margin-bottom: 8px; color: <?php echo $current_action === 'reports' ? '#007bff' : '#666'; ?>; text-decoration: none; font-weight: 600; border-left: 3px solid <?php echo $current_action === 'reports' ? '#007bff' : 'transparent'; ?>;">
            📈 Reports
        </a>
        <a href="<?php echo SITE_URL; ?>/?page=admin&action=settings" style="display: block; padding: 12px; margin-bottom: 8px; color: <?php echo $current_action === 'settings' ? '#007bff' : '#666'; ?>; text-decoration: none; font-weight: 600; border-left: 3px solid <?php echo $current_action === 'settings' ? '#007bff' : 'transparent'; ?>;">
            ⚙️ Settings
        </a>
    </nav>
</div>
