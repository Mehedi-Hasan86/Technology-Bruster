<?php
// Loader already included by index.php - no require needed

if (!isset($_SESSION['admin_id'])) {
    header('Location: ' . SITE_URL . '/?page=admin');
    exit;
}

$success = '';

// Handle settings save
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tax_percentage = (float)($_POST['tax_percentage'] ?? TAX_PERCENTAGE);
    
    // Update settings
    $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) 
                           ON DUPLICATE KEY UPDATE setting_value = ?");
    if ($stmt) {
        $stmt->bind_param("sss", $key, $value, $value);
        
        $key = 'tax_percentage';
        $value = $tax_percentage;
        $stmt->execute();
        
        $success = '✅ Settings saved';
        $stmt->close();
    }
}

// Get current settings
$tax_result = $conn->query("SELECT setting_value FROM settings WHERE setting_key = 'tax_percentage'");
$current_tax = $tax_result ? ($tax_result->fetch_assoc()['setting_value'] ?? TAX_PERCENTAGE) : TAX_PERCENTAGE;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/public/css/style.css">
</head>
<body>
<?php include INCLUDES_PATH . '/admin-navbar.php'; ?>

<div style="display: flex; gap: 24px; padding: 24px;">
    <?php include INCLUDES_PATH . '/admin-sidebar.php'; ?>

    <div style="flex: 1; max-width: 700px;">
        <h1 style="font-size: 28px; margin-bottom: 24px;">⚙️ System Settings</h1>

        <?php if ($success): ?>
            <div style="background: #e8f5e9; color: #2e7d32; padding: 12px; border-radius: 8px; margin-bottom: 16px;">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <!-- General Settings -->
        <div style="background: white; padding: 24px; border-radius: 8px; margin-bottom: 24px; box-shadow: 0 1px 4px rgba(0,0,0,0.1);">
            <h2 style="font-size: 20px; margin-bottom: 16px; font-weight: 600;">General Settings</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Site Name</label>
                    <input type="text" value="<?php echo SITE_NAME; ?>" disabled>
                </div>

                <div class="form-group">
                    <label>Currency</label>
                    <input type="text" value="<?php echo CURRENCY; ?>" disabled>
                </div>

                <div class="form-group">
                    <label>Tax Percentage (%)</label>
                    <input type="number" name="tax_percentage" step="0.01" value="<?php echo $current_tax; ?>">
                </div>

                <button type="submit" class="btn btn-primary" style="padding: 12px 24px;">Save Settings</button>
            </form>
        </div>

        <!-- Payment Settings -->
        <div style="background: white; padding: 24px; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.1);">
            <h2 style="font-size: 20px; margin-bottom: 16px; font-weight: 600;">Payment Methods</h2>
            <div style="display: grid; gap: 16px;">
                <div style="padding: 12px; background: #f5f5f5; border-radius: 8px;">
                    <h3 style="font-weight: 600; margin-bottom: 4px;">💳 bKash</h3>
                    <p style="font-size: 12px; opacity: 0.7;">Enabled</p>
                </div>
                <div style="padding: 12px; background: #f5f5f5; border-radius: 8px;">
                    <h3 style="font-weight: 600; margin-bottom: 4px;">🏦 SSLCommerz</h3>
                    <p style="font-size: 12px; opacity: 0.7;">Enabled</p>
                </div>
                <div style="padding: 12px; background: #f5f5f5; border-radius: 8px;">
                    <h3 style="font-weight: 600; margin-bottom: 4px;">🏧 Bank Transfer</h3>
                    <p style="font-size: 12px; opacity: 0.7;">Enabled</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>
</body>
</html>
