<?php
require_once '../config/database.php';
require_once '../config/settings.php';

header('Content-Type: application/json');

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'delete_product':
        $product_id = (int)$_POST['id'];
        $query = "DELETE FROM products WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $product_id);
        echo json_encode(['success' => $stmt->execute()]);
        break;

    case 'toggle_block':
        $user_id = (int)$_POST['user_id'];
        $block = (int)$_POST['block'];
        $query = "UPDATE users SET is_blocked = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $block, $user_id);
        echo json_encode(['success' => $stmt->execute()]);
        break;

    case 'toggle_coupon':
        $coupon_id = (int)$_POST['coupon_id'];
        $active = (int)$_POST['active'];
        $query = "UPDATE coupons SET is_active = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $active, $coupon_id);
        echo json_encode(['success' => $stmt->execute()]);
        break;

    case 'update_order_status':
        $order_id = (int)$_POST['order_id'];
        $status = $_POST['status'];
        $query = "UPDATE orders SET payment_status = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $status, $order_id);
        echo json_encode(['success' => $stmt->execute()]);
        break;

    case 'toggle_faq':
        $faq_id = (int)$_POST['faq_id'];
        $active = (int)$_POST['active'];
        $query = "UPDATE faq SET is_active = ? WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $active, $faq_id);
        echo json_encode(['success' => $stmt->execute()]);
        break;

    case 'add_faq':
        $question = trim($_POST['question'] ?? '');
        $answer = trim($_POST['answer'] ?? '');

        if (empty($question) || empty($answer)) {
            echo json_encode(['success' => false, 'message' => 'All fields required']);
            exit;
        }

        $query = "INSERT INTO faq (question, answer, is_active) VALUES (?, ?, 1)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $question, $answer);
        echo json_encode(['success' => $stmt->execute()]);
        break;

    case 'save_settings':
        $settings = $_POST;
        unset($settings['action']);

        foreach ($settings as $key => $value) {
            $query = "INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
                     ON DUPLICATE KEY UPDATE setting_value = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sss", $key, $value, $value);
            $stmt->execute();
        }

        echo json_encode(['success' => true, 'message' => 'Settings saved']);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
?>
