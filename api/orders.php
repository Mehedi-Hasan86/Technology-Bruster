<?php
require_once dirname(__DIR__) . '/loader.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

if ($action === 'place_order') {
    $payment_method = $_POST['payment_method'] ?? '';
    $coupon_code = strtoupper(trim($_POST['coupon_code'] ?? ''));

    if (empty($payment_method)) {
        echo json_encode(['success' => false, 'message' => 'Select payment method']);
        exit;
    }

    // Get cart items with proper error handling
    $cart_query = "SELECT c.id, c.user_id, c.product_id, c.quantity, p.price, p.title
                   FROM cart c 
                   JOIN products p ON c.product_id = p.id 
                   WHERE c.user_id = ? AND c.quantity > 0";
    
    $stmt = $conn->prepare($cart_query);
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        exit;
    }

    $stmt->bind_param("i", $user_id);
    
    if (!$stmt->execute()) {
        echo json_encode(['success' => false, 'message' => 'Query failed: ' . $stmt->error]);
        exit;
    }

    $result = $stmt->get_result();
    $cart_items = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // Debug: Check if cart is empty
    if (empty($cart_items)) {
        // Try fallback query without join to see if issue is with products
        $fallback = $conn->query("SELECT * FROM cart WHERE user_id = $user_id");
        $fallback_items = $fallback ? $fallback->fetch_all(MYSQLI_ASSOC) : [];
        
        echo json_encode([
            'success' => false, 
            'message' => 'Cart is empty',
            'debug' => [
                'user_id' => $user_id,
                'cart_count' => count($fallback_items),
                'error' => 'Cart items not found or products missing'
            ]
        ]);
        exit;
    }

    $subtotal = 0;
    foreach ($cart_items as $item) {
        $subtotal += $item['price'] * $item['quantity'];
    }

    $tax_amount = $subtotal * TAX_PERCENTAGE / 100;
    $discount_amount = 0;
    $coupon_id = null;

    // Apply coupon if provided
    if (!empty($coupon_code)) {
        $coupon_stmt = $conn->prepare("SELECT * FROM coupons WHERE code = ? AND is_active = 1 
                                       AND (expires_at IS NULL OR expires_at > NOW()) 
                                       AND current_usage < max_usage");
        if ($coupon_stmt) {
            $coupon_stmt->bind_param("s", $coupon_code);
            $coupon_stmt->execute();
            $coupon = $coupon_stmt->get_result()->fetch_assoc();

            if ($coupon) {
                $coupon_id = $coupon['id'];
                if ($coupon['discount_type'] === 'flat') {
                    $discount_amount = min($coupon['discount_value'], $subtotal + $tax_amount);
                } else {
                    $discount_amount = ($subtotal + $tax_amount) * $coupon['discount_value'] / 100;
                }
            }
            $coupon_stmt->close();
        }
    }

    $total_amount = $subtotal + $tax_amount - $discount_amount;
    $order_number = 'ORD-' . strtoupper(uniqid());

    // Create order
    $order_stmt = $conn->prepare("INSERT INTO orders (user_id, order_number, total_amount, tax_amount, coupon_id, 
                                  discount_amount, payment_method, payment_status, status) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', 'pending')");
    if (!$order_stmt) {
        echo json_encode(['success' => false, 'message' => 'Failed to create order']);
        exit;
    }

    $order_stmt->bind_param("isdidss", 
        $user_id,
        $order_number,
        $total_amount,
        $tax_amount,
        $coupon_id,
        $discount_amount,
        $payment_method
    );

    if ($order_stmt->execute()) {
        $order_id = $conn->insert_id;

        // Add order items
        foreach ($cart_items as $item) {
            $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) 
                                        VALUES (?, ?, ?, ?)");
            if ($item_stmt) {
                $item_stmt->bind_param("iiii", $order_id, $item['product_id'], $item['quantity'], $item['price']);
                $item_stmt->execute();
                $item_stmt->close();
            }
        }

        // Update coupon usage
        if ($coupon_id) {
            $coupon_update = $conn->prepare("UPDATE coupons SET current_usage = current_usage + 1 WHERE id = ?");
            if ($coupon_update) {
                $coupon_update->bind_param("i", $coupon_id);
                $coupon_update->execute();
                $coupon_update->close();
            }
        }

        // Clear cart
        $clear_stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        if ($clear_stmt) {
            $clear_stmt->bind_param("i", $user_id);
            $clear_stmt->execute();
            $clear_stmt->close();
        }

        echo json_encode([
            'success' => true,
            'order_id' => $order_id,
            'order_number' => $order_number,
            'redirect' => SITE_URL . '/?page=payment&order_id=' . $order_id
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Order creation failed']);
    }
    $order_stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
