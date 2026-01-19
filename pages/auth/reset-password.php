<?php
require_once '../../config/database.php';
require_once '../../config/settings.php';

$error = '';
$success = '';
$token = $_GET['token'] ?? '';

if (empty($token)) {
    $error = 'Invalid reset link';
} else {
    $query = "SELECT user_id FROM password_resets WHERE token = ? AND expires_at > NOW()";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        $error = 'Reset link expired or invalid';
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($error)) {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($password) || empty($confirm_password)) {
        $error = 'All fields required';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } else {
        $query = "SELECT user_id FROM password_resets WHERE token = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $reset = $stmt->get_result()->fetch_assoc();

        if ($reset) {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $update_query = "UPDATE users SET password = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("si", $hashed, $reset['user_id']);

            if ($update_stmt->execute()) {
                $conn->query("DELETE FROM password_resets WHERE user_id = " . $reset['user_id']);
                $success = 'Password reset successfully! Please login.';
                header('Refresh: 2; url=' . SITE_URL . '/?page=login');
            } else {
                $error = 'Failed to reset password';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/public/css/style.css">
</head>
<body>
<div class="appbar">
    <div class="appbar-logo"><?php echo SITE_NAME; ?></div>
</div>

<div class="main-content">
    <div style="max-width: 400px; margin: 40px auto; padding: 0 16px;">
        <h2 style="text-align: center; margin-bottom: 24px;">Create New Password</h2>

        <?php if ($error): ?>
            <div style="background: #ffebee; color: #c62828; padding: 12px; border-radius: 8px; margin-bottom: 16px;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div style="background: #e8f5e9; color: #2e7d32; padding: 12px; border-radius: 8px; margin-bottom: 16px;">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($error)): ?>
        <form method="POST">
            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="password" required minlength="6">
            </div>

            <div class="form-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm_password" required minlength="6">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px;">Reset Password</button>
        </form>
        <?php endif; ?>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
</body>
</html>
