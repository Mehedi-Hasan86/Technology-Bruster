<?php
// Loader already included by index.php

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . SITE_URL . '/?page=login');
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
if (!$stmt) {
    die("❌ Query error");
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    header('Location: ' . SITE_URL . '/?page=login');
    exit;
}

// Handle profile updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'update_profile') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $bio = trim($_POST['bio'] ?? '');

        if (empty($name) || empty($email)) {
            $error = '❌ Name and email required';
        } else {
            $update_stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, bio = ? WHERE id = ?");
            if ($update_stmt) {
                $update_stmt->bind_param("ssssi", $name, $email, $phone, $bio, $user_id);
                if ($update_stmt->execute()) {
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_email'] = $email;
                    $success = '✅ Profile updated successfully';
                    $user['name'] = $name;
                    $user['email'] = $email;
                    $user['phone'] = $phone;
                    $user['bio'] = $bio;
                }
                $update_stmt->close();
            }
        }
    }
    elseif ($action === 'upload_picture') {
        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
            $image_file = $_FILES['profile_pic'];
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            
            if (!in_array($image_file['type'], $allowed_types)) {
                $error = '❌ Invalid image format. Use JPG, PNG, GIF, or WebP';
            } elseif ($image_file['size'] > 5 * 1024 * 1024) {
                $error = '❌ Image too large (max 5MB)';
            } else {
                // Delete old profile picture if exists
                if ($user['profile_pic'] && file_exists(PRODUCT_IMG_DIR . $user['profile_pic'])) {
                    @unlink(PRODUCT_IMG_DIR . $user['profile_pic']);
                }
                
                // Create unique filename
                $filename = 'profile_' . $user_id . '_' . time() . '.' . pathinfo($image_file['name'], PATHINFO_EXTENSION);
                $upload_path = PRODUCT_IMG_DIR . $filename;
                
                if (move_uploaded_file($image_file['tmp_name'], $upload_path)) {
                    $pic_stmt = $conn->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
                    if ($pic_stmt) {
                        $pic_stmt->bind_param("si", $filename, $user_id);
                        if ($pic_stmt->execute()) {
                            $success = '✅ Profile picture updated';
                            $user['profile_pic'] = $filename;
                        }
                        $pic_stmt->close();
                    }
                } else {
                    $error = '❌ Failed to upload image';
                }
            }
        }
    }
    elseif ($action === 'change_password') {
        $old_pwd = $_POST['old_password'] ?? '';
        $new_pwd = $_POST['new_password'] ?? '';
        $confirm_pwd = $_POST['confirm_password'] ?? '';

        if (empty($old_pwd) || empty($new_pwd)) {
            $error = '❌ All fields required';
        } elseif ($new_pwd !== $confirm_pwd) {
            $error = '❌ Passwords do not match';
        } elseif (!password_verify($old_pwd, $user['password'])) {
            $error = '❌ Incorrect current password';
        } else {
            $hashed = password_hash($new_pwd, PASSWORD_BCRYPT);
            $pwd_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            if ($pwd_stmt) {
                $pwd_stmt->bind_param("si", $hashed, $user_id);
                if ($pwd_stmt->execute()) {
                    $success = '✅ Password changed successfully';
                }
                $pwd_stmt->close();
            }
        }
    }
}

// Get user statistics
$orders_result = $conn->query("SELECT COUNT(*) as count FROM orders WHERE user_id = $user_id AND payment_status = 'success'");
$total_orders = $orders_result ? $orders_result->fetch_assoc()['count'] : 0;

$spent_result = $conn->query("SELECT SUM(total_amount) as sum FROM orders WHERE user_id = $user_id AND payment_status = 'success'");
$total_spent = $spent_result ? ($spent_result->fetch_assoc()['sum'] ?? 0) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - <?php echo SITE_NAME; ?></title>
    
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
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 30px 0;
        }

        .profile-header {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            margin-bottom: 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .profile-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 100px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .profile-pic-container {
            position: relative;
            z-index: 2;
            margin-top: -50px;
            margin-bottom: 20px;
        }

        .profile-pic {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 6px solid white;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            background: var(--light);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: #ccc;
            margin: 0 auto;
        }

        .pic-upload-overlay {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            cursor: pointer;
            font-size: 1.2rem;
            transition: all 0.3s;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            border: 3px solid white;
            margin-left: 100px;
            margin-top: 100px;
        }

        .pic-upload-overlay:hover {
            transform: scale(1.1);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }

        #pic-input {
            display: none;
        }

        .profile-info h2 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark);
            margin: 15px 0 5px;
        }

        .profile-info p {
            color: #999;
            font-size: 0.95rem;
            margin: 0;
        }

        .profile-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 20px;
            margin-top: 30px;
            padding-top: 30px;
            border-top: 1px solid #e0e0e0;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary);
        }

        .stat-label {
            font-size: 0.85rem;
            color: #999;
            text-transform: uppercase;
            margin-top: 5px;
        }

        .nav-tabs {
            border-bottom: 2px solid #e0e0e0;
            margin-bottom: 30px;
        }

        .nav-tabs .nav-link {
            color: #999;
            border: none;
            border-bottom: 3px solid transparent;
            padding: 15px 20px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .nav-tabs .nav-link:hover {
            color: var(--primary);
            border-bottom-color: var(--primary);
        }

        .nav-tabs .nav-link.active {
            color: var(--primary);
            border-bottom-color: var(--primary);
            background: none;
        }

        .tab-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
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
            transition: all 0.3s;
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

        textarea.form-control {
            resize: vertical;
            min-height: 100px;
        }

        .btn-save {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s;
            cursor: pointer;
        }

        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .message {
            padding: 14px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-left: 4px solid;
            animation: slideDown 0.3s ease-out;
        }

        .error {
            background: #ffebee;
            color: #c62828;
            border-left-color: #c62828;
        }

        .success {
            background: #e8f5e9;
            color: #2e7d32;
            border-left-color: #2e7d32;
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

        @media (max-width: 768px) {
            .profile-header {
                padding: 30px 20px;
            }

            .profile-info h2 {
                font-size: 1.5rem;
            }

            .tab-content {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
<?php include INCLUDES_PATH . '/navbar.php'; ?>

<div class="container py-4">
    <!-- Profile Header -->
    <div class="profile-header">
        <div class="profile-pic-container">
            <div class="profile-pic" id="profile-pic-display">
                <?php if (!empty($user['profile_pic']) && file_exists(PRODUCT_IMG_DIR . $user['profile_pic'])): ?>
                    <img src="<?php echo SITE_URL; ?>/uploads/product_images/<?php echo htmlspecialchars($user['profile_pic']); ?>" 
                         alt="Profile" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                <?php else: ?>
                    <i class="fas fa-user"></i>
                <?php endif; ?>
            </div>
            <div class="pic-upload-overlay" onclick="document.getElementById('pic-input').click();" title="Upload Photo">
                <i class="fas fa-camera"></i>
            </div>
            <form id="pic-form" style="display: none;">
                <input type="file" id="pic-input" name="profile_pic" accept="image/*" onchange="handlePicUpload()">
            </form>
        </div>

        <div class="profile-info">
            <h2><?php echo htmlspecialchars($user['name']); ?></h2>
            <p><?php echo htmlspecialchars($user['email']); ?></p>
        </div>

        <div class="profile-stats">
            <div class="stat-item">
                <div class="stat-number"><?php echo $total_orders; ?></div>
                <div class="stat-label">Orders</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php echo CURRENCY; ?> <?php echo number_format($total_spent, 0); ?></div>
                <div class="stat-label">Total Spent</div>
            </div>
            <div class="stat-item">
                <div class="stat-number" style="color: #28a745;">✅</div>
                <div class="stat-label">Member</div>
            </div>
        </div>
    </div>

    <!-- Messages -->
    <?php if ($error): ?>
        <div class="message error">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo htmlspecialchars($error); ?></span>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="message success">
            <i class="fas fa-check-circle"></i>
            <span><?php echo htmlspecialchars($success); ?></span>
        </div>
    <?php endif; ?>

    <!-- Tabs -->
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="about-tab" data-bs-toggle="tab" href="#about">
                <i class="fas fa-user-circle"></i> About
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="security-tab" data-bs-toggle="tab" href="#security">
                <i class="fas fa-lock"></i> Security
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="orders-tab" data-bs-toggle="tab" href="#orders">
                <i class="fas fa-shopping-bag"></i> Orders
            </a>
        </li>
    </ul>

    <div class="tab-content">
        <!-- About Tab -->
        <div class="tab-pane fade show active" id="about">
            <form method="POST">
                <input type="hidden" name="action" value="update_profile">

                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>

                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" class="form-control" name="phone" placeholder="Enter phone number" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label>Bio</label>
                    <textarea class="form-control" name="bio" placeholder="Tell us about yourself..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                </div>

                <button type="submit" class="btn-save">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </form>
        </div>

        <!-- Security Tab -->
        <div class="tab-pane fade" id="security">
            <form method="POST">
                <input type="hidden" name="action" value="change_password">

                <div class="form-group">
                    <label>Current Password</label>
                    <input type="password" class="form-control" name="old_password" required>
                </div>

                <div class="form-group">
                    <label>New Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control" name="new_password" id="new_pwd" required onkeyup="checkPasswordStrength()">
                        <span class="input-group-text" onclick="togglePassword('new_pwd', 'toggleIcon1')">
                            <i class="fas fa-eye" id="toggleIcon1"></i>
                        </span>
                    </div>
                    <div class="password-strength">
                        <div class="strength-meter" id="strengthMeter"></div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Confirm New Password</label>
                    <div class="input-group">
                        <input type="password" class="form-control" name="confirm_password" required>
                        <span class="input-group-text" onclick="togglePassword('confirm_password', 'toggleIcon2')">
                            <i class="fas fa-eye" id="toggleIcon2"></i>
                        </span>
                    </div>
                </div>

                <button type="submit" class="btn-save">
                    <i class="fas fa-key"></i> Change Password
                </button>
            </form>
        </div>

        <!-- Orders Tab -->
        <div class="tab-pane fade" id="orders">
            <?php
            $orders = $conn->query("SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 10");
            $orders_list = $orders ? $orders->fetch_all(MYSQLI_ASSOC) : [];
            ?>

            <?php if (empty($orders_list)): ?>
                <div style="text-align: center; padding: 40px;">
                    <i style="font-size: 3rem; color: #ccc;" class="fas fa-shopping-bag"></i>
                    <p style="margin-top: 15px; color: #999;">No orders yet</p>
                    <a href="<?php echo SITE_URL; ?>/?page=products" class="btn-save" style="display: inline-block; text-decoration: none; margin-top: 15px;">
                        <i class="fas fa-shopping-cart"></i> Start Shopping
                    </a>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table style="width: 100;">
                        <thead>
                            <tr style="background: var(--light);">
                                <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Order #</th>
                                <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Date</th>
                                <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Amount</th>
                                <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Status</th>
                                <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders_list as $order): ?>
                                <tr style="border-bottom: 1px solid #eee;">
                                    <td style="padding: 12px;"><?php echo htmlspecialchars($order['order_number']); ?></td>
                                    <td style="padding: 12px; font-size: 0.9rem;"><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                    <td style="padding: 12px; font-weight: 600;"><?php echo CURRENCY; ?> <?php echo number_format($order['total_amount'], 0); ?></td>
                                    <td style="padding: 12px;">
                                        <span style="background: <?php echo $order['payment_status'] === 'success' ? '#e8f5e9' : '#fff3e0'; ?>; color: <?php echo $order['payment_status'] === 'success' ? '#2e7d32' : '#ff9800'; ?>; padding: 4px 8px; border-radius: 6px; font-size: 0.85rem;">
                                            <?php echo ucfirst($order['payment_status']); ?>
                                        </span>
                                    </td>
                                    <td style="padding: 12px;">
                                        <a href="<?php echo SITE_URL; ?>/?page=order-detail&id=<?php echo $order['id']; ?>" style="color: var(--primary); text-decoration: none; font-weight: 600;">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>

<!-- Bootstrap JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

<script>
    const SITE_URL = '<?php echo SITE_URL; ?>';

    function handlePicUpload() {
        const fileInput = document.getElementById('pic-input');
        const file = fileInput.files[0];

        if (!file) return;

        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            alert('Invalid file type. Use JPG, PNG, GIF, or WebP');
            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            alert('File too large (max 5MB)');
            return;
        }

        // Preview
        const reader = new FileReader();
        reader.onload = function(e) {
            const display = document.getElementById('profile-pic-display');
            display.innerHTML = '<img src="' + e.target.result + '" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">';
        };
        reader.readAsDataURL(file);

        // Upload
        const formData = new FormData();
        formData.append('action', 'upload_picture');
        formData.append('profile_pic', file);

        fetch('', {
            method: 'POST',
            body: formData
        })
        .then(() => location.reload())
        .catch(err => alert('Upload failed'));
    }

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
        const password = document.getElementById('new_pwd').value;
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

    // Auto-hide messages
    setTimeout(() => {
        document.querySelectorAll('.message').forEach(msg => {
            msg.style.display = 'none';
        });
    }, 5000);
</script>

</body>
</html>
