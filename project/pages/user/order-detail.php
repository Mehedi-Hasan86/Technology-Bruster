<?php
// Loader already included by index.php - no require needed

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . SITE_URL . '/?page=login');
    exit;
}

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
if (!$stmt) {
    die("❌ Query error");
}

$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    header('Location: ' . SITE_URL . '/?page=orders');
    exit;
}

$items_result = $conn->query("SELECT oi.*, p.title FROM order_items oi 
                             JOIN products p ON oi.product_id = p.id 
                             WHERE oi.order_id = $order_id");
$items = $items_result ? $items_result->fetch_all(MYSQLI_ASSOC) : [];

// Get download tokens if order is successful
$downloads = [];
if ($order['payment_status'] === 'success') {
    $token_result = $conn->query("SELECT dt.*, p.title FROM download_tokens dt 
                                 JOIN products p ON dt.product_id = p.id 
                                 WHERE dt.order_id = $order_id AND dt.expires_at > NOW()");
    $downloads = $token_result ? $token_result->fetch_all(MYSQLI_ASSOC) : [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/public/css/style.css">
</head>
<body>
<?php include INCLUDES_PATH . '/navbar.php'; ?>

<div class="main-content">
    <div style="max-width: 900px; margin: 0 auto; padding: 0 16px;">
        <h1 style="font-size: 24px; margin-bottom: 24px;">📋 Order #<?php echo htmlspecialchars($order['order_number']); ?></h1>

        <?php if (isset($_SESSION['payment_success'])): ?>
            <div style="background: #e8f5e9; color: #2e7d32; padding: 16px; border-radius: 8px; margin-bottom: 20px;">
                ✅ Payment successful! Your order is confirmed.
            </div>
            <?php unset($_SESSION['payment_success']); ?>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <!-- Order Items -->
            <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.1);">
                <h2 style="font-size: 18px; margin-bottom: 16px; font-weight: 600;">Products</h2>
                
                <?php if (empty($items)): ?>
                    <p style="opacity: 0.7;">No items</p>
                <?php else: ?>
                    <table style="width: 100%; font-size: 14px;">
                        <thead>
                            <tr style="background: #f5f5f5;">
                                <th style="padding: 8px; text-align: left; border-bottom: 1px solid #ddd;">Product</th>
                                <th style="padding: 8px; text-align: center; border-bottom: 1px solid #ddd;">Qty</th>
                                <th style="padding: 8px; text-align: right; border-bottom: 1px solid #ddd;">Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr style="border-bottom: 1px solid #eee;">
                                    <td style="padding: 8px;"><?php echo htmlspecialchars(substr($item['title'], 0, 30)); ?></td>
                                    <td style="padding: 8px; text-align: center;"><?php echo $item['quantity']; ?></td>
                                    <td style="padding: 8px; text-align: right; font-weight: 600;"><?php echo CURRENCY; ?> <?php echo number_format($item['price'], 0); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- Summary -->
            <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.1);">
                <h2 style="font-size: 18px; margin-bottom: 16px; font-weight: 600;">Summary</h2>
                
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px;">
                    <span>Subtotal:</span>
                    <strong><?php echo CURRENCY; ?> <?php echo number_format($order['total_amount'] - $order['tax_amount'] + $order['discount_amount'], 0); ?></strong>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px;">
                    <span>Tax:</span>
                    <strong><?php echo CURRENCY; ?> <?php echo number_format($order['tax_amount'], 0); ?></strong>
                </div>
                <?php if ($order['discount_amount'] > 0): ?>
                <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px;">
                    <span>Discount:</span>
                    <strong style="color: var(--success);">-<?php echo CURRENCY; ?> <?php echo number_format($order['discount_amount'], 0); ?></strong>
                </div>
                <?php endif; ?>
                <div style="display: flex; justify-content: space-between; font-size: 16px; font-weight: bold; padding: 12px 0; border-top: 2px solid #eee; border-bottom: 2px solid #eee; margin-bottom: 16px;">
                    <span>Total:</span>
                    <span style="color: var(--primary);"><?php echo CURRENCY; ?> <?php echo number_format($order['total_amount'], 0); ?></span>
                </div>

                <div style="background: <?php 
                    $bg_map = ['success' => '#e8f5e9', 'pending' => '#fff3cd', 'failed' => '#ffebee'];
                    echo $bg_map[$order['payment_status']] ?? '#f5f5f5';
                ?>; padding: 12px; border-radius: 6px; font-size: 14px;">
                    <strong>Status:</strong> <?php echo ucfirst($order['payment_status']); ?>
                </div>
            </div>
        </div>

        <!-- Downloads -->
        <?php if ($order['payment_status'] === 'success' && !empty($downloads)): ?>
        <div style="background: #e8f5e9; padding: 20px; border-radius: 8px; margin-top: 20px; border-left: 4px solid var(--success);">
            <h2 style="font-size: 18px; margin-bottom: 16px; font-weight: 600; color: #2e7d32;">📥 Downloads</h2>
            
            <div style="display: grid; gap: 8px;">
                <?php foreach ($downloads as $dl): ?>
                    <a href="<?php echo SITE_URL; ?>/api/download.php?token=<?php echo htmlspecialchars($dl['token']); ?>" 
                       style="display: block; padding: 12px; background: white; border-radius: 6px; text-decoration: none; color: var(--primary); font-weight: 600; text-align: center;">
                        📥 Download: <?php echo htmlspecialchars($dl['title']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
            
            <p style="font-size: 12px; margin-top: 12px; opacity: 0.7;">⏰ Downloads available for 30 days</p>
        </div>
        <?php endif; ?>

        <a href="<?php echo SITE_URL; ?>/?page=orders" style="display: inline-block; margin-top: 20px; color: var(--primary); text-decoration: none; font-weight: 600;">← Back to Orders</a>
    </div>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>
</body>
</html>
