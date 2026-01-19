<?php
// Loader already included by index.php

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if (empty($name) || empty($email) || empty($password)) {
        $error = 'All fields are required';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address';
    } else {
        // Check if email exists
        $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        if ($check_stmt) {
            $check_stmt->bind_param("s", $email);
            $check_stmt->execute();
            
            if ($check_stmt->get_result()->num_rows > 0) {
                $error = 'Email already registered';
            } else {
                // Create new user
                $hashed_pwd = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
                
                if ($stmt) {
                    $stmt->bind_param("sss", $name, $email, $hashed_pwd);
                    
                    if ($stmt->execute()) {
                        $success = '✅ Account created successfully! Redirecting to login...';
                        header('Refresh: 2; url=' . SITE_URL . '/?page=login');
                    } else {
                        $error = 'Signup failed: ' . $stmt->error;
                    }
                    $stmt->close();
                }
            }
            $check_stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - <?php echo SITE_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/public/css/style.css">
    
    <style>
        :root {
            --primary: #007bff;
            --success: #28a745;
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

        .signup-container {
            width: 100%;
            max-width: 500px;
        }

        .signup-card {
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

        .signup-header {
            text-align: center;
            margin-bottom: 35px;
        }

        .signup-logo {
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

        .signup-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 8px;
        }

        .signup-header p {
            color: #999;
            font-size: 0.95rem;
        }

        .form-group {
            margin-bottom: 20px;
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

        .form-control::placeholder {
            color: #ccc;
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
            transition: all 0.3s ease;
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

        .strength-text {
            font-size: 0.75rem;
            margin-top: 4px;
            color: #999;
            transition: color 0.3s ease;
        }

        .terms-check {
            margin-bottom: 24px;
            padding: 12px;
            background: var(--light);
            border-radius: 10px;
        }

        .form-check-input {
            width: 18px;
            height: 18px;
            border: 2px solid #e0e0e0;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 5px;
        }

        .form-check-input:checked {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .form-check-label {
            font-size: 0.9rem;
            color: #666;
            cursor: pointer;
            margin-left: 8px;
        }

        .form-check-label a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }

        .form-check-label a:hover {
            text-decoration: underline;
        }

        .signup-btn {
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
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .signup-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
        }

        .signup-btn:active {
            transform: translateY(0);
        }

        .signup-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .login-link {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            font-size: 0.95rem;
            color: #666;
        }

        .login-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .login-link a:hover {
            color: #0056b3;
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
            animation: slideDown 0.3s ease-out;
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

        .error-message i,
        .success-message i {
            font-size: 1.2rem;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .password-toggle {
            cursor: pointer;
            user-select: none;
        }

        /* Responsive */
        @media (max-width: 576px) {
            .signup-card {
                padding: 30px 20px;
            }

            .signup-header h1 {
                font-size: 1.5rem;
            }

            .signup-logo {
                width: 70px;
                height: 70px;
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>

<div class="signup-container">
    <div class="signup-card">
        <!-- Header -->
        <div class="signup-header">
            <div class="signup-logo">
                <i class="fas fa-user-plus"></i>
            </div>
            <h1>Create Account</h1>
            <p>Join our community today</p>
        </div>

        <!-- Error Message -->
        <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <!-- Success Message -->
        <?php if ($success): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                <span><?php echo htmlspecialchars($success); ?></span>
            </div>
        <?php endif; ?>

        <!-- Signup Form -->
        <form method="POST" id="signupForm">
            <!-- Full Name -->
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" class="form-control" id="name" name="name" 
                       placeholder="Enter your full name" required 
                       value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" autofocus>
            </div>

            <!-- Email -->
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" 
                       placeholder="Enter your email" required 
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>

            <!-- Password -->
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="Create a strong password" required 
                           onkeyup="checkPasswordStrength()">
                    <span class="input-group-text password-toggle" onclick="togglePassword('password', 'toggleIcon1')">
                        <i class="fas fa-eye" id="toggleIcon1"></i>
                    </span>
                </div>
                <div class="password-strength">
                    <div class="strength-meter" id="strengthMeter"></div>
                </div>
                <div class="strength-text" id="strengthText"></div>
            </div>

            <!-- Confirm Password -->
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                           placeholder="Confirm your password" required>
                    <span class="input-group-text password-toggle" onclick="togglePassword('confirm_password', 'toggleIcon2')">
                        <i class="fas fa-eye" id="toggleIcon2"></i>
                    </span>
                </div>
                <div id="passwordMatch" style="font-size: 0.85rem; margin-top: 6px; color: #dc3545; display: none;">
                    <i class="fas fa-times-circle"></i> Passwords do not match
                </div>
            </div>

            <!-- Terms & Conditions -->
            <div class="terms-check">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                    <label class="form-check-label" for="terms">
                        I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
                    </label>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="signup-btn" id="submitBtn">
                <i class="fas fa-user-check"></i> Create Account
            </button>
        </form>

        <!-- Login Link -->
        <div class="login-link">
            Already have an account? <a href="<?php echo SITE_URL; ?>/?page=login">Sign in here</a>
        </div>
    </div>

    <!-- Back to Home -->
    <div style="text-align: center; margin-top: 30px;">
        <a href="<?php echo SITE_URL; ?>" style="color: white; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: all 0.3s;">
            <i class="fas fa-arrow-left"></i> Back to Home
        </a>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

<script>
    function togglePassword(fieldId, iconId) {
        const passwordInput = document.getElementById(fieldId);
        const toggleIcon = document.getElementById(iconId);
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        }
    }

    function checkPasswordStrength() {
        const password = document.getElementById('password').value;
        const strengthMeter = document.getElementById('strengthMeter');
        const strengthText = document.getElementById('strengthText');
        
        let strength = 0;
        
        if (password.length >= 6) strength++;
        if (password.length >= 10) strength++;
        if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^a-zA-Z0-9]/.test(password)) strength++;
        
        strengthMeter.className = 'strength-meter';
        
        if (strength === 0) {
            strengthMeter.style.width = '0%';
            strengthText.textContent = '';
        } else if (strength < 3) {
            strengthMeter.classList.add('weak');
            strengthText.textContent = 'Weak password';
            strengthText.style.color = '#dc3545';
        } else if (strength < 4) {
            strengthMeter.classList.add('medium');
            strengthText.textContent = 'Medium password';
            strengthText.style.color = '#ffc107';
        } else {
            strengthMeter.classList.add('strong');
            strengthText.textContent = 'Strong password';
            strengthText.style.color = '#28a745';
        }
        
        checkPasswordsMatch();
    }

    function checkPasswordsMatch() {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const passwordMatch = document.getElementById('passwordMatch');
        const submitBtn = document.getElementById('submitBtn');
        
        if (confirmPassword && password !== confirmPassword) {
            passwordMatch.style.display = 'block';
            submitBtn.disabled = true;
        } else {
            passwordMatch.style.display = 'none';
            submitBtn.disabled = false;
        }
    }

    // Check passwords on confirm password input
    document.getElementById('confirm_password').addEventListener('input', checkPasswordsMatch);

    // Remove error message on input
    ['name', 'email', 'password', 'confirm_password'].forEach(id => {
        document.getElementById(id).addEventListener('input', function() {
            const errorMsg = document.querySelector('.error-message');
            if (errorMsg) errorMsg.style.display = 'none';
        });
    });

    // Form validation before submit
    document.getElementById('signupForm').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const terms = document.getElementById('terms').checked;

        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Passwords do not match!');
            return;
        }

        if (!terms) {
            e.preventDefault();
            alert('Please agree to the Terms of Service');
            return;
        }
    });
</script>

</body>
</html>
