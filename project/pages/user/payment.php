<?php
// Loader already included by index.php - no require needed

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . SITE_URL . '/?page=login');
    exit;
}

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
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

// Handle payment confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
    $transaction_id = strtoupper($order['payment_method']) . '-' . time();
    
    $update_stmt = $conn->prepare("UPDATE orders SET payment_status = 'success', transaction_id = ?, status = 'completed' WHERE id = ?");
    if ($update_stmt) {
        $update_stmt->bind_param("si", $transaction_id, $order_id);
        if ($update_stmt->execute()) {
            // Create download tokens
            $items_result = $conn->query("SELECT product_id FROM order_items WHERE order_id = $order_id");
            if ($items_result) {
                while ($item = $items_result->fetch_assoc()) {
                    $token = bin2hex(random_bytes(32));
                    $expires = date('Y-m-d H:i:s', time() + (30 * 24 * 3600));
                    
                    $token_stmt = $conn->prepare("INSERT INTO download_tokens (user_id, product_id, order_id, token, expires_at) 
                                                 VALUES (?, ?, ?, ?, ?)");
                    if ($token_stmt) {
                        $token_stmt->bind_param("iiiis", $user_id, $item['product_id'], $order_id, $token, $expires);
                        $token_stmt->execute();
                        $token_stmt->close();
                    }
                }
            }
            
            $_SESSION['payment_success'] = true;
            header('Location: ' . SITE_URL . '/?page=order-detail&id=' . $order_id);
            exit;
        }
        $update_stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/public/css/style.css">
</head>
<body>
<?php include INCLUDES_PATH . '/navbar.php'; ?>

<div class="main-content">
    <div style="max-width: 600px; margin: 0 auto; padding: 0 16px;">
        <h1 style="font-size: 24px; margin-bottom: 24px; text-align: center;">💳 Complete Payment</h1>

        <div style="background: white; padding: 24px; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.1); text-align: center;">
            <div style="font-size: 48px; margin-bottom: 16px;">💰</div>
            <h2 style="font-size: 20px; margin-bottom: 8px;">Order #<?php echo htmlspecialchars($order['order_number']); ?></h2>
            <div style="font-size: 32px; color: var(--primary); font-weight: bold; margin-bottom: 20px;">
                <?php echo CURRENCY; ?> <?php echo number_format($order['total_amount'], 0); ?>
            </div>

            <div style="background: #f5f5f5; padding: 16px; border-radius: 8px; margin-bottom: 24px; text-align: left;">
                <p style="margin: 8px 0;"><strong>Payment Method:</strong> <?php echo ucfirst($order['payment_method']); ?></p>
                <p style="margin: 8px 0;"><strong>Order Date:</strong> <?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></p>
                <p style="margin: 8px 0;"><strong>Status:</strong> <span style="background: #fff3cd; padding: 2px 6px; border-radius: 4px;">Pending</span></p>
            </div>

            <div style="background: #e3f2fd; padding: 16px; border-radius: 8px; margin-bottom: 24px; text-align: left;">
                <h3 style="margin-bottom: 12px; font-weight: 600;">📋 Payment Instructions</h3>
                <?php if ($order['payment_method'] === 'bkash'): ?>
                    <ol style="margin: 0; padding-left: 20px;">
                        <li>Open bKash app</li>
                        <li>Select "Send Money"</li>
                        <li>Enter merchant number</li>
                        <li>Confirm amount: <?php echo CURRENCY; ?> <?php echo number_format($order['total_amount'], 0); ?></li>
                        <li>Complete transaction</li>
                    </ol>
                <?php elseif ($order['payment_method'] === 'ssl'): ?>
                    <p>You will be redirected to SSLCommerz secure payment gateway.</p>
                <?php else: ?>
                    <p>Please transfer the amount to the bank account provided in your email.</p>
                <?php endif; ?>
            </div>

            <form method="POST">
                <input type="hidden" name="confirm_payment" value="1">
                <button type="submit" class="btn btn-primary" style="width: 100%; padding: 14px; font-size: 16px; font-weight: 600;">✓ Confirm Payment</button>
            </form>

            <a href="<?php echo SITE_URL; ?>/?page=orders" style="display: block; margin-top: 12px; color: var(--primary); text-decoration: none; font-size: 14px;">← Back to Orders</a>
        </div>
    </div>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>
</body>
</html>
