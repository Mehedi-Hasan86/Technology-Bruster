<?php
// Loader already included by index.php - no require needed

if (!isset($_SESSION['admin_id'])) {
    header('Location: ' . SITE_URL . '/?page=admin');
    exit;
}

// Handle block/unblock
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = (int)($_POST['user_id'] ?? 0);
    $block = (int)($_POST['block'] ?? 0);
    
    $stmt = $conn->prepare("UPDATE users SET is_blocked = ? WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("ii", $block, $user_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Get users
$users = $conn->query("SELECT u.*, COUNT(o.id) as orders_count, SUM(o.total_amount) as total_spent 
                      FROM users u 
                      LEFT JOIN orders o ON u.id = o.user_id AND o.payment_status = 'success'
                      GROUP BY u.id 
                      ORDER BY u.created_at DESC");
$users = $users ? $users->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - Admin</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/public/css/style.css">
</head>
<body>
<?php include INCLUDES_PATH . '/admin-navbar.php'; ?>

<div style="display: flex; gap: 24px; padding: 24px;">
    <?php include INCLUDES_PATH . '/admin-sidebar.php'; ?>

    <div style="flex: 1; max-width: 1000px;">
        <h1 style="font-size: 28px; margin-bottom: 24px;">👥 Manage Users</h1>

        <div style="background: white; padding: 24px; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.1);">
            <?php if (empty($users)): ?>
                <p style="opacity: 0.7; text-align: center; padding: 20px;">No users found</p>
            <?php else: ?>
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="background: #f5f5f5;">
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Name</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Email</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Orders</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Spent</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Status</th>
                            <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 12px;"><?php echo htmlspecialchars($u['name']); ?></td>
                                <td style="padding: 12px; font-size: 12px;"><?php echo htmlspecialchars($u['email']); ?></td>
                                <td style="padding: 12px;"><?php echo $u['orders_count'] ?? 0; ?></td>
                                <td style="padding: 12px; font-weight: 600;"><?php echo CURRENCY; ?> <?php echo number_format($u['total_spent'] ?? 0, 0); ?></td>
                                <td style="padding: 12px;">
                                    <span style="background: <?php echo $u['is_blocked'] ? '#ffebee' : '#e8f5e9'; ?>; padding: 4px 8px; border-radius: 4px; font-size: 12px; color: <?php echo $u['is_blocked'] ? '#c62828' : '#2e7d32'; ?>;">
                                        <?php echo $u['is_blocked'] ? '🚫 Blocked' : '✅ Active'; ?>
                                    </span>
                                </td>
                                <td style="padding: 12px;">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                        <input type="hidden" name="block" value="<?php echo $u['is_blocked'] ? 0 : 1; ?>">
                                        <button type="submit" style="background: <?php echo $u['is_blocked'] ? '#28a745' : '#ffc107'; ?>; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 600;">
                                            <?php echo $u['is_blocked'] ? 'Unblock' : 'Block'; ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>
</body>
</html>
