<?php
require_once dirname(__DIR__) . '/loader.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action === 'validate') {
    $code = strtoupper(trim($_POST['code'] ?? ''));
    $subtotal = (float)($_POST['subtotal'] ?? 0);

    if (empty($code)) {
        echo json_encode(['success' => false, 'message' => 'Please enter a coupon code']);
        exit;
    }

    // Check if coupon exists and is valid
    $stmt = $conn->prepare("SELECT * FROM coupons WHERE code = ? AND is_active = 1 
                           AND (expires_at IS NULL OR expires_at > NOW()) 
                           AND current_usage < max_usage");
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
        exit;
    }

    $stmt->bind_param("s", $code);
    $stmt->execute();
    $coupon = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$coupon) {
        echo json_encode(['success' => false, 'message' => '❌ Invalid or expired coupon code']);
        exit;
    }

    // Calculate discount
    $tax = $subtotal * TAX_PERCENTAGE / 100;
    $discount = 0;

    if ($coupon['discount_type'] === 'flat') {
        $discount = min($coupon['discount_value'], $subtotal + $tax);
    } else {
        $discount = ($subtotal + $tax) * $coupon['discount_value'] / 100;
    }

    $new_total = ($subtotal + $tax) - $discount;

    echo json_encode([
        'success' => true,
        'message' => '✅ Coupon applied successfully!',
        'discount' => number_format($discount, 0),
        'discount_amount' => $discount,
        'new_total' => number_format($new_total, 0),
        'coupon_id' => $coupon['id'],
        'coupon_code' => $coupon['code']
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
