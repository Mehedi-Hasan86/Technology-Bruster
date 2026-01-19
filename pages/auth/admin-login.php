<?php
// Loader already included by index.php

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = '❌ Email and password are required';
    } else {
        $stmt = $conn->prepare("SELECT id, name, email, password, role FROM admin_users WHERE email = ?");
        
        if (!$stmt) {
            $error = '❌ Database error: ' . $conn->error;
        } else {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $admin = $result->fetch_assoc();
                
                // Verify password
                if (password_verify($password, $admin['password'])) {
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_name'] = $admin['name'];
                    $_SESSION['admin_role'] = $admin['role'];
                    header('Location: ' . SITE_URL . '/?page=admin');
                    exit;
                } else {
                    $error = '❌ Invalid email or password';
                }
            } else {
                $error = '❌ Invalid email or password';
            }
            
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
    <title>Admin Login - <?php echo SITE_NAME; ?></title>
    
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

        .admin-login-container {
            width: 100%;
            max-width: 450px;
        }

        .admin-login-card {
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

        .admin-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .admin-logo {
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

        .admin-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 8px;
        }

        .admin-header p {
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

        .admin-info {
            background: #e3f2fd;
            padding: 16px;
            border-radius: 10px;
            margin-top: 20px;
            font-size: 0.85rem;
            color: #1565c0;
            border-left: 4px solid #1565c0;
        }

        .admin-info strong {
            display: block;
            margin-bottom: 8px;
        }

        .back-link {
            text-align: center;
            margin-top: 30px;
        }

        .back-link a {
            color: white;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .back-link a:hover {
            gap: 12px;
        }

        .password-toggle {
            cursor: pointer;
            user-select: none;
        }
    </style>
</head>
<body>

<div class="admin-login-container">
    <div class="admin-login-card">
        <!-- Header -->
        <div class="admin-header">
            <div class="admin-logo">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h1>Admin Login</h1>
            <p>Administrators only</p>
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
                       placeholder="Enter admin email" required 
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" autofocus>
            </div>

            <!-- Password -->
            <div class="form-group">
                <label for="password">Password</label>
                <div class="input-group">
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="Enter password" required>
                    <span class="input-group-text password-toggle" onclick="togglePassword()">
                        <i class="fas fa-eye" id="toggleIcon"></i>
                    </span>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="login-btn">
                <i class="fas fa-sign-in-alt"></i> Login to Admin Panel
            </button>
        </form>

        <!-- Test Credentials Info -->
        <div class="admin-info">
            <strong><i class="fas fa-info-circle"></i> Test Credentials</strong>
            Email: <code>admin@test.com</code><br>
            Password: <code>123456</code>
        </div>
    </div>

    <!-- Back to Home -->
    <div class="back-link">
        <a href="<?php echo SITE_URL; ?>">
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
