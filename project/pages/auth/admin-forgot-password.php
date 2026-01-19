<?php
// Loader already included by index.php

$error = '';
$success = '';
$step = isset($_GET['step']) ? $_GET['step'] : 1;
$token = isset($_GET['token']) ? $_GET['token'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // Step 1: Request reset
    if ($action === 'request_reset') {
        $email = trim($_POST['email'] ?? '');
        
        if (empty($email)) {
            $error = '❌ Email is required';
        } else {
            // Check if admin exists
            $stmt = $conn->prepare("SELECT id FROM admin_users WHERE email = ?");
            if ($stmt) {
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    // Generate reset token
                    $token = bin2hex(random_bytes(32));
                    $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour expiry
                    
                    // Save token (using contact_messages table as temp storage or create new table)
                    $update = $conn->prepare("UPDATE admin_users SET reset_token = ?, reset_expires = ? WHERE email = ?");
                    if ($update) {
                        $update->bind_param("sss", $token, $expires, $email);
                        if ($update->execute()) {
                            $success = '✅ Check your email for reset link';
                            echo "<script>setTimeout(() => { window.location.href = '" . SITE_URL . "/?page=admin'; }, 3000);</script>";
                        }
                        $update->close();
                    }
                } else {
                    $error = '❌ No admin account found with this email';
                }
                $stmt->close();
            }
        }
    }
    
    // Step 2: Reset password
    elseif ($action === 'reset_password') {
        $token = $_POST['token'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        if (empty($token) || empty($new_password)) {
            $error = '❌ All fields required';
        } elseif ($new_password !== $confirm_password) {
            $error = '❌ Passwords do not match';
        } elseif (strlen($new_password) < 6) {
            $error = '❌ Password must be at least 6 characters';
        } else {
            // Verify token
            $stmt = $conn->prepare("SELECT id, email FROM admin_users WHERE reset_token = ? AND reset_expires > NOW()");
            if ($stmt) {
                $stmt->bind_param("s", $token);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($result->num_rows > 0) {
                    $admin = $result->fetch_assoc();
                    $hashed = password_hash($new_password, PASSWORD_BCRYPT);
                    
                    // Update password
                    $update = $conn->prepare("UPDATE admin_users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
                    if ($update) {
                        $update->bind_param("si", $hashed, $admin['id']);
                        if ($update->execute()) {
                            $success = '✅ Password reset successfully! Redirecting to login...';
                            echo "<script>setTimeout(() => { window.location.href = '" . SITE_URL . "/?page=admin'; }, 2000);</script>";
                        }
                        $update->close();
                    }
                } else {
                    $error = '❌ Invalid or expired reset link';
                }
                $stmt->close();
            }
        }
    }
}

// Check if token is valid
$token_valid = false;
if ($token) {
    $check = $conn->prepare("SELECT id FROM admin_users WHERE reset_token = ? AND reset_expires > NOW()");
    if ($check) {
        $check->bind_param("s", $token);
        $check->execute();
        $token_valid = $check->get_result()->num_rows > 0;
        $check->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Forgot Password - <?php echo SITE_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary: #007bff;
            --dark: #343a40;
            --light: #f8f9fa;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }

        .reset-container {
            width: 100%;
            max-width: 450px;
        }

        .reset-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .reset-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .reset-logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2.5rem;
            margin: 0 auto 20px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .reset-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 8px;
        }

        .reset-header p {
            color: #999;
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 8px;
            font-size: 0.95rem;
        }

        .form-control {
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: var(--light);
        }

        .form-control:focus {
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
            outline: none;
        }

        .input-group-text {
            background: var(--light);
            border: 2px solid #e0e0e0;
            border-left: none;
            color: #999;
            cursor: pointer;
        }

        .form-control:focus + .input-group-text {
            border-color: var(--primary);
            background: white;
            color: var(--primary);
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
        }

        .error-message,
        .success-message {
            padding: 14px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-left: 4px solid;
        }

        .error-message {
            background: #ffebee;
            color: #c62828;
            border-left-color: #c62828;
        }

        .success-message {
            background: #e8f5e9;
            color: #2e7d32;
            border-left-color: #2e7d32;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        .info-box {
            background: #e3f2fd;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 0.85rem;
            color: #1565c0;
            border-left: 4px solid #1565c0;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="reset-container">
    <div class="reset-card">
        <!-- Header -->
        <div class="reset-header">
            <div class="reset-logo">
                <i class="fas fa-key"></i>
            </div>
            <h1>Reset Password</h1>
            <p>Recover your admin account</p>
        </div>

        <!-- Error/Success Messages -->
        <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                <span><?php echo htmlspecialchars($success); ?></span>
            </div>
        <?php endif; ?>

        <!-- Step 1: Request Reset -->
        <?php if (!$token): ?>
            <div class="info-box">
                <i class="fas fa-info-circle"></i> Enter your admin email to receive a password reset link
            </div>

            <form method="POST">
                <input type="hidden" name="action" value="request_reset">

                <div class="form-group">
                    <label for="email">Admin Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           placeholder="Enter admin email" required 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" autofocus>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane"></i> Send Reset Link
                </button>
            </form>
        <?php endif; ?>

        <!-- Step 2: Reset Password (with valid token) -->
        <?php if ($token && $token_valid): ?>
            <div class="info-box">
                <i class="fas fa-info-circle"></i> Enter your new password below
            </div>

            <form method="POST">
                <input type="hidden" name="action" value="reset_password">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="new_password" name="new_password" 
                               placeholder="Enter new password" required minlength="6"
                               onkeyup="checkPasswordStrength()">
                        <span class="input-group-text" onclick="togglePassword('new_password', 'toggleIcon1')">
                            <i class="fas fa-eye" id="toggleIcon1"></i>
                        </span>
                    </div>
                    <div class="password-strength">
                        <div class="strength-meter" id="strengthMeter"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                               placeholder="Confirm password" required minlength="6">
                        <span class="input-group-text" onclick="togglePassword('confirm_password', 'toggleIcon2')">
                            <i class="fas fa-eye" id="toggleIcon2"></i>
                        </span>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-key"></i> Reset Password
                </button>
            </form>
        <?php elseif ($token && !$token_valid): ?>
            <div class="error-message">
                <i class="fas fa-times-circle"></i>
                <span>❌ Reset link has expired. Please request a new one.</span>
            </div>
        <?php endif; ?>

        <!-- Back Links -->
        <div class="back-link">
            <a href="<?php echo SITE_URL; ?>/?page=admin">
                <i class="fas fa-sign-in-alt"></i> Back to Admin Login
            </a>
        </div>
    </div>

    <!-- Back to Home -->
    <div style="text-align: center; margin-top: 30px;">
        <a href="<?php echo SITE_URL; ?>" style="color: white; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
            <i class="fas fa-arrow-left"></i> Back to Home
        </a>
    </div>
</div>

<style>
    .password-strength {
        margin-top: 8px;
        height: 4px;
        background: #e0e0e0;
        border-radius: 2px;
        overflow: hidden;
    }

    .strength-meter {
        height: 100%;
        width: 0%;
        background: #ccc;
        border-radius: 2px;
        transition: all 0.3s;
    }

    .strength-meter.weak {
        width: 33%;
        background: #dc3545;
    }

    .strength-meter.medium {
        width: 66%;
        background: #ffc107;
    }

    .strength-meter.strong {
        width: 100%;
        background: #28a745;
    }
</style>

<!-- Bootstrap JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

<script>
    function togglePassword(fieldId, iconId) {
        const field = document.getElementById(fieldId);
        const icon = document.getElementById(iconId);
        
        if (field.type === 'password') {
            field.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            field.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    function checkPasswordStrength() {
        const password = document.getElementById('new_password').value;
        const strengthMeter = document.getElementById('strengthMeter');
        
        let strength = 0;
        if (password.length >= 6) strength++;
        if (password.length >= 10) strength++;
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^a-zA-Z0-9]/.test(password)) strength++;
        
        strengthMeter.className = 'strength-meter';
        if (strength < 3) strengthMeter.classList.add('weak');
        else if (strength < 4) strengthMeter.classList.add('medium');
        else strengthMeter.classList.add('strong');
    }
</script>

</body>
</html>
