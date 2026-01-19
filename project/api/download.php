<?php
require_once dirname(__DIR__) . '/loader.php';

require_once '../config/database.php';
require_once '../config/settings.php';

$token = $_GET['token'] ?? '';

if (empty($token)) {
    die('❌ Invalid download link');
}

// Verify token
$stmt = $conn->prepare("SELECT dt.*, p.file_path FROM download_tokens dt 
                       JOIN products p ON dt.product_id = p.id 
                       WHERE dt.token = ? AND dt.expires_at > NOW()");

if (!$stmt) {
    die('❌ Database error');
}

$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

if ($result->num_rows === 0) {
    die('❌ Download link expired or invalid');
}

$download = $result->fetch_assoc();
$file_path = PRODUCT_FILE_DIR . $download['file_path'];

if (!file_exists($file_path)) {
    die('❌ File not found');
}

// Update download count
$update_stmt = $conn->prepare("UPDATE download_tokens SET downloaded_count = downloaded_count + 1 WHERE token = ?");
if ($update_stmt) {
    $update_stmt->bind_param("s", $token);
    $update_stmt->execute();
    $update_stmt->close();
}

// Update product downloads
$product_stmt = $conn->prepare("UPDATE products SET downloads_count = downloads_count + 1 WHERE id = ?");
if ($product_stmt) {
    $product_stmt->bind_param("i", $download['product_id']);
    $product_stmt->execute();
    $product_stmt->close();
}

// Force download
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename=' . basename($file_path));
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . filesize($file_path));

readfile($file_path);
exit;
?>
