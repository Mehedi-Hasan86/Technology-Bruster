<?php
// Loader already included by index.php - no require needed

if (!isset($_SESSION['admin_id'])) {
    header('Location: ' . SITE_URL . '/?page=admin');
    exit;
}

// Get statistics
$users_result = $conn->query("SELECT COUNT(*) as count FROM users");
$total_users = $users_result ? $users_result->fetch_assoc()['count'] : 0;

$products_result = $conn->query("SELECT COUNT(*) as count FROM products");
$total_products = $products_result ? $products_result->fetch_assoc()['count'] : 0;

$orders_result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE payment_status = 'success'");
$total_orders = $orders_result ? $orders_result->fetch_assoc()['count'] : 0;

$revenue_result = $conn->query("SELECT SUM(total_amount) as sum FROM orders WHERE payment_status = 'success'");
$total_revenue = $revenue_result ? ($revenue_result->fetch_assoc()['sum'] ?? 0) : 0;

// Recent orders
$recent = $conn->query("SELECT o.*, u.name FROM orders o JOIN users u ON o.user_id = u.id WHERE o.payment_status = 'success' ORDER BY o.created_at DESC LIMIT 5");
$recent_orders = $recent ? $recent->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/public/css/style.css">
</head>
<body>
<?php include INCLUDES_PATH . '/admin-navbar.php'; ?>

<div style="display: flex; gap: 24px; padding: 24px;">
    <?php include INCLUDES_PATH . '/admin-sidebar.php'; ?>

    <div style="flex: 1; max-width: 1200px;">
        <h1 style="font-size: 28px; margin-bottom: 24px;">📊 Dashboard</h1>

        <!-- Stats Cards -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px; margin-bottom: 32px;">
            <div style="background: white; padding: 24px; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.1); border-left: 4px solid #007bff;">
                <div style="font-size: 14px; opacity: 0.7; margin-bottom: 8px;">Total Users</div>
                <div style="font-size: 32px; font-weight: bold; color: #007bff;"><?php echo number_format($total_users); ?></div>
            </div>

            <div style="background: white; padding: 24px; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.1); border-left: 4px solid #28a745;">
                <div style="font-size: 14px; opacity: 0.7; margin-bottom: 8px;">Total Products</div>
                <div style="font-size: 32px; font-weight: bold; color: #28a745;"><?php echo number_format($total_products); ?></div>
            </div>

            <div style="background: white; padding: 24px; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.1); border-left: 4px solid #ffc107;">
                <div style="font-size: 14px; opacity: 0.7; margin-bottom: 8px;">Total Orders</div>
                <div style="font-size: 32px; font-weight: bold; color: #ffc107;"><?php echo number_format($total_orders); ?></div>
            </div>

            <div style="background: white; padding: 24px; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.1); border-left: 4px solid #dc3545;">
                <div style="font-size: 14px; opacity: 0.7; margin-bottom: 8px;">Total Revenue</div>
                <div style="font-size: 32px; font-weight: bold; color: #dc3545;"><?php echo CURRENCY; ?> <?php echo number_format($total_revenue, 0); ?></div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div style="background: white; padding: 24px; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.1);">
            <h2 style="font-size: 20px; margin-bottom: 16px; font-weight: 600;">Recent Orders</h2>
            
            <?php if (empty($recent_orders)): ?>
                <p style="opacity: 0.7; text-align: center; padding: 20px;">No orders yet</p>
            <?php else: ?>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f5f5f5;">
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Order</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Customer</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Amount</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Date</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_orders as $order): ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 12px;"><strong><?php echo htmlspecialchars($order['order_number']); ?></strong></td>
                                <td style="padding: 12px;"><?php echo htmlspecialchars($order['name']); ?></td>
                                <td style="padding: 12px; color: var(--primary); font-weight: bold;"><?php echo CURRENCY; ?> <?php echo number_format($order['total_amount'], 0); ?></td>
                                <td style="padding: 12px; font-size: 12px; opacity: 0.7;"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                <td style="padding: 12px;">
                                    <a href="<?php echo SITE_URL; ?>/?page=admin&action=order_detail&id=<?php echo $order['id']; ?>" style="color: var(--primary); text-decoration: none; font-size: 12px; font-weight: 600;">View</a>
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
