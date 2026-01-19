<?php
require_once '../config/database.php';
require_once '../config/settings.php';
require_once '../classes/Email.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$action = $_POST['action'] ?? '';
$user_id = $_SESSION['user_id'];

switch ($action) {
    case 'process_bkash':
        $order_id = (int)$_POST['order_id'];
        $amount = (float)$_POST['amount'];

        // Get order
        $order = $conn->query("SELECT * FROM orders WHERE id = $order_id AND user_id = $user_id")->fetch_assoc();
        
        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            exit;
        }

        // Simulate bKash payment (In production, use actual API)
        $transaction_id = 'BKASH-' . time();
        
        $update_query = "UPDATE orders SET payment_status = 'success', transaction_id = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("si", $transaction_id, $order_id);

        if ($stmt->execute()) {
            // Send confirmation email
            $user = $conn->query("SELECT name, email FROM users WHERE id = $user_id")->fetch_assoc();
            $emailer = new Email();
            $emailer->sendOrderConfirmation($user['email'], $user['name'], $order['order_number'], $order['total_amount']);

            // Create download tokens
            $items = $conn->query("SELECT product_id FROM order_items WHERE order_id = $order_id")->fetch_all(MYSQLI_ASSOC);
            foreach ($items as $item) {
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', time() + (30 * 24 * 3600)); // 30 days
                
                $token_query = "INSERT INTO download_tokens (user_id, product_id, order_id, token, expires_at) VALUES (?, ?, ?, ?, ?)";
                $token_stmt = $conn->prepare($token_query);
                $token_stmt->bind_param("iiiis", $user_id, $item['product_id'], $order_id, $token, $expires);
                $token_stmt->execute();
            }

            echo json_encode(['success' => true, 'message' => 'Payment successful', 'order_id' => $order_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Payment processing failed']);
        }
        break;

    case 'process_ssl':
        // SSLCommerz Integration
        $order_id = (int)$_POST['order_id'];
        $order = $conn->query("SELECT * FROM orders WHERE id = $order_id AND user_id = $user_id")->fetch_assoc();

        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            exit;
        }

        // In production, integrate with SSLCommerz API
        // For now, simulate success
        $transaction_id = 'SSL-' . time();
        
        $update_query = "UPDATE orders SET payment_status = 'success', transaction_id = ? WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("si", $transaction_id, $order_id);

        if ($stmt->execute()) {
            $user = $conn->query("SELECT name, email FROM users WHERE id = $user_id")->fetch_assoc();
            $emailer = new Email();
            $emailer->sendOrderConfirmation($user['email'], $user['name'], $order['order_number'], $order['total_amount']);

            echo json_encode(['success' => true, 'message' => 'Payment successful']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Payment processing failed']);
        }
        break;

    case 'check_payment':
        $order_id = (int)$_POST['order_id'];
        $order = $conn->query("SELECT payment_status FROM orders WHERE id = $order_id AND user_id = $user_id")->fetch_assoc();
        
        echo json_encode([
            'success' => true,
            'status' => $order['payment_status'] ?? 'unknown'
        ]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
