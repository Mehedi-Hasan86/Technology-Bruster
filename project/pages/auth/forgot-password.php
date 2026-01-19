<?php
require_once '../../config/database.php';
require_once '../../config/settings.php';
require_once '../../classes/Email.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        $error = 'Email is required';
    } else {
        $query = "SELECT id, name FROM users WHERE email = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', time() + 3600);

            $insert_query = "INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param("iss", $user['id'], $token, $expires);

            if ($insert_stmt->execute()) {
                $reset_link = SITE_URL . '/?page=reset-password&token=' . $token;
                $emailer = new Email();
                $emailer->sendPasswordReset($email, $user['name'], $reset_link);
                $success = 'Password reset link sent to your email';
            } else {
                $error = 'Failed to process request';
            }
        } else {
            $success = 'If email exists, password reset link has been sent';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/public/css/style.css">
</head>
<body>
<div class="appbar">
    <div class="appbar-logo"><?php echo SITE_NAME; ?></div>
</div>

<div class="main-content">
    <div style="max-width: 400px; margin: 40px auto; padding: 0 16px;">
        <h2 style="text-align: center; margin-bottom: 24px;">Reset Your Password</h2>

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

        <form method="POST">
            <div class="form-group">
                <label>Enter your email address</label>
                <input type="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px;">Send Reset Link</button>
        </form>

        <div style="text-align: center; margin-top: 16px;">
            <a href="<?php echo SITE_URL; ?>/?page=login" style="color: var(--primary); text-decoration: none;">Back to Login</a>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
</body>
</html>
