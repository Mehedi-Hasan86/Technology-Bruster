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
    
    if ($action === 'add_category') {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (empty($name)) {
            $error = '❌ Category name required';
        } else {
            $stmt = $conn->prepare("INSERT INTO categories (name, description, is_active) VALUES (?, ?, 1)");
            if ($stmt) {
                $stmt->bind_param("ss", $name, $description);
                if ($stmt->execute()) {
                    $success = '✅ Category added successfully';
                } else {
                    $error = '❌ Error: ' . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
    elseif ($action === 'delete_category') {
        $category_id = (int)($_POST['category_id'] ?? 0);
        
        // Check if category has products
        $check = $conn->query("SELECT COUNT(*) as count FROM products WHERE category_id = $category_id");
        $has_products = $check ? $check->fetch_assoc()['count'] : 0;
        
        if ($has_products > 0) {
            $error = '❌ Cannot delete category with products. Move products first.';
        } else {
            $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $category_id);
                if ($stmt->execute()) {
                    $success = '✅ Category deleted';
                }
                $stmt->close();
            }
        }
    }
}

// Get all categories
$categories = $conn->query("SELECT * FROM categories ORDER BY created_at DESC");
$category_list = $categories ? $categories->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - Admin</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/public/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .category-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            border-left: 4px solid #1f81eaff;
        }

        .category-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
        }

        .category-name {
            font-weight: 600;
            font-size: 1.05rem;
            color: var(--dark);
        }

        .product-count {
            background: #e3f2fd;
            color: #1976d2;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .category-description {
            color: #666;
            font-size: 0.95rem;
            margin-bottom: 12px;
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

        .btn-delete {
            background: #ffcdd2;
            color: #c62828;
        }

        .btn-delete:hover {
            background: #c62828;
            color: white;
        }
    </style>
</head>
<body>
<?php include INCLUDES_PATH . '/admin-navbar.php'; ?>

<div style="display: flex; gap: 24px; padding: 24px;">
    <?php include INCLUDES_PATH . '/admin-sidebar.php'; ?>

    <div style="flex: 1; max-width: 1000px;">
        <h1 style="font-size: 28px; margin-bottom: 24px;">📂 Manage Categories</h1>

        <?php if ($error): ?>
            <div style="background: #ffebee; color: #c62828; padding: 12px; border-radius: 8px; margin-bottom: 16px;">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div style="background: #e8f5e9; color: #2e7d32; padding: 12px; border-radius: 8px; margin-bottom: 16px;">
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <!-- Add Category Form -->
        <div style="background: white; padding: 24px; border-radius: 10px; margin-bottom: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
            <h2 style="font-size: 20px; margin-bottom: 16px; font-weight: 600;">Add New Category</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add_category">
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div class="form-group">
                        <label>Category Name *</label>
                        <input type="text" name="name" placeholder="e.g., Phones, Laptops, Cameras" required style="padding: 10px; border: 1px solid #ddd; border-radius: 6px; width: 100%;">
                    </div>

                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 10px;">Add Category</button>
                    </div>
                </div>

                <div class="form-group">
                    <label>Description (Optional)</label>
                    <textarea name="description" placeholder="Category description..." style="padding: 10px; border: 1px solid #ddd; border-radius: 6px; width: 100%; height: 80px; resize: vertical;"></textarea>
                </div>
            </form>
        </div>

        <!-- Categories List -->
        <div style="background: white; padding: 24px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
            <h2 style="font-size: 20px; margin-bottom: 16px; font-weight: 600;">All Categories (<?php echo count($category_list); ?>)</h2>
            
            <?php if (empty($category_list)): ?>
                <p style="opacity: 0.7; text-align: center; padding: 20px;">No categories yet</p>
            <?php else: ?>
                <?php foreach ($category_list as $cat): ?>
                    <div class="category-card">
                        <div class="category-header">
                            <div>
                                <div class="category-name"><?php echo htmlspecialchars($cat['name']); ?></div>
                                <?php if ($cat['description']): ?>
                                    <div class="category-description"><?php echo htmlspecialchars($cat['description']); ?></div>
                                <?php endif; ?>
                            </div>
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <?php 
                                $count = $conn->query("SELECT COUNT(*) as c FROM products WHERE category_id = {$cat['id']}");
                                $prod_count = $count ? $count->fetch_assoc()['c'] : 0;
                                ?>
                                <span class="product-count"><?php echo $prod_count; ?> product<?php echo $prod_count !== 1 ? 's' : ''; ?></span>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this category?');">
                                    <input type="hidden" name="action" value="delete_category">
                                    <input type="hidden" name="category_id" value="<?php echo $cat['id']; ?>">
                                    <button type="submit" class="btn-action btn-delete">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>
</body>
</html>
