<?php
// Loader already included by index.php - no require needed

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND is_active = 1");
if (!$stmt) {
    die("❌ Query error");
}

$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    header('Location: ' . SITE_URL . '/?page=products');
    exit;
}

// Get related products - fix for NULL category_id
$related_products = [];
if (!empty($product['category_id'])) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? AND is_active = 1 LIMIT 3");
    if ($stmt) {
        $stmt->bind_param("ii", $product['category_id'], $product_id);
        $stmt->execute();
        $related_products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['title']); ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/public/css/style.css">
</head>
<body>
<?php include INCLUDES_PATH . '/navbar.php'; ?>

<div class="main-content">
    <div style="max-width: 1000px; margin: 0 auto; padding: 0 16px;">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; align-items: start;">
            <!-- Product Image -->
            <div style="background: #f5f5f5; border-radius: 8px; padding: 20px; display: flex; align-items: center; justify-content: center; min-height: 300px;">
                <?php if (!empty($product['image_path']) && file_exists(PRODUCT_IMG_DIR . $product['image_path'])): ?>
                    <img src="<?php echo SITE_URL; ?>/uploads/product_images/<?php echo htmlspecialchars($product['image_path']); ?>" 
                         alt="<?php echo htmlspecialchars($product['title']); ?>"
                         style="width: 100%; max-height: 400px; object-fit: contain; border-radius: 8px;">
                <?php else: ?>
                    <div style="width: 100%; height: 300px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: white; font-size: 48px;">📦</div>
                <?php endif; ?>
            </div>

            <!-- Product Info -->
            <div>
                <h1 style="font-size: 28px; margin-bottom: 12px;"><?php echo htmlspecialchars($product['title']); ?></h1>
                
                <div style="font-size: 24px; color: var(--primary); font-weight: bold; margin-bottom: 20px;">
                    <?php echo CURRENCY; ?> <?php echo number_format($product['price'], 0); ?>
                </div>

                <!-- Stock Status -->
                <div style="background: <?php echo $product['quantity'] > 0 ? '#e8f5e9' : '#ffebee'; ?>; padding: 12px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid <?php echo $product['quantity'] > 0 ? '#2e7d32' : '#c62828'; ?>;">
                    <?php if ($product['quantity'] > 0): ?>
                        <div style="color: #2e7d32; font-weight: 600;">
                            ✅ In Stock - <?php echo $product['quantity']; ?> available
                        </div>
                    <?php else: ?>
                        <div style="color: #c62828; font-weight: 600;">
                            ❌ Out of Stock
                        </div>
                    <?php endif; ?>
                </div>

                <p style="opacity: 0.8; margin-bottom: 20px; line-height: 1.8;">
                    <?php echo nl2br(htmlspecialchars($product['description'])); ?>
                </p>

                <div style="display: grid; gap: 12px; margin-bottom: 24px;">
                    <?php if ($product['quantity'] > 0): ?>
                        <button onclick="buyNow(<?php echo $product['id']; ?>)" class="btn btn-primary" style="width: 100%; padding: 14px; font-size: 16px; font-weight: 600;">Buy Now</button>
                        <button onclick="addToCart(<?php echo $product['id']; ?>)" class="btn btn-secondary" style="width: 100%; padding: 14px; font-size: 16px; font-weight: 600;">Add to Cart</button>
                    <?php else: ?>
                        <button disabled style="width: 100%; padding: 14px; font-size: 16px; font-weight: 600; background: #ccc; color: #666; border: none; border-radius: 8px; cursor: not-allowed;">Out of Stock</button>
                    <?php endif; ?>
                </div>

                <div style="background: var(--light); padding: 16px; border-radius: 8px;">
                    <p style="font-size: 14px;"><strong>📦 Format:</strong> Digital Download</p>
                    <p style="font-size: 14px; margin-top: 8px;"><strong>⏰ Access:</strong> Instant after purchase</p>
                    <p style="font-size: 14px; margin-top: 8px;"><strong>📥 Downloads:</strong> 30 days from purchase</p>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        <?php if (!empty($related_products)): ?>
        <div style="margin-top: 40px;">
            <h2 style="font-size: 20px; margin-bottom: 16px;">Related Products</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px;">
                <?php foreach ($related_products as $rel): ?>
                    <div style="background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,0.1);">
                        <div style="width: 100%; height: 150px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                            <?php if (!empty($rel['image_path']) && file_exists(PRODUCT_IMG_DIR . $rel['image_path'])): ?>
                                <img src="<?php echo SITE_URL; ?>/uploads/product_images/<?php echo htmlspecialchars($rel['image_path']); ?>" 
                                     alt="<?php echo htmlspecialchars($rel['title']); ?>"
                                     style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"></div>
                            <?php endif; ?>
                        </div>
                        <div style="padding: 12px;">
                            <h4 style="font-weight: 600; margin-bottom: 4px; font-size: 14px;"><?php echo htmlspecialchars(substr($rel['title'], 0, 30)); ?></h4>
                            <p style="color: var(--primary); font-weight: bold; margin-bottom: 8px;"><?php echo CURRENCY; ?> <?php echo number_format($rel['price'], 0); ?></p>
                            <div style="display: flex; gap: 4px;">
                                <a href="<?php echo SITE_URL; ?>/?page=product&id=<?php echo $rel['id']; ?>" class="btn btn-primary" style="flex: 1; text-align: center; padding: 6px; font-size: 11px; text-decoration: none;">View</a>
                                <button onclick="addToCart(<?php echo $rel['id']; ?>)" class="btn btn-secondary" style="flex: 1; padding: 6px; font-size: 11px;">Cart</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <a href="<?php echo SITE_URL; ?>/?page=products" style="display: inline-block; margin-top: 20px; color: var(--primary); text-decoration: none; font-weight: 600;">← Back to Products</a>
    </div>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>
<script src="<?php echo SITE_URL; ?>/public/js/main.js"></script>
</body>
</html>
