<?php
// Loader already included by index.php - no require needed

if (!isset($_SESSION['admin_id'])) {
    header('Location: ' . SITE_URL . '/?page=admin');
    exit;
}

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $conn->prepare("SELECT o.*, u.name, u.email FROM orders o JOIN users u ON o.user_id = u.id WHERE o.id = ?");
if (!$stmt) die("❌ Query error");

$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    header('Location: ' . SITE_URL . '/?page=admin&action=orders');
    exit;
}

$items_result = $conn->query("SELECT oi.*, p.title FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = $order_id");
$items = $items_result ? $items_result->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - Admin</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/public/css/style.css">
</head>
<body>
<?php include INCLUDES_PATH . '/admin-navbar.php'; ?>

<div style="display: flex; gap: 24px; padding: 24px;">
    <?php include INCLUDES_PATH . '/admin-sidebar.php'; ?>

    <div style="flex: 1; max-width: 1000px;">
        <h1 style="font-size: 28px; margin-bottom: 24px;">Order #<?php echo htmlspecialchars($order['order_number']); ?></h1>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <!-- Customer Info -->
            <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.1);">
                <h2 style="font-size: 18px; margin-bottom: 16px; font-weight: 600;">Customer</h2>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($order['name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                <p><strong>Amount:</strong> <?php echo CURRENCY; ?> <?php echo number_format($order['total_amount'], 0); ?></p>
                <p><strong>Status:</strong> <span style="background: <?php echo $order['payment_status'] === 'success' ? '#e8f5e9' : '#fff3e0'; ?>; padding: 4px 8px; border-radius: 4px;"><?php echo ucfirst($order['payment_status']); ?></span></p>
            </div>

            <!-- Order Items -->
            <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.1);">
                <h2 style="font-size: 18px; margin-bottom: 16px; font-weight: 600;">Products</h2>
                <table style="width: 100%; font-size: 12px;">
                    <thead>
                        <tr style="background: #f5f5f5;">
                            <th style="padding: 6px; border-bottom: 1px solid #ddd;">Product</th>
                            <th style="padding: 6px; border-bottom: 1px solid #ddd;">Qty</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 6px;"><?php echo htmlspecialchars(substr($item['title'], 0, 25)); ?></td>
                                <td style="padding: 6px;"><?php echo $item['quantity']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <a href="<?php echo SITE_URL; ?>/?page=admin&action=orders" style="display: inline-block; margin-top: 20px; color: var(--primary); text-decoration: none; font-weight: 600;">← Back</a>
    </div>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>
</body>
</html>
