<?php
// Loader already included by index.php

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email and password are required';
    } else {
        $stmt = $conn->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
        
        if (!$stmt) {
            $error = 'Database error';
        } else {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                if (password_verify($password, $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];
                    header('Location: ' . SITE_URL . '/?page=products');
                    exit;
                }
            }
            $error = 'Invalid email or password';
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo SITE_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/public/css/style.css">
    
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
        }

        .login-container {
            width: 100%;
            max-width: 450px;
            padding: 20px;
        }

        .login-card {
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

        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .login-logo {
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

        .login-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 8px;
        }

        .login-header p {
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

        .form-control::placeholder {
            color: #ccc;
        }

        .input-group-text {
            background: var(--light);
            border: 2px solid #e0e0e0;
            border-right: none;
            color: #999;
            cursor: pointer;
        }

        .form-control:focus + .input-group-text {
            border-color: var(--primary);
            background: white;
            color: var(--primary);
        }

        .form-check {
            margin-bottom: 24px;
        }

        .form-check-input {
            width: 18px;
            height: 18px;
            border: 2px solid #e0e0e0;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
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

        .forgot-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .forgot-link:hover {
            color: #0056b3;
        }

        .login-btn {
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

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 16px;
            margin: 24px 0;
            color: #ccc;
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e0e0e0;
        }

        .social-login {
            display: flex;
            gap: 12px;
            margin-bottom: 24px;
        }

        .social-btn {
            flex: 1;
            padding: 12px;
            border: 2px solid #e0e0e0;
            background: white;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 1.2rem;
            color: #666;
        }

        .social-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: var(--light);
        }

        .signup-link {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
            font-size: 0.95rem;
            color: #666;
        }

        .signup-link a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .signup-link a:hover {
            color: #0056b3;
        }

        .error-message {
            background: #ffebee;
            color: #c62828;
            padding: 14px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-left: 4px solid #c62828;
            animation: slideDown 0.3s ease-out;
        }

        .error-message i {
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
            .login-card {
                padding: 30px 20px;
            }

            .login-header h1 {
                font-size: 1.5rem;
            }

            .login-logo {
                width: 70px;
                height: 70px;
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-card">
        <!-- Header -->
        <div class="login-header">
            <div class="login-logo">
                <i class="fas fa-user"></i>
            </div>
            <h1>Welcome Back</h1>
            <p>Sign in to your account</p>
        </div>

        <!-- Error Message -->
        <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <!-- Login Form -->
        <form method="POST">
            <!-- Email -->
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" 
                       placeholder="Enter your email" required 
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" autofocus>
            </div>

            <!-- Password -->
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="Enter your password" required>
                    <span class="input-group-text password-toggle" onclick="togglePassword()">
                        <i class="fas fa-eye" id="toggleIcon"></i>
                    </span>
                </div>
            </div>

            <!-- Remember & Forgot -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="remember" name="remember">
                    <label class="form-check-label" for="remember">
                        Remember me
                    </label>
                </div>
                <a href="<?php echo SITE_URL; ?>/?page=forgot-password" class="forgot-link">
                    Forgot password?
                </a>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="login-btn">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
        </form>

        <!-- Divider -->
        <div class="divider">or</div>

        <!-- Social Login -->
        <div class="social-login">
            <button type="button" class="social-btn" title="Sign in with Google">
                <i class="fab fa-google"></i>
            </button>
            <button type="button" class="social-btn" title="Sign in with Facebook">
                <i class="fab fa-facebook"></i>
            </button>
            <button type="button" class="social-btn" title="Sign in with GitHub">
                <i class="fab fa-github"></i>
            </button>
        </div>

        <!-- Sign Up Link -->
        <div class="signup-link">
            Don't have an account? <a href="<?php echo SITE_URL; ?>/?page=signup">Sign up here</a>
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
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');
        
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

    // Remove error message on input
    document.getElementById('email').addEventListener('input', function() {
        const errorMsg = document.querySelector('.error-message');
        if (errorMsg) errorMsg.style.display = 'none';
    });

    document.getElementById('password').addEventListener('input', function() {
        const errorMsg = document.querySelector('.error-message');
        if (errorMsg) errorMsg.style.display = 'none';
    });
</script>

</body>
</html>
