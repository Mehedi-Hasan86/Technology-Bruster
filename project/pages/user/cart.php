<?php
// Loader already included by index.php - no require needed

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . SITE_URL . '/?page=login');
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT c.*, p.title, p.price FROM cart c 
                       JOIN products p ON c.product_id = p.id 
                       WHERE c.user_id = ? ORDER BY c.added_at DESC");

if (!$stmt) {
    die("❌ Query error: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$total = 0;
foreach ($items as $item) {
    $total += $item['price'] * $item['quantity'];
}
$tax = $total * TAX_PERCENTAGE / 100;
$grand_total = $total + $tax;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/public/css/style.css">
</head>
<body>
<?php include INCLUDES_PATH . '/navbar.php'; ?>

<div class="main-content">
    <div style="max-width: 1000px; margin: 0 auto; padding: 0 16px;">
        <h1 style="font-size: 24px; margin-bottom: 24px;">🛒 Shopping Cart</h1>

        <?php if (empty($items)): ?>
            <div style="text-align: center; padding: 40px 0; background: white; border-radius: 8px;">
                <p style="font-size: 16px; margin-bottom: 16px; opacity: 0.7;">Your cart is empty</p>
                <a href="<?php echo SITE_URL; ?>/?page=products" class="btn btn-primary" style="padding: 10px 20px;">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
                <!-- Items -->
                <div style="background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,0.1);">
                    <?php foreach ($items as $item): ?>
                        <div style="display: flex; gap: 16px; padding: 16px; border-bottom: 1px solid #eee; align-items: center;">
                            <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); width: 80px; height: 80px; border-radius: 8px; flex-shrink: 0;"></div>
                            <div style="flex: 1;">
                                <h3 style="font-weight: 600; margin-bottom: 4px;"><?php echo htmlspecialchars($item['title']); ?></h3>
                                <p style="color: var(--primary); font-weight: bold;"><?php echo CURRENCY; ?> <?php echo number_format($item['price'], 0); ?></p>
                            </div>
                            <div style="display: flex; gap: 8px;">
                                <input type="number" value="<?php echo $item['quantity']; ?>" min="1" disabled style="width: 50px; padding: 8px; border: 1px solid #ddd; border-radius: 4px; text-align: center; background: #f5f5f5;">
                                <button onclick="removeFromCart(<?php echo $item['id']; ?>)" class="btn btn-secondary" style="padding: 8px 12px; background: #ffcdd2; color: #c62828; border: none; cursor: pointer;">Remove</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Summary -->
                <div style="background: white; border-radius: 8px; padding: 20px; height: fit-content; box-shadow: 0 1px 4px rgba(0,0,0,0.1);">
                    <h3 style="font-size: 18px; margin-bottom: 16px; font-weight: 600;">Order Summary</h3>
                    
                    <div style="display: flex; justify-content: space-between; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px solid #eee;">
                        <span>Subtotal:</span>
                        <strong><?php echo CURRENCY; ?> <?php echo number_format($total, 0); ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 16px; padding-bottom: 8px; border-bottom: 1px solid #eee;">
                        <span>Tax (<?php echo TAX_PERCENTAGE; ?>%):</span>
                        <strong><?php echo CURRENCY; ?> <?php echo number_format($tax, 0); ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 18px; font-weight: bold; color: var(--primary); padding: 12px 0; border-top: 2px solid #eee; border-bottom: 2px solid #eee; margin-bottom: 16px;">
                        <span>Total:</span>
                        <span><?php echo CURRENCY; ?> <?php echo number_format($grand_total, 0); ?></span>
                    </div>

                    <a href="<?php echo SITE_URL; ?>/?page=checkout" class="btn btn-primary" style="width: 100%; padding: 12px; text-align: center; text-decoration: none; display: block; margin-bottom: 8px; font-weight: 600;">Proceed to Checkout</a>
                    <a href="<?php echo SITE_URL; ?>/?page=products" class="btn btn-secondary" style="width: 100%; padding: 12px; text-align: center; text-decoration: none; display: block; font-weight: 600;">Continue Shopping</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>
<script src="<?php echo SITE_URL; ?>/public/js/main.js"></script>
</body>
</html>
