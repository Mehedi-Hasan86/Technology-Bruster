<?php
// Loader already included by index.php - no require needed

if (!isset($_SESSION['admin_id'])) {
    header('Location: ' . SITE_URL . '/?page=admin');
    exit;
}

$filter = $_GET['filter'] ?? '';

$query = "SELECT o.*, u.name, u.email FROM orders o JOIN users u ON o.user_id = u.id";

if ($filter && in_array($filter, ['pending', 'success', 'failed'])) {
    $query .= " WHERE o.payment_status = '$filter'";
}

$query .= " ORDER BY o.created_at DESC";

$orders = $conn->query($query);
$orders = $orders ? $orders->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - Admin</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/public/css/style.css">
</head>
<body>
<?php include INCLUDES_PATH . '/admin-navbar.php'; ?>

<div style="display: flex; gap: 24px; padding: 24px;">
    <?php include INCLUDES_PATH . '/admin-sidebar.php'; ?>

    <div style="flex: 1; max-width: 1000px;">
        <h1 style="font-size: 28px; margin-bottom: 24px;">📋 Manage Orders</h1>

        <!-- Filters -->
        <div style="display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap;">
            <a href="<?php echo SITE_URL; ?>/?page=admin&action=orders" class="btn btn-secondary" style="padding: 10px 16px; background: <?php echo !$filter ? '#007bff' : '#ccc'; ?>; color: white; text-decoration: none;">All</a>
            <a href="<?php echo SITE_URL; ?>/?page=admin&action=orders&filter=pending" class="btn btn-secondary" style="padding: 10px 16px; background: <?php echo $filter === 'pending' ? '#ffc107' : '#ccc'; ?>; color: white; text-decoration: none;">Pending</a>
            <a href="<?php echo SITE_URL; ?>/?page=admin&action=orders&filter=success" class="btn btn-secondary" style="padding: 10px 16px; background: <?php echo $filter === 'success' ? '#28a745' : '#ccc'; ?>; color: white; text-decoration: none;">Success</a>
            <a href="<?php echo SITE_URL; ?>/?page=admin&action=orders&filter=failed" class="btn btn-secondary" style="padding: 10px 16px; background: <?php echo $filter === 'failed' ? '#dc3545' : '#ccc'; ?>; color: white; text-decoration: none;">Failed</a>
        </div>

        <!-- Orders Table -->
        <div style="background: white; padding: 24px; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.1);">
            <?php if (empty($orders)): ?>
                <p style="opacity: 0.7; text-align: center; padding: 20px;">No orders found</p>
            <?php else: ?>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f5f5f5;">
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Order</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Customer</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Amount</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Status</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Date</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $o): ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 12px;"><strong><?php echo htmlspecialchars($o['order_number']); ?></strong></td>
                                <td style="padding: 12px;"><?php echo htmlspecialchars($o['name']); ?></td>
                                <td style="padding: 12px; font-weight: 600;"><?php echo CURRENCY; ?> <?php echo number_format($o['total_amount'], 0); ?></td>
                                <td style="padding: 12px;">
                                    <span style="background: <?php 
                                        $bg_map = ['success' => '#e8f5e9', 'pending' => '#fff3e0', 'failed' => '#ffebee'];
                                        echo $bg_map[$o['payment_status']] ?? '#f5f5f5';
                                    ?>; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                        <?php echo ucfirst($o['payment_status']); ?>
                                    </span>
                                </td>
                                <td style="padding: 12px; font-size: 12px; opacity: 0.7;"><?php echo date('M d, Y', strtotime($o['created_at'])); ?></td>
                                <td style="padding: 12px;">
                                    <a href="<?php echo SITE_URL; ?>/?page=admin&action=order_detail&id=<?php echo $o['id']; ?>" style="color: var(--primary); text-decoration: none; font-size: 12px; font-weight: 600;">View</a>
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
