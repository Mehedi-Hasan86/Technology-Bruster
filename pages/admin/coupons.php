<?php
// Loader already included by index.php - no require needed

if (!isset($_SESSION['admin_id'])) {
    header('Location: ' . SITE_URL . '/?page=admin');
    exit;
}

$error = '';
$success = '';

// Handle add coupon
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'add_coupon') {
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $discount_type = $_POST['discount_type'];
        $discount_value = (float)($_POST['discount_value'] ?? 0);
        $max_usage = (int)($_POST['max_usage'] ?? 100);
        $expires_at = $_POST['expires_at'] ?? null;

        if (empty($code) || $discount_value <= 0) {
            $error = '❌ Code and value required';
        } else {
            $stmt = $conn->prepare("INSERT INTO coupons (code, discount_type, discount_value, max_usage, expires_at, is_active) 
                                   VALUES (?, ?, ?, ?, ?, 1)");
            if ($stmt) {
                $stmt->bind_param("ssdis", $code, $discount_type, $discount_value, $max_usage, $expires_at);
                if ($stmt->execute()) {
                    $success = '✅ Coupon created';
                } else {
                    $error = '❌ ' . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
}

// Get coupons
$coupons = $conn->query("SELECT * FROM coupons ORDER BY created_at DESC");
$coupons = $coupons ? $coupons->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coupons - Admin</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/public/css/style.css">
</head>
<body>
<?php include INCLUDES_PATH . '/admin-navbar.php'; ?>

<div style="display: flex; gap: 24px; padding: 24px;">
    <?php include INCLUDES_PATH . '/admin-sidebar.php'; ?>

    <div style="flex: 1; max-width: 1000px;">
        <h1 style="font-size: 28px; margin-bottom: 24px;">🎟️ Manage Coupons</h1>

        <?php if ($error): ?>
            <div style="background: #ffebee; color: #c62828; padding: 12px; border-radius: 8px; margin-bottom: 16px;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div style="background: #e8f5e9; color: #2e7d32; padding: 12px; border-radius: 8px; margin-bottom: 16px;">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <!-- Add Coupon Form -->
        <div style="background: white; padding: 24px; border-radius: 8px; margin-bottom: 24px; box-shadow: 0 1px 4px rgba(0,0,0,0.1);">
            <h2 style="font-size: 20px; margin-bottom: 16px; font-weight: 600;">Create New Coupon</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add_coupon">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label>Coupon Code *</label>
                        <input type="text" name="code" placeholder="e.g., SAVE20" required>
                    </div>

                    <div class="form-group">
                        <label>Discount Type *</label>
                        <select name="discount_type" required>
                            <option value="flat">Flat Amount</option>
                            <option value="percentage">Percentage</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Discount Value *</label>
                        <input type="number" name="discount_value" step="0.01" min="0" required>
                    </div>

                    <div class="form-group">
                        <label>Max Usage</label>
                        <input type="number" name="max_usage" value="100" min="1">
                    </div>

                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label>Expires At (Optional)</label>
                        <input type="datetime-local" name="expires_at">
                    </div>

                    <button type="submit" class="btn btn-primary" style="grid-column: 1 / -1; padding: 12px;">Create Coupon</button>
                </div>
            </form>
        </div>

        <!-- Coupons List -->
        <div style="background: white; padding: 24px; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.1);">
            <h2 style="font-size: 20px; margin-bottom: 16px; font-weight: 600;">All Coupons (<?php echo count($coupons); ?>)</h2>
            
            <?php if (empty($coupons)): ?>
                <p style="opacity: 0.7; text-align: center; padding: 20px;">No coupons yet</p>
            <?php else: ?>
                <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                    <thead>
                        <tr style="background: #f5f5f5;">
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Code</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Type</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Value</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Usage</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Expires</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($coupons as $c): ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 12px;"><strong><?php echo htmlspecialchars($c['code']); ?></strong></td>
                                <td style="padding: 12px;"><?php echo ucfirst($c['discount_type']); ?></td>
                                <td style="padding: 12px;"><?php echo number_format($c['discount_value'], 2); ?><?php echo $c['discount_type'] === 'percentage' ? '%' : ''; ?></td>
                                <td style="padding: 12px;"><?php echo $c['current_usage']; ?>/<?php echo $c['max_usage']; ?></td>
                                <td style="padding: 12px; font-size: 12px; opacity: 0.7;"><?php echo $c['expires_at'] ? date('M d, Y', strtotime($c['expires_at'])) : 'No limit'; ?></td>
                                <td style="padding: 12px;">
                                    <span style="background: <?php echo $c['is_active'] ? '#e8f5e9' : '#fff3e0'; ?>; padding: 4px 8px; border-radius: 4px; font-size: 11px;">
                                        <?php echo $c['is_active'] ? '✅ Active' : '⭕ Inactive'; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>
</body>
</html>
