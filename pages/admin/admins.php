<?php
// Loader already included by index.php

if (!isset($_SESSION['admin_id'])) {
    header('Location: ' . SITE_URL . '/?page=admin');
    exit;
}

$message = '';
$error = '';

// Handle add admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_admin') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'editor';

        if (empty($name) || empty($email) || empty($password)) {
            $error = 'All fields are required';
        } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email address';
        } else if (strlen($password) < 6) {
            $error = 'Password must be at least 6 characters';
        } else {
            // Check if email exists
            $check = $conn->query("SELECT id FROM admin_users WHERE email = '$email'");
            if ($check && $check->num_rows > 0) {
                $error = 'Email already exists';
            } else {
                $hashed = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $conn->prepare("INSERT INTO admin_users (name, email, password, role) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $name, $email, $hashed, $role);
                
                if ($stmt->execute()) {
                    $message = '✅ Admin user created successfully!';
                    $_POST = [];
                } else {
                    $error = 'Error creating admin: ' . $stmt->error;
                }
                $stmt->close();
            }
        }
    } elseif ($_POST['action'] === 'delete_admin') {
        $admin_id = (int)$_POST['admin_id'];
        
        if ($admin_id == $_SESSION['admin_id']) {
            $error = 'Cannot delete your own account';
        } else {
            $stmt = $conn->prepare("DELETE FROM admin_users WHERE id = ? AND role != 'super_admin'");
            $stmt->bind_param("i", $admin_id);
            
            if ($stmt->execute()) {
                $message = '✅ Admin user deleted successfully!';
            } else {
                $error = 'Error deleting admin';
            }
            $stmt->close();
        }
    } elseif ($_POST['action'] === 'update_role') {
        $admin_id = (int)$_POST['admin_id'];
        $new_role = $_POST['role'] ?? 'editor';
        
        if ($admin_id == $_SESSION['admin_id']) {
            $error = 'Cannot change your own role';
        } else {
            $stmt = $conn->prepare("UPDATE admin_users SET role = ? WHERE id = ?");
            $stmt->bind_param("si", $new_role, $admin_id);
            
            if ($stmt->execute()) {
                $message = '✅ Admin role updated successfully!';
            } else {
                $error = 'Error updating role';
            }
            $stmt->close();
        }
    }
}

// Get all admins
$admins = $conn->query("SELECT id, name, email, role, created_at FROM admin_users ORDER BY created_at DESC");
$admin_list = $admins ? $admins->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Admins - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/public/css/style.css">
</head>
<body>
<?php include INCLUDES_PATH . '/admin-navbar.php'; ?>

<div style="display: flex; gap: 24px; padding: 24px;">
    <?php include INCLUDES_PATH . '/admin-sidebar.php'; ?>

    <div style="flex: 1; max-width: 1200px;">
        <h1 style="font-size: 28px; margin-bottom: 24px;">👨‍💼 Manage Admins</h1>

        <?php if ($message): ?>
            <div style="background: #d4edda; color: #155724; padding: 12px 16px; border-radius: 4px; margin-bottom: 16px;">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div style="background: #f8d7da; color: #721c24; padding: 12px 16px; border-radius: 4px; margin-bottom: 16px;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Add New Admin Form -->
        <div style="background: white; padding: 24px; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.1); margin-bottom: 24px;">
            <h2 style="font-size: 20px; margin-bottom: 16px; font-weight: 600;">➕ Add New Admin</h2>
            
            <form method="POST" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px;">
                <input type="hidden" name="action" value="add_admin">
                
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 500;">Full Name</label>
                    <input type="text" name="name" placeholder="Admin Name" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                </div>

                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 500;">Email</label>
                    <input type="email" name="email" placeholder="admin@example.com" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                </div>

                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 500;">Password</label>
                    <input type="password" name="password" placeholder="Min 6 characters" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                </div>

                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 500;">Role</label>
                    <select name="role" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                        <option value="editor">Editor</option>
                        <option value="super_admin">Super Admin</option>
                    </select>
                </div>

                <div style="display: flex; align-items: flex-end;">
                    <button type="submit" style="width: 100%; padding: 10px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: 600;">Create Admin</button>
                </div>
            </form>
        </div>

        <!-- Admins List -->
        <div style="background: white; padding: 24px; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.1);">
            <h2 style="font-size: 20px; margin-bottom: 16px; font-weight: 600;">📋 All Admins</h2>

            <?php if (empty($admin_list)): ?>
                <p style="opacity: 0.7; text-align: center; padding: 20px;">No admins found</p>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #f5f5f5;">
                                <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Name</th>
                                <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Email</th>
                                <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Role</th>
                                <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Created</th>
                                <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($admin_list as $admin): ?>
                                <tr style="border-bottom: 1px solid #eee;">
                                    <td style="padding: 12px;">
                                        <strong><?php echo htmlspecialchars($admin['name']); ?></strong>
                                        <?php if ($admin['id'] == $_SESSION['admin_id']): ?>
                                            <span style="margin-left: 8px; background: #007bff; color: white; padding: 2px 8px; border-radius: 3px; font-size: 12px;">You</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 12px;"><?php echo htmlspecialchars($admin['email']); ?></td>
                                    <td style="padding: 12px;">
                                        <form method="POST" style="display: inline; gap: 8px; display: flex; align-items: center;">
                                            <input type="hidden" name="action" value="update_role">
                                            <input type="hidden" name="admin_id" value="<?php echo $admin['id']; ?>">
                                            <select name="role" style="padding: 6px; border: 1px solid #ddd; border-radius: 4px;" <?php echo ($admin['id'] == $_SESSION['admin_id']) ? 'disabled' : ''; ?> onchange="this.form.submit();">
                                                <option value="editor" <?php echo $admin['role'] === 'editor' ? 'selected' : ''; ?>>Editor</option>
                                                <option value="super_admin" <?php echo $admin['role'] === 'super_admin' ? 'selected' : ''; ?>>Super Admin</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td style="padding: 12px;">
                                        <?php echo date('M d, Y', strtotime($admin['created_at'])); ?>
                                    </td>
                                    <td style="padding: 12px;">
                                        <?php if ($admin['id'] != $_SESSION['admin_id']): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="action" value="delete_admin">
                                                <input type="hidden" name="admin_id" value="<?php echo $admin['id']; ?>">
                                                <button type="submit" onclick="return confirm('Are you sure?')" style="padding: 6px 12px; background: #dc3545; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 14px;">Delete</button>
                                            </form>
                                        <?php else: ?>
                                            <span style="color: #999; font-size: 14px;">-</span>
                                        <?php endif; ?>
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

</body>
</html>
