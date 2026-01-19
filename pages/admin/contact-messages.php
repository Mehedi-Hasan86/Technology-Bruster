<?php
// Loader already included by index.php

if (!isset($_SESSION['admin_id'])) {
    header('Location: ' . SITE_URL . '/?page=admin');
    exit;
}

$error = '';
$success = '';

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'delete_message') {
        $message_id = (int)($_POST['message_id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM contact_messages WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $message_id);
            if ($stmt->execute()) {
                $success = '✅ Message deleted';
            }
            $stmt->close();
        }
    }
    elseif ($action === 'mark_replied') {
        $message_id = (int)($_POST['message_id'] ?? 0);
        $stmt = $conn->prepare("UPDATE contact_messages SET is_replied = 1 WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $message_id);
            if ($stmt->execute()) {
                $success = '✅ Message marked as replied';
            }
            $stmt->close();
        }
    }
}

// Get all contact messages
$messages = $conn->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
$message_list = $messages ? $messages->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Messages - Admin</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/public/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #8bb4e0ff;
            --light: #f8f9fa;
        }

        .message-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-left: 4px solid #ddd;
            transition: all 0.3s;
        }

        .message-card:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .message-card.replied {
            border-left-color: #28a745;
            background: #f0f8f5;
        }

        .message-card.unreplied {
            border-left-color: #ffc107;
            background: #fffbf0;
        }

        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .message-name {
            font-weight: 600;
            font-size: 1.05rem;
            color: var(--dark);
        }

        .message-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-replied {
            background: #d4edda;
            color: #155724;
        }

        .status-unreplied {
            background: #fff3cd;
            color: #856404;
        }

        .message-subject {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 8px;
        }

        .message-email {
            font-size: 0.9rem;
            color: #999;
            margin-bottom: 8px;
        }

        .message-date {
            font-size: 0.85rem;
            color: #999;
            margin-bottom: 12px;
        }

        .message-content {
            background: white;
            padding: 15px;
            border-radius: 8px;
            color: #666;
            line-height: 1.6;
            margin-bottom: 12px;
            font-size: 0.95rem;
        }

        .message-actions {
            display: flex;
            gap: 8px;
        }

        .btn-action {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.85rem;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-reply {
            background: #e3f2fd;
            color: #1976d2;
        }

        .btn-reply:hover {
            background: #1976d2;
            color: white;
        }

        .btn-delete {
            background: #ffcdd2;
            color: #c62828;
        }

        .btn-delete:hover {
            background: #c62828;
            color: white;
        }

        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .filter-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 8px 16px;
            border: 2px solid #e0e0e0;
            background: white;
            border-radius: 20px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            font-size: 0.9rem;
        }

        .filter-btn.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .filter-btn:hover {
            border-color: var(--primary);
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: white;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary);
        }

        .stat-label {
            font-size: 0.85rem;
            color: #999;
            margin-top: 5px;
        }

        .no-messages {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 10px;
        }

        .no-messages i {
            font-size: 3rem;
            color: #ccc;
            margin-bottom: 15px;
        }

        .no-messages p {
            color: #999;
            font-size: 1rem;
        }

        .success-msg {
            background: #e8f5e9;
            color: #2e7d32;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #2e7d32;
        }
    </style>
</head>
<body>
<?php include INCLUDES_PATH . '/admin-navbar.php'; ?>

<div style="display: flex; gap: 24px; padding: 24px;">
    <?php include INCLUDES_PATH . '/admin-sidebar.php'; ?>

    <div style="flex: 1; max-width: 1000px;">
        <h1 style="font-size: 28px; margin-bottom: 24px;">📧 Contact Messages</h1>

        <?php if ($success): ?>
            <div class="success-msg">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <!-- Statistics -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo count($message_list); ?></div>
                <div class="stat-label">Total Messages</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">
                    <?php 
                    $unreplied = array_filter($message_list, function($m) { return !$m['is_replied']; });
                    echo count($unreplied);
                    ?>
                </div>
                <div class="stat-label">Unreplied</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">
                    <?php 
                    $replied = array_filter($message_list, function($m) { return $m['is_replied']; });
                    echo count($replied);
                    ?>
                </div>
                <div class="stat-label">Replied</div>
            </div>
        </div>

        <!-- Filter -->
        <div class="filter-section">
            <label style="font-weight: 600; margin-bottom: 12px; display: block;">Filter Messages:</label>
            <div class="filter-buttons">
                <button class="filter-btn active" onclick="filterMessages('all')">All</button>
                <button class="filter-btn" onclick="filterMessages('unreplied')">Unreplied</button>
                <button class="filter-btn" onclick="filterMessages('replied')">Replied</button>
            </div>
        </div>

        <!-- Messages List -->
        <?php if (empty($message_list)): ?>
            <div class="no-messages">
                <i class="fas fa-envelope-open"></i>
                <p>No contact messages yet</p>
            </div>
        <?php else: ?>
            <div id="messages-container">
                <?php foreach ($message_list as $msg): ?>
                    <div class="message-card <?php echo $msg['is_replied'] ? 'replied' : 'unreplied'; ?>" 
                         data-status="<?php echo $msg['is_replied'] ? 'replied' : 'unreplied'; ?>">
                        <div class="message-header">
                            <div>
                                <div class="message-name">
                                    <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($msg['name']); ?>
                                </div>
                                <div class="message-email">
                                    <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($msg['email']); ?>
                                </div>
                            </div>
                            <span class="message-status <?php echo $msg['is_replied'] ? 'status-replied' : 'status-unreplied'; ?>">
                                <?php echo $msg['is_replied'] ? '✅ Replied' : '⏳ Unreplied'; ?>
                            </span>
                        </div>

                        <div class="message-date">
                            <i class="fas fa-calendar-alt"></i> <?php echo date('M d, Y H:i', strtotime($msg['created_at'])); ?>
                        </div>

                        <div class="message-subject">
                            Subject: <?php echo htmlspecialchars($msg['subject']); ?>
                        </div>

                        <div class="message-content">
                            <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                        </div>

                        <div class="message-actions">
                            <?php if (!$msg['is_replied']): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="mark_replied">
                                    <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
                                    <button type="submit" class="btn-action btn-reply">
                                        <i class="fas fa-check"></i> Mark as Replied
                                    </button>
                                </form>
                            <?php endif; ?>

                            <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this message?');">
                                <input type="hidden" name="action" value="delete_message">
                                <input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>">
                                <button type="submit" class="btn-action btn-delete">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>

                            <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>" class="btn-action" 
                               style="background: #e8f5e9; color: #2e7d32; text-decoration: none;">
                                <i class="fas fa-reply"></i> Reply Email
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>

<script>
    function filterMessages(status) {
        // Update active button
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        event.target.classList.add('active');

        // Filter cards
        const cards = document.querySelectorAll('.message-card');
        cards.forEach(card => {
            if (status === 'all') {
                card.style.display = 'block';
            } else {
                card.style.display = card.getAttribute('data-status') === status ? 'block' : 'none';
            }
        });
    }
</script>

</body>
</html>
