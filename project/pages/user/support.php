<?php
// Loader already included by index.php

if (!isset($_SESSION['user_id'])) {
    header('Location: ' . SITE_URL . '/?page=login');
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Handle new ticket submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($subject) || empty($message)) {
        $error = '❌ Subject and message are required';
    } else {
        $stmt = $conn->prepare("INSERT INTO support_tickets (user_id, subject, message, status) VALUES (?, ?, ?, 'open')");
        if ($stmt) {
            $stmt->bind_param("iss", $user_id, $subject, $message);
            if ($stmt->execute()) {
                $success = '✅ Support ticket created successfully. Our team will respond soon!';
            } else {
                $error = '❌ Failed to create ticket';
            }
            $stmt->close();
        }
    }
}

// Get user's tickets
$tickets = $conn->query("SELECT * FROM support_tickets WHERE user_id = $user_id ORDER BY created_at DESC");
$ticket_list = $tickets ? $tickets->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support - <?php echo SITE_NAME; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/public/css/style.css">
    
    <style>
        :root {
            --primary: #007bff;
            --light: #f8f9fa;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            padding: 40px 0;
        }

        .support-container {
            max-width: 900px;
        }

        .support-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
        }

        .support-header h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .nav-tabs {
            border-bottom: 2px solid #e0e0e0;
            margin-bottom: 30px;
        }

        .nav-tabs .nav-link {
            color: #999;
            border: none;
            padding: 15px 20px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .nav-tabs .nav-link:hover {
            color: var(--primary);
            border-bottom: 3px solid var(--primary);
        }

        .nav-tabs .nav-link.active {
            color: var(--primary);
            border-bottom: 3px solid var(--primary);
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
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--dark);
        }

        .form-control {
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            transition: all 0.3s;
            background: var(--light);
        }

        .form-control:focus {
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
            outline: none;
        }

        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }

        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }

        .ticket-item {
            background: var(--light);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 15px;
            border-left: 4px solid var(--primary);
            transition: all 0.3s;
        }

        .ticket-item:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        .ticket-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .ticket-subject {
            font-weight: 600;
            font-size: 1.05rem;
            color: var(--dark);
        }

        .ticket-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-open {
            background: #fff3cd;
            color: #856404;
        }

        .status-in-progress {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-closed {
            background: #d4edda;
            color: #155724;
        }

        .ticket-meta {
            font-size: 0.9rem;
            color: #999;
            margin-bottom: 10px;
        }

        .ticket-message {
            color: #666;
            line-height: 1.6;
        }

        .message {
            padding: 14px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-left: 4px solid;
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

        .empty-state {
            text-align: center;
            padding: 60px 20px;
        }

        .empty-state i {
            font-size: 3rem;
            color: #ccc;
            margin-bottom: 15px;
        }

        .empty-state p {
            color: #999;
            font-size: 1.1rem;
        }
    </style>
</head>
<body>
<?php include INCLUDES_PATH . '/navbar.php'; ?>

<div class="container support-container py-5">
    <!-- Header -->
    <div class="support-header">
        <h1><i class="fas fa-headset"></i> Support Center</h1>
        <p>Get help from our support team</p>
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
            <a class="nav-link active" id="new-tab" data-bs-toggle="tab" href="#new">
                <i class="fas fa-plus-circle"></i> New Ticket
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="tickets-tab" data-bs-toggle="tab" href="#tickets">
                <i class="fas fa-list"></i> My Tickets
            </a>
        </li>
    </ul>

    <div class="tab-content">
        <!-- New Ticket Tab -->
        <div class="tab-pane fade show active" id="new">
            <form method="POST">
                <div class="form-group">
                    <label>Subject</label>
                    <input type="text" class="form-control" name="subject" 
                           placeholder="Brief description of your issue" required>
                </div>

                <div class="form-group">
                    <label>Message</label>
                    <textarea class="form-control" name="message" 
                              placeholder="Describe your issue in detail..." required></textarea>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane"></i> Submit Ticket
                </button>
            </form>
        </div>

        <!-- My Tickets Tab -->
        <div class="tab-pane fade" id="tickets">
            <?php if (empty($ticket_list)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>No support tickets yet</p>
                    <p style="font-size: 0.95rem; margin-top: 10px; color: #ccc;">
                        Create a new ticket if you need assistance
                    </p>
                </div>
            <?php else: ?>
                <div style="display: grid; gap: 15px;">
                    <?php foreach ($ticket_list as $ticket): ?>
                        <div class="ticket-item">
                            <div class="ticket-header">
                                <div class="ticket-subject"><?php echo htmlspecialchars($ticket['subject']); ?></div>
                                <span class="ticket-status status-<?php echo $ticket['status']; ?>">
                                    <?php echo ucfirst($ticket['status']); ?>
                                </span>
                            </div>
                            <div class="ticket-meta">
                                <i class="fas fa-calendar"></i> 
                                <?php echo date('M d, Y H:i', strtotime($ticket['created_at'])); ?>
                            </div>
                            <div class="ticket-message">
                                <?php echo htmlspecialchars(substr($ticket['message'], 0, 200)); ?>
                                <?php if (strlen($ticket['message']) > 200): ?>
                                    <span style="color: var(--primary); cursor: pointer;">... more</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>

<!-- Bootstrap JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

<script>
    // Auto-hide messages
    setTimeout(() => {
        document.querySelectorAll('.message').forEach(msg => {
            msg.style.display = 'none';
        });
    }, 5000);
</script>

</body>
</html>
