<?php
// Load loader from parent directory
require_once dirname(__DIR__) . '/loader.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_POST['action'] ?? '';

if ($action === 'add') {
    $product_id = (int)($_POST['product_id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 1);

    if ($product_id <= 0 || $quantity <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid data']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)
                            ON DUPLICATE KEY UPDATE quantity = quantity + ?");
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
        exit;
    }

    $stmt->bind_param("iiii", $user_id, $product_id, $quantity, $quantity);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Added to cart']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add']);
    }
    $stmt->close();

} elseif ($action === 'remove') {
    $cart_id = (int)($_POST['cart_id'] ?? 0);

    $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    if (!$stmt) {
        echo json_encode(['success' => false]);
        exit;
    }

    $stmt->bind_param("ii", $cart_id, $user_id);
    $result = $stmt->execute();
    echo json_encode(['success' => $result]);
    $stmt->close();

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
