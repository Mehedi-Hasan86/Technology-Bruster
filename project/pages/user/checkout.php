<?php
// Loader already included by index.php - no require needed

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . SITE_URL . '/?page=login');
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Get cart items - Debug version
$stmt = $conn->prepare("SELECT c.id, c.user_id, c.product_id, c.quantity, p.title, p.price 
                       FROM cart c 
                       JOIN products p ON c.product_id = p.id 
                       WHERE c.user_id = ? 
                       ORDER BY c.added_at DESC");

if (!$stmt) {
    die("❌ Query error: " . $conn->error);
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$cart_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$tax_amount = $subtotal * TAX_PERCENTAGE / 100;
$total = $subtotal + $tax_amount;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/public/css/style.css">
</head>
<body>
<?php include INCLUDES_PATH . '/navbar.php'; ?>

<div class="main-content">
    <div style="max-width: 900px; margin: 0 auto; padding: 0 16px;">
        <h1 style="font-size: 24px; margin-bottom: 24px;">🛒 Checkout</h1>

        <?php if (empty($cart_items)): ?>
            <div style="text-align: center; padding: 40px 0; background: white; border-radius: 8px;">
                <p style="font-size: 16px; margin-bottom: 16px; opacity: 0.7;">Your cart is empty</p>
                <a href="<?php echo SITE_URL; ?>/?page=products" class="btn btn-primary" style="padding: 10px 20px;">Continue Shopping</a>
            </div>
        <?php else: ?>
            <form method="POST" action="<?php echo SITE_URL; ?>/api/orders.php" style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
                <input type="hidden" name="action" value="place_order">
                <input type="hidden" name="coupon_code" id="coupon_code_input" value="">

                <!-- Left: Order Summary -->
                <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.1);">
                    <h2 style="font-size: 18px; margin-bottom: 16px; font-weight: 600;">Order Summary</h2>
                    
                    <div style="max-height: 300px; overflow-y: auto; margin-bottom: 16px;">
                        <?php foreach ($cart_items as $item): ?>
                            <div style="display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #eee;">
                                <div>
                                    <div style="font-weight: 600; font-size: 14px;"><?php echo htmlspecialchars($item['title']); ?></div>
                                    <div style="font-size: 12px; opacity: 0.7;">Qty: <?php echo $item['quantity']; ?></div>
                                </div>
                                <div style="font-weight: 600;"><?php echo CURRENCY; ?> <?php echo number_format($item['price'] * $item['quantity'], 0); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div style="background: #f5f5f5; padding: 12px; border-radius: 8px;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px;">
                            <span>Subtotal:</span>
                            <strong id="subtotal-display"><?php echo CURRENCY; ?> <?php echo number_format($subtotal, 0); ?></strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px;">
                            <span>Tax (<?php echo TAX_PERCENTAGE; ?>%):</span>
                            <strong id="tax-display"><?php echo CURRENCY; ?> <?php echo number_format($tax_amount, 0); ?></strong>
                        </div>
                        <div id="discount-row" style="display: none; justify-content: space-between; margin-bottom: 8px; font-size: 14px; color: var(--success);">
                            <span>Discount:</span>
                            <strong id="discount-display">-<?php echo CURRENCY; ?> 0</strong>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 18px; font-weight: bold; color: var(--primary); padding-top: 8px; border-top: 1px solid #ddd;">
                            <span>Total:</span>
                            <span id="total-price"><?php echo CURRENCY; ?> <?php echo number_format($total, 0); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Right: Payment Options -->
                <div>
                    <!-- Coupon Section -->
                    <div style="background: white; padding: 20px; border-radius: 8px; margin-bottom: 16px; box-shadow: 0 1px 4px rgba(0,0,0,0.1);">
                        <h3 style="font-size: 16px; margin-bottom: 12px; font-weight: 600;">
                            <i class="fas fa-tag"></i> Apply Coupon Code
                        </h3>
                        
                        <div id="coupon-applied" style="display: none; background: #e8f5e9; padding: 12px; border-radius: 8px; margin-bottom: 12px;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <p style="margin: 0; color: #2e7d32; font-weight: 600;">
                                        <i class="fas fa-check-circle"></i> <span id="applied-code"></span>
                                    </p>
                                    <p style="margin: 4px 0 0 0; font-size: 12px; color: #2e7d32; opacity: 0.8;">
                                        Discount: <strong id="applied-discount"></strong>
                                    </p>
                                </div>
                                <button type="button" onclick="removeCoupon()" style="background: none; border: none; color: #2e7d32; cursor: pointer; font-size: 1.2rem;">
                                    <i class="fas fa-times-circle"></i>
                                </button>
                            </div>
                        </div>

                        <div id="coupon-form" style="display: block;">
                            <div style="display: flex; gap: 8px; margin-bottom: 12px;">
                                <input type="text" id="coupon-input" placeholder="Enter coupon code" 
                                       style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px;">
                                <button type="button" onclick="applyCoupon()" class="btn btn-secondary" 
                                        style="padding: 10px 20px; white-space: nowrap; background: var(--primary); color: white; border: none; cursor: pointer;">
                                    <i class="fas fa-check"></i> Apply
                                </button>
                            </div>
                        </div>

                        <div id="coupon-msg" style="font-size: 12px; padding: 8px; border-radius: 6px; display: none;"></div>
                    </div>

                    <!-- Payment Method -->
                    <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.1);">
                        <h3 style="font-size: 16px; margin-bottom: 12px; font-weight: 600;">Payment Method</h3>
                        
                        <label style="display: block; padding: 12px; border: 2px solid #ddd; border-radius: 6px; margin-bottom: 8px; cursor: pointer; transition: all 0.3s;">
                            <input type="radio" name="payment_method" value="bkash" required onchange="updatePaymentUI(this)">
                            <span style="margin-left: 8px; font-weight: 600;">📱 bKash</span>
                        </label>

                        <label style="display: block; padding: 12px; border: 2px solid #ddd; border-radius: 6px; margin-bottom: 8px; cursor: pointer; transition: all 0.3s;">
                            <input type="radio" name="payment_method" value="ssl" onchange="updatePaymentUI(this)">
                            <span style="margin-left: 8px; font-weight: 600;">🏦 SSLCommerz</span>
                        </label>

                        <label style="display: block; padding: 12px; border: 2px solid #ddd; border-radius: 6px; cursor: pointer; transition: all 0.3s;">
                            <input type="radio" name="payment_method" value="bank" onchange="updatePaymentUI(this)">
                            <span style="margin-left: 8px; font-weight: 600;">🏧 Bank Transfer</span>
                        </label>

                        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; margin-top: 16px; font-weight: 600; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 16px;">
                            <i class="fas fa-credit-card"></i> Place Order
                        </button>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>

<script>
    const SITE_URL = '<?php echo SITE_URL; ?>';
    const SUBTOTAL = <?php echo $subtotal ?? 0; ?>;
    const TAX_PERCENTAGE = <?php echo TAX_PERCENTAGE; ?>;
    const CURRENCY = '<?php echo CURRENCY; ?>';
    let appliedCoupon = null;
    let discountAmount = 0;

    function applyCoupon() {
        const code = document.getElementById('coupon-input').value.trim().toUpperCase();
        const msgDiv = document.getElementById('coupon-msg');
        
        if (!code) {
            msgDiv.innerHTML = '❌ Please enter a coupon code';
            msgDiv.style.background = '#ffebee';
            msgDiv.style.color = '#c62828';
            msgDiv.style.display = 'block';
            return;
        }

        // Show loading
        msgDiv.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Validating...';
        msgDiv.style.background = '#e3f2fd';
        msgDiv.style.color = '#1976d2';
        msgDiv.style.display = 'block';

        const formData = new FormData();
        formData.append('action', 'validate');
        formData.append('code', code);
        formData.append('subtotal', SUBTOTAL);

        fetch(SITE_URL + '/api/coupon.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                appliedCoupon = data.coupon_code;
                discountAmount = data.discount_amount;
                
                // Update UI
                document.getElementById('coupon-form').style.display = 'none';
                document.getElementById('coupon-applied').style.display = 'block';
                document.getElementById('applied-code').textContent = data.coupon_code;
                document.getElementById('applied-discount').textContent = CURRENCY + ' ' + data.discount;
                
                // Update totals
                updateTotals();
                
                msgDiv.style.display = 'none';
                document.getElementById('coupon_code_input').value = data.coupon_code;
            } else {
                msgDiv.innerHTML = data.message;
                msgDiv.style.background = '#ffebee';
                msgDiv.style.color = '#c62828';
                msgDiv.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            msgDiv.innerHTML = '❌ Network error. Please try again.';
            msgDiv.style.background = '#ffebee';
            msgDiv.style.color = '#c62828';
            msgDiv.style.display = 'block';
        });
    }

    function removeCoupon() {
        appliedCoupon = null;
        discountAmount = 0;
        
        document.getElementById('coupon-input').value = '';
        document.getElementById('coupon-applied').style.display = 'none';
        document.getElementById('coupon-form').style.display = 'block';
        document.getElementById('coupon-msg').style.display = 'none';
        document.getElementById('coupon_code_input').value = '';
        
        updateTotals();
    }

    function updateTotals() {
        const tax = SUBTOTAL * TAX_PERCENTAGE / 100;
        const total = SUBTOTAL + tax - discountAmount;
        
        document.getElementById('subtotal-display').textContent = CURRENCY + ' ' + formatNumber(SUBTOTAL);
        document.getElementById('tax-display').textContent = CURRENCY + ' ' + formatNumber(tax);
        document.getElementById('total-price').textContent = CURRENCY + ' ' + formatNumber(total);
        
        if (discountAmount > 0) {
            document.getElementById('discount-row').style.display = 'flex';
            document.getElementById('discount-display').textContent = '-' + CURRENCY + ' ' + formatNumber(discountAmount);
        } else {
            document.getElementById('discount-row').style.display = 'none';
        }
    }

    function formatNumber(num) {
        return Math.round(num).toLocaleString();
    }

    function updatePaymentUI(element) {
        document.querySelectorAll('label').forEach(label => {
            if (label.querySelector('input[name="payment_method"]')) {
                label.style.borderColor = '#ddd';
                label.style.background = 'white';
            }
        });
        element.parentElement.style.borderColor = 'var(--primary)';
        element.parentElement.style.background = '#f5f5f5';
    }

    // Allow Enter key to apply coupon
    document.getElementById('coupon-input')?.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            applyCoupon();
        }
    });
</script>

</body>
</html>