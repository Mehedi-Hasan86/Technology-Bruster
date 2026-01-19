<?php
require_once '../../config/database.php';
require_once '../../config/settings.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: ' . SITE_URL . '/?page=admin');
    exit;
}

$tickets = $conn->query("SELECT t.*, u.name, u.email FROM support_tickets t JOIN users u ON t.user_id = u.id ORDER BY t.created_at DESC")->fetch_all(MYSQLI_ASSOC);

$faqs = $conn->query("SELECT * FROM faq ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Management - Admin</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/public/css/style.css">
</head>
<body>
<?php include '../../includes/admin-navbar.php'; ?>

<div style="display: flex; gap: 24px; padding: 24px;">
    <?php include '../../includes/admin-sidebar.php'; ?>

    <div class="main" style="flex: 1;">
        <h1 style="font-size: 28px; margin-bottom: 24px;">Support Management</h1>

        <!-- Tabs -->
        <div style="display: flex; gap: 8px; margin-bottom: 24px; border-bottom: 1px solid var(--gray);">
            <button onclick="showTab('tickets')" class="tab-btn active" style="padding: 12px 16px; border: none; background: none; cursor: pointer; border-bottom: 3px solid var(--primary); font-weight: 600;">Tickets</button>
            <button onclick="showTab('faq')" class="tab-btn" style="padding: 12px 16px; border: none; background: none; cursor: pointer; font-weight: 600;">FAQ</button>
        </div>

        <!-- Tickets Tab -->
        <div id="tickets" class="tab-content">
            <div style="background: white; padding: 24px; border-radius: 8px;">
                <table>
                    <thead>
                        <tr>
                            <th>Subject</th>
                            <th>User</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tickets as $t): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(substr($t['subject'], 0, 40)); ?></td>
                                <td><?php echo htmlspecialchars($t['name']); ?></td>
                                <td><span style="background: <?php echo $t['status'] == 'closed' ? '#f0f0f0' : '#e3f2fd'; ?>; padding: 4px 8px; border-radius: 4px;"><?php echo ucfirst($t['status']); ?></span></td>
                                <td><?php echo date('M d, Y', strtotime($t['created_at'])); ?></td>
                                <td><a href="#" style="color: var(--primary); text-decoration: none;">View</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- FAQ Tab -->
        <div id="faq" class="tab-content" style="display: none;">
            <div style="background: white; padding: 24px; border-radius: 8px; margin-bottom: 24px;">
                <h2 style="font-size: 18px; margin-bottom: 16px;">Add FAQ</h2>
                <form method="POST" style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <input type="hidden" name="action" value="add_faq">
                    
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label>Question</label>
                        <input type="text" name="question" required>
                    </div>

                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label>Answer</label>
                        <textarea name="answer" rows="4" required></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="grid-column: 1 / -1; padding: 12px;">Add FAQ</button>
                </form>
            </div>

            <div style="background: white; padding: 24px; border-radius: 8px;">
                <h2 style="font-size: 18px; margin-bottom: 16px;">All FAQs</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Question</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($faqs as $f): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(substr($f['question'], 0, 50)); ?></td>
                                <td><span style="background: <?php echo $f['is_active'] ? '#e8f5e9' : '#fff3e0'; ?>; padding: 4px 8px; border-radius: 4px;"><?php echo $f['is_active'] ? 'Active' : 'Inactive'; ?></span></td>
                                <td>
                                    <button onclick="toggleFAQ(<?php echo $f['id']; ?>, <?php echo $f['is_active'] ? 0 : 1; ?>)" class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px;">
                                        <?php echo $f['is_active'] ? 'Disable' : 'Enable'; ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>
<script>
function showTab(tabName) {
    const tabs = document.querySelectorAll('.tab-content');
    tabs.forEach(tab => tab.style.display = 'none');
    document.getElementById(tabName).style.display = 'block';

    const btns = document.querySelectorAll('.tab-btn');
    btns.forEach(btn => btn.style.borderBottom = 'none');
    event.target.style.borderBottom = '3px solid var(--primary)';
}

function toggleFAQ(id, active) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '<?php echo SITE_URL; ?>/api/admin.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        location.reload();
    };
    xhr.send('action=toggle_faq&faq_id=' + id + '&active=' + active);
}
</script>
</body>
</html>
