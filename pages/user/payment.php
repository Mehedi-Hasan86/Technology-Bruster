<?php
// Loader already included by index.php - no require needed

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . SITE_URL . '/?page=login');
    exit;
}

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT o.*, GROUP_CONCAT(oi.product_id) as product_ids FROM orders o 
                       LEFT JOIN order_items oi ON o.id = oi.order_id 
                       WHERE o.id = ? AND o.user_id = ? GROUP BY o.id");
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

// Get order items
$items_stmt = $conn->prepare("SELECT oi.*, p.title FROM order_items oi 
                             JOIN products p ON oi.product_id = p.id 
                             WHERE oi.order_id = ?");
$items_stmt->bind_param("i", $order_id);
$items_stmt->execute();
$order_items = $items_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$items_stmt->close();

$payment_error = '';
$payment_success = '';

// Handle payment confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['process_payment'])) {
    $payment_method = $_POST['payment_method'] ?? '';
    
    if (empty($payment_method)) {
        $payment_error = 'Please select a payment method';
    } else {
        // Generate transaction ID
        $transaction_id = strtoupper(substr($payment_method, 0, 3)) . '-' . time() . '-' . rand(1000, 9999);
        
        // Update order with payment status
        $update_stmt = $conn->prepare("UPDATE orders SET payment_status = 'success', transaction_id = ?, status = 'completed', payment_method = ? WHERE id = ?");
        if ($update_stmt) {
            $update_stmt->bind_param("ssi", $transaction_id, $payment_method, $order_id);
            if ($update_stmt->execute()) {
                // Create download tokens for all products in order
                $items_result = $conn->query("SELECT DISTINCT product_id FROM order_items WHERE order_id = $order_id");
                if ($items_result) {
                    while ($item = $items_result->fetch_assoc()) {
                        $token = bin2hex(random_bytes(32));
                        $expires = date('Y-m-d H:i:s', time() + (30 * 24 * 3600)); // 30 days
                        
                        $token_stmt = $conn->prepare("INSERT INTO download_tokens (user_id, product_id, order_id, token, expires_at) 
                                                     VALUES (?, ?, ?, ?, ?)");
                        if ($token_stmt) {
                            $token_stmt->bind_param("iiiis", $user_id, $item['product_id'], $order_id, $token, $expires);
                            $token_stmt->execute();
                            $token_stmt->close();
                        }
                    }
                }
                
                $payment_success = '✅ Payment processed successfully!';
                $_SESSION['payment_success'] = true;
                
                // Redirect after 2 seconds
                header('Refresh: 2; url=' . SITE_URL . '/?page=order-detail&id=' . $order_id);
            } else {
                $payment_error = 'Error processing payment. Please try again.';
            }
            $update_stmt->close();
        } else {
            $payment_error = 'Database error. Please try again.';
        }
    }
}
?>
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/public/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
<?php include INCLUDES_PATH . '/navbar.php'; ?>

<div class="main-content">
    <div style="max-width: 900px; margin: 0 auto; padding: 0 16px;">
        <h1 style="font-size: 28px; margin-bottom: 24px;">💳 Payment</h1>

        <?php if ($payment_error): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 12px 16px; border-radius: 4px; margin-bottom: 16px;">
                <?php echo $payment_error; ?>
            </div>
        <?php endif; ?>

        <?php if ($payment_success): ?>
            <div style="background: #d4edda; color: #155724; padding: 12px 16px; border-radius: 4px; margin-bottom: 16px;">
                <?php echo $payment_success; ?>
            </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
            <!-- Payment Method Selection -->
            <div style="background: white; padding: 24px; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.1);">
                <h2 style="font-size: 20px; margin-bottom: 20px; font-weight: 600;">Select Payment Method</h2>

                <form method="POST" id="payment-form">
                    <!-- bKash Payment -->
                    <label style="display: block; padding: 16px; border: 2px solid #ddd; border-radius: 8px; margin-bottom: 12px; cursor: pointer; transition: all 0.3s;">
                        <input type="radio" name="payment_method" value="bkash" required onchange="updatePaymentInfo(this)">
                        <div style="display: inline-block; vertical-align: middle; margin-left: 12px; flex: 1;">
                            <div style="font-weight: 600; font-size: 16px;">📱 bKash</div>
                            <div style="font-size: 13px; opacity: 0.7; margin-top: 4px;">Instant mobile money transfer</div>
                        </div>
                    </label>

                    <!-- SSLCommerz Payment -->
                    <label style="display: block; padding: 16px; border: 2px solid #ddd; border-radius: 8px; margin-bottom: 12px; cursor: pointer; transition: all 0.3s;">
                        <input type="radio" name="payment_method" value="ssl" onchange="updatePaymentInfo(this)">
                        <div style="display: inline-block; vertical-align: middle; margin-left: 12px; flex: 1;">
                            <div style="font-weight: 600; font-size: 16px;">🏦 SSLCommerz</div>
                            <div style="font-size: 13px; opacity: 0.7; margin-top: 4px;">Credit card, Debit card, Mobile banking</div>
                        </div>
                    </label>

                    <!-- Bank Transfer -->
                    <label style="display: block; padding: 16px; border: 2px solid #ddd; border-radius: 8px; margin-bottom: 12px; cursor: pointer; transition: all 0.3s;">
                        <input type="radio" name="payment_method" value="bank" onchange="updatePaymentInfo(this)">
                        <div style="display: inline-block; vertical-align: middle; margin-left: 12px; flex: 1;">
                            <div style="font-weight: 600; font-size: 16px;">🏧 Bank Transfer</div>
                            <div style="font-size: 13px; opacity: 0.7; margin-top: 4px;">Direct bank transfer</div>
                        </div>
                    </label>

                    <!-- Payment Info -->
                    <div id="payment-info" style="background: #e3f2fd; padding: 16px; border-radius: 8px; margin-bottom: 24px; display: none;">
                        <h3 style="margin-bottom: 12px; font-weight: 600;"><i class="fas fa-info-circle"></i> Payment Instructions</h3>
                        <div id="payment-instructions"></div>
                    </div>

                    <button type="submit" name="process_payment" style="width: 100%; padding: 14px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; font-size: 16px;">
                        <i class="fas fa-lock"></i> Confirm Payment
                    </button>
                </form>
            </div>

            <!-- Order Summary -->
            <div>
                <!-- Order Details -->
                <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.1); margin-bottom: 16px;">
                    <h3 style="font-size: 16px; margin-bottom: 12px; font-weight: 600;">📦 Order Details</h3>
                    
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px; padding-bottom: 8px; border-bottom: 1px solid #eee;">
                        <span>Order Number:</span>
                        <strong><?php echo htmlspecialchars($order['order_number']); ?></strong>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px; padding-bottom: 8px; border-bottom: 1px solid #eee;">
                        <span>Date:</span>
                        <strong><?php echo date('M d, Y', strtotime($order['created_at'])); ?></strong>
                    </div>

                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px; padding-bottom: 8px; border-bottom: 1px solid #eee;">
                        <span>Items:</span>
                        <strong><?php echo count($order_items); ?> product<?php echo count($order_items) !== 1 ? 's' : ''; ?></strong>
                    </div>

                    <div style="display: flex; justify-content: space-between; padding-top: 8px;">
                        <span style="opacity: 0.7;">Status:</span>
                        <span style="background: #fff3cd; color: #856404; padding: 2px 8px; border-radius: 3px; font-size: 12px; font-weight: 600;">Pending Payment</span>
                    </div>
                </div>

                <!-- Order Items -->
                <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.1); margin-bottom: 16px;">
                    <h3 style="font-size: 16px; margin-bottom: 12px; font-weight: 600;">📋 Items</h3>
                    
                    <div style="max-height: 200px; overflow-y: auto;">
                        <?php foreach ($order_items as $item): ?>
                            <div style="padding: 10px 0; border-bottom: 1px solid #eee; display: flex; justify-content: space-between;">
                                <span style="font-size: 14px;"><?php echo htmlspecialchars($item['title']); ?></span>
                                <span style="font-weight: 600;">x<?php echo $item['quantity']; ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Amount Summary -->
                <div style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.1);">
                    <h3 style="font-size: 16px; margin-bottom: 12px; font-weight: 600;">💰 Amount</h3>
                    
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px; padding-bottom: 8px; border-bottom: 1px solid #eee;">
                        <span>Subtotal:</span>
                        <strong><?php echo CURRENCY; ?> <?php echo number_format($order['total_amount'] - $order['tax_amount'], 0); ?></strong>
                    </div>

                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px; padding-bottom: 8px; border-bottom: 1px solid #eee;">
                        <span>Tax:</span>
                        <strong><?php echo CURRENCY; ?> <?php echo number_format($order['tax_amount'], 0); ?></strong>
                    </div>

                    <?php if ($order['discount_amount'] > 0): ?>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 10px; padding-bottom: 8px; border-bottom: 1px solid #eee; color: #28a745;">
                            <span>Discount:</span>
                            <strong>-<?php echo CURRENCY; ?> <?php echo number_format($order['discount_amount'], 0); ?></strong>
                        </div>
                    <?php endif; ?>

                    <div style="display: flex; justify-content: space-between; padding-top: 10px; font-size: 18px; font-weight: bold; color: var(--primary);">
                        <span>Total:</span>
                        <span><?php echo CURRENCY; ?> <?php echo number_format($order['total_amount'], 0); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>

<script>
    const paymentMethods = {
        bkash: {
            title: '📱 bKash Payment',
            instructions: `
                <ol style="margin: 0; padding-left: 20px;">
                    <li>Open bKash app or dial <strong>*247#</strong></li>
                    <li>Select <strong>Send Money</strong></li>
                    <li>Enter merchant account number</li>
                    <li>Enter amount: <strong><?php echo CURRENCY; ?> <?php echo number_format($order['total_amount'], 0); ?></strong></li>
                    <li>Enter your PIN and confirm</li>
                    <li>You will receive a confirmation SMS</li>
                    <li>Click the button to complete</li>
                </ol>
                <p style="margin-top: 12px; font-size: 13px; background: #fff9c4; padding: 8px; border-radius: 4px;">💡 <strong>Note:</strong> Please complete the bKash transaction before submitting.</p>
            `
        },
        ssl: {
            title: '🏦 SSLCommerz Secure Payment',
            instructions: `
                <p>Click the button to proceed to SSLCommerz secure payment gateway where you can pay using:</p>
                <ul style="margin: 12px 0; padding-left: 20px;">
                    <li>💳 Credit Card (Visa, MasterCard, Amex)</li>
                    <li>🏦 Debit Card</li>
                    <li>📱 Mobile Banking (Nagad, Rocket)</li>
                </ul>
                <p style="font-size: 13px; background: #e8f5e9; padding: 8px; border-radius: 4px;">🔒 <strong>Secure:</strong> Your payment information is encrypted and secure.</p>
            `
        },
        bank: {
            title: '🏧 Bank Transfer',
            instructions: `
                <p><strong>Bank Transfer Details:</strong></p>
                <p style="background: #f5f5f5; padding: 12px; border-radius: 4px; margin: 12px 0;">
                    Bank Name: Example Bank<br>
                    Account Number: 1234567890<br>
                    Account Holder: Technology Bruster<br>
                    SWIFT Code: EXBLBD
                </p>
                <p><strong>Amount to Transfer:</strong> <?php echo CURRENCY; ?> <?php echo number_format($order['total_amount'], 0); ?></p>
                <p style="font-size: 13px; background: #fff9c4; padding: 8px; border-radius: 4px;">⏱️ <strong>Note:</strong> Bank transfers may take 1-2 business days to process. Click the button once you've initiated the transfer.</p>
            `
        }
    };

    function updatePaymentInfo(radio) {
        const method = radio.value;
        const infoDiv = document.getElementById('payment-info');
        const instructionsDiv = document.getElementById('payment-instructions');
        
        if (paymentMethods[method]) {
            instructionsDiv.innerHTML = paymentMethods[method].instructions;
            infoDiv.style.display = 'block';
        }

        // Update label styles
        document.querySelectorAll('label').forEach(label => {
            if (label.querySelector('input[name="payment_method"]')) {
                label.style.borderColor = '#ddd';
                label.style.background = 'white';
            }
        });
        radio.parentElement.style.borderColor = 'var(--primary)';
        radio.parentElement.style.background = '#f5f5f5';
    }
</script>

</body>
</html>
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
