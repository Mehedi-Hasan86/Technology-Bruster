<?php
// Loader already included by index.php - no require needed

if (!isset($_SESSION['admin_id'])) {
    header('Location: ' . SITE_URL . '/?page=admin');
    exit;
}

// Daily sales
$daily = $conn->query("SELECT DATE(created_at) as date, COUNT(*) as orders, SUM(total_amount) as revenue 
                      FROM orders WHERE payment_status = 'success'
                      GROUP BY DATE(created_at) ORDER BY date DESC LIMIT 30");
$daily_sales = $daily ? $daily->fetch_all(MYSQLI_ASSOC) : [];

// Top products
$top = $conn->query("SELECT p.title, COUNT(oi.id) as sales, SUM(oi.price * oi.quantity) as revenue
                    FROM order_items oi JOIN products p ON oi.product_id = p.id
                    GROUP BY oi.product_id ORDER BY sales DESC LIMIT 10");
$top_products = $top ? $top->fetch_all(MYSQLI_ASSOC) : [];

// Tax summary
$tax_result = $conn->query("SELECT SUM(tax_amount) as total_tax FROM orders WHERE payment_status = 'success'");
$total_tax = $tax_result ? ($tax_result->fetch_assoc()['total_tax'] ?? 0) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Admin</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/public/css/style.css">
</head>
<body>
<?php include INCLUDES_PATH . '/admin-navbar.php'; ?>

<div style="display: flex; gap: 24px; padding: 24px;">
    <?php include INCLUDES_PATH . '/admin-sidebar.php'; ?>

    <div style="flex: 1; max-width: 1000px;">
        <h1 style="font-size: 28px; margin-bottom: 24px;">📈 Reports & Analytics</h1>

        <!-- Tax Summary -->
        <div style="background: white; padding: 24px; border-radius: 8px; margin-bottom: 24px; box-shadow: 0 1px 4px rgba(0,0,0,0.1);">
            <h2 style="font-size: 20px; margin-bottom: 16px; font-weight: 600;">Tax Collection Summary</h2>
            <div style="font-size: 28px; color: var(--primary); font-weight: bold;">
                <?php echo CURRENCY; ?> <?php echo number_format($total_tax, 0); ?>
            </div>
        </div>

        <!-- Daily Sales -->
        <div style="background: white; padding: 24px; border-radius: 8px; margin-bottom: 24px; box-shadow: 0 1px 4px rgba(0,0,0,0.1);">
            <h2 style="font-size: 20px; margin-bottom: 16px; font-weight: 600;">Daily Sales (Last 30 Days)</h2>
            
            <?php if (empty($daily_sales)): ?>
                <p style="opacity: 0.7; text-align: center; padding: 20px;">No sales data</p>
            <?php else: ?>
                <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                    <thead>
                        <tr style="background: #f5f5f5;">
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Date</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Orders</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($daily_sales as $d): ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 12px;"><?php echo date('M d, Y', strtotime($d['date'])); ?></td>
                                <td style="padding: 12px;"><?php echo $d['orders']; ?></td>
                                <td style="padding: 12px; font-weight: 600;"><?php echo CURRENCY; ?> <?php echo number_format($d['revenue'], 0); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Top Products -->
        <div style="background: white; padding: 24px; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.1);">
            <h2 style="font-size: 20px; margin-bottom: 16px; font-weight: 600;">Top Selling Products</h2>
            
            <?php if (empty($top_products)): ?>
                <p style="opacity: 0.7; text-align: center; padding: 20px;">No sales data</p>
            <?php else: ?>
                <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
                    <thead>
                        <tr style="background: #f5f5f5;">
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Product</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Sales</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($top_products as $p): ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 12px;"><?php echo htmlspecialchars(substr($p['title'], 0, 50)); ?></td>
                                <td style="padding: 12px;"><?php echo $p['sales']; ?></td>
                                <td style="padding: 12px; font-weight: 600;"><?php echo CURRENCY; ?> <?php echo number_format($p['revenue'], 0); ?></td>
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
