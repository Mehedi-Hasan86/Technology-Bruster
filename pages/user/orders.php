<?php
// Loader already included by index.php - no require needed

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . SITE_URL . '/?page=login');
    exit;
}

$user_id = $_SESSION['user_id'];

$query = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/public/css/style.css">
</head>
<body>
<?php include INCLUDES_PATH . '/navbar.php'; ?>

<div class="main-content">
    <div style="padding: 0 16px;">
        <h1 style="font-size: 24px; margin-bottom: 20px;">My Orders</h1>

        <?php if (empty($orders)): ?>
            <div style="text-align: center; padding: 40px 0;">
                <p style="font-size: 16px; margin-bottom: 16px;">You haven't placed any orders yet</p>
                <a href="<?php echo SITE_URL; ?>/?page=products" class="btn btn-primary" style="display: inline-block; padding: 10px 20px;">Start Shopping</a>
            </div>
        <?php else: ?>
            <div style="display: grid; gap: 12px;">
                <?php foreach ($orders as $order): ?>
                    <div style="background: white; padding: 16px; border-radius: 8px; border-left: 4px solid var(--primary);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                            <div>
                                <div style="font-weight: bold;">Order #<?php echo htmlspecialchars($order['order_number']); ?></div>
                                <div style="font-size: 12px; opacity: 0.7;"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 18px; font-weight: bold; color: var(--primary);"><?php echo CURRENCY; ?> <?php echo number_format($order['total_amount'], 0); ?></div>
                                <div style="font-size: 12px; padding: 4px 8px; background: <?php echo $order['payment_status'] == 'success' ? '#e8f5e9' : '#fff3e0'; ?>; color: <?php echo $order['payment_status'] == 'success' ? '#2e7d32' : '#e65100'; ?>; border-radius: 4px; margin-top: 4px;"><?php echo ucfirst($order['payment_status']); ?></div>
                            </div>
                        </div>

                        <a href="<?php echo SITE_URL; ?>/?page=order-detail&id=<?php echo $order['id']; ?>" class="btn btn-primary" style="width: 100%; padding: 10px; text-align: center; text-decoration: none; display: block; font-size: 14px;">View Details & Download</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>
</body>
</html>
