<?php
// Loader already included by index.php

if (!isset($_SESSION['admin_id'])) {
    header('Location: ' . SITE_URL . '/?page=admin');
    exit;
}

$error = '';
$success = '';
$edit_product = null;

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'delete_product') {
        $product_id = (int)($_POST['product_id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $product_id);
            if ($stmt->execute()) {
                $success = '✅ Product deleted';
            }
            $stmt->close();
        }
    }
    elseif ($action === 'add_product' || $action === 'update_product') {
        $product_id = $action === 'update_product' ? (int)($_POST['product_id'] ?? 0) : null;
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 0);
        $category_id = (int)($_POST['category_id'] ?? 0);
        
        $category_id = $category_id > 0 ? $category_id : null;

        if (empty($title) || $price <= 0) {
            $error = '❌ Title and price required';
        } else {
            // Handle image upload
            $image_path = null;
            
            if ($action === 'update_product' && $product_id) {
                // Get existing image for update
                $existing = $conn->query("SELECT image_path FROM products WHERE id = $product_id");
                if ($existing) {
                    $row = $existing->fetch_assoc();
                    $image_path = $row['image_path'];
                }
            }
            
            if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
                $image_file = $_FILES['product_image'];
                $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                
                if (!in_array($image_file['type'], $allowed_types)) {
                    $error = '❌ Invalid image format. Use JPG, PNG, GIF, or WebP';
                } elseif ($image_file['size'] > 5 * 1024 * 1024) {
                    $error = '❌ Image size too large (max 5MB)';
                } else {
                    // Delete old image if exists
                    if ($image_path && file_exists(PRODUCT_IMG_DIR . $image_path)) {
                        @unlink(PRODUCT_IMG_DIR . $image_path);
                    }
                    
                    // Create unique filename
                    $filename = 'product_' . time() . '_' . rand(1000, 9999) . '.' . pathinfo($image_file['name'], PATHINFO_EXTENSION);
                    $upload_path = PRODUCT_IMG_DIR . $filename;
                    
                    if (move_uploaded_file($image_file['tmp_name'], $upload_path)) {
                        $image_path = $filename;
                    } else {
                        $error = '❌ Failed to upload image';
                    }
                }
            }
            
            if (empty($error)) {
                if ($action === 'add_product') {
                    $stmt = $conn->prepare("INSERT INTO products (title, description, price, quantity, category_id, image_path, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)");
                    if ($stmt) {
                        $stmt->bind_param("ssdisi", $title, $description, $price, $quantity, $category_id, $image_path);
                        if ($stmt->execute()) {
                            $success = '✅ Product added successfully';
                        } else {
                            $error = '❌ Error: ' . $stmt->error;
                        }
                        $stmt->close();
                    }
                } else {
                    // Update product
                    $stmt = $conn->prepare("UPDATE products SET title = ?, description = ?, price = ?, quantity = ?, category_id = ?, image_path = ? WHERE id = ?");
                    if ($stmt) {
                        $stmt->bind_param("ssdissi", $title, $description, $price, $quantity, $category_id, $image_path, $product_id);
                        if ($stmt->execute()) {
                            $success = '✅ Product updated successfully';
                        } else {
                            $error = '❌ Error: ' . $stmt->error;
                        }
                        $stmt->close();
                    }
                }
            }
        }
    }
}

// Get product to edit if editing
if (isset($_GET['edit_id'])) {
    $edit_id = (int)$_GET['edit_id'];
    $result = $conn->query("SELECT * FROM products WHERE id = $edit_id");
    if ($result) {
        $edit_product = $result->fetch_assoc();
    }
}

// Get products
$products = $conn->query("SELECT p.*, c.name as category FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.created_at DESC");
$products = $products ? $products->fetch_all(MYSQLI_ASSOC) : [];

// Get categories
$categories = $conn->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name ASC");
$categories = $categories ? $categories->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Admin</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/public/css/style.css">
</head>
<body>
<?php include INCLUDES_PATH . '/admin-navbar.php'; ?>

<div style="display: flex; gap: 24px; padding: 24px;">
    <?php include INCLUDES_PATH . '/admin-sidebar.php'; ?>

    <div style="flex: 1; max-width: 1000px;">
        <h1 style="font-size: 28px; margin-bottom: 24px;">
            <?php echo $edit_product ? '✏️ Edit Product' : '📦 Manage Products'; ?>
        </h1>

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

        <!-- Add/Edit Product Form -->
        <div style="background: white; padding: 24px; border-radius: 8px; margin-bottom: 24px; box-shadow: 0 1px 4px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h2 style="font-size: 20px; font-weight: 600;">
                    <?php echo $edit_product ? '✏️ Edit Product Details' : '➕ Add New Product'; ?>
                </h2>
                <?php if ($edit_product): ?>
                    <a href="<?php echo SITE_URL; ?>/?page=admin&action=products" style="color: var(--primary); text-decoration: none; font-size: 14px;">← Cancel Edit</a>
                <?php endif; ?>
            </div>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="<?php echo $edit_product ? 'update_product' : 'add_product'; ?>">
                <?php if ($edit_product): ?>
                    <input type="hidden" name="product_id" value="<?php echo $edit_product['id']; ?>">
                <?php endif; ?>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label>Product Title *</label>
                        <input type="text" name="title" required value="<?php echo htmlspecialchars($edit_product['title'] ?? ''); ?>" style="padding: 10px; border: 1px solid #ddd; border-radius: 6px; width: 100%;">
                    </div>

                    <div class="form-group">
                        <label>Category *</label>
                        <select name="category_id" required style="padding: 10px; border: 1px solid #ddd; border-radius: 6px; width: 100%;">
                            <option value="">-- Select Category --</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo ($edit_product && $edit_product['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Price (<?php echo CURRENCY; ?>) *</label>
                        <input type="number" name="price" step="0.01" min="0" required value="<?php echo htmlspecialchars($edit_product['price'] ?? ''); ?>" style="padding: 10px; border: 1px solid #ddd; border-radius: 6px; width: 100%;">
                    </div>

                    <div class="form-group">
                        <label>Stock/Quantity *</label>
                        <input type="number" name="quantity" min="0" required value="<?php echo htmlspecialchars($edit_product['quantity'] ?? 0); ?>" style="padding: 10px; border: 1px solid #ddd; border-radius: 6px; width: 100%;">
                    </div>

                    <div class="form-group">
                        <label>Product Image</label>
                        <input type="file" name="product_image" accept="image/*" onchange="previewImage(event)" style="padding: 10px; border: 1px solid #ddd; border-radius: 6px; width: 100%;">
                        <small style="opacity: 0.7;">Max 5MB, Recommended: 500x500px</small>
                    </div>

                    <!-- Image Preview -->
                    <div id="image-preview" style="display: <?php echo ($edit_product && $edit_product['image_path']) ? 'block' : 'none'; ?>; grid-column: 2; margin-top: 8px;">
                        <img id="preview-img" 
                             src="<?php echo ($edit_product && $edit_product['image_path']) ? SITE_URL . '/uploads/product_images/' . htmlspecialchars($edit_product['image_path']) : ''; ?>"
                             style="width: 100%; max-height: 200px; border-radius: 8px; object-fit: cover;">
                    </div>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="4" style="padding: 10px; border: 1px solid #ddd; border-radius: 6px; width: 100%; resize: vertical;"><?php echo htmlspecialchars($edit_product['description'] ?? ''); ?></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="padding: 12px 24px;">
                    <?php echo $edit_product ? 'Update Product' : 'Add Product'; ?>
                </button>
            </form>
        </div>

        <!-- Products List -->
        <div style="background: white; padding: 24px; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.1);">
            <h2 style="font-size: 20px; margin-bottom: 16px; font-weight: 600;">All Products (<?php echo count($products); ?>)</h2>
            
            <?php if (empty($products)): ?>
                <p style="opacity: 0.7; text-align: center; padding: 20px;">No products yet</p>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #f5f5f5;">
                                <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd; width: 60px;">Image</th>
                                <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Title</th>
                                <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Category</th>
                                <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Price</th>
                                <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Stock</th>
                                <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Status</th>
                                <th style="padding: 12px; text-align: left; border-bottom: 2px solid #ddd;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $p): ?>
                                <tr style="border-bottom: 1px solid #eee;">
                                    <td style="padding: 12px;">
                                        <?php if ($p['image_path']): ?>
                                            <img src="<?php echo SITE_URL; ?>/uploads/product_images/<?php echo htmlspecialchars($p['image_path']); ?>" 
                                                 style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;" alt="Product">
                                        <?php else: ?>
                                            <div style="width: 50px; height: 50px; background: #eee; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 20px;">📦</div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 12px;"><?php echo htmlspecialchars(substr($p['title'], 0, 40)); ?></td>
                                    <td style="padding: 12px; font-size: 12px;"><?php echo htmlspecialchars($p['category'] ?? 'No Category'); ?></td>
                                    <td style="padding: 12px; font-weight: 600;"><?php echo CURRENCY; ?> <?php echo number_format($p['price'], 0); ?></td>
                                    <td style="padding: 12px;">
                                        <span style="background: <?php echo $p['quantity'] > 0 ? '#e8f5e9' : '#ffebee'; ?>; color: <?php echo $p['quantity'] > 0 ? '#2e7d32' : '#c62828'; ?>; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;">
                                            <?php echo $p['quantity']; ?> units
                                        </span>
                                    </td>
                                    <td style="padding: 12px;">
                                        <span style="background: <?php echo $p['is_active'] ? '#e8f5e9' : '#fff3e0'; ?>; padding: 4px 8px; border-radius: 4px; font-size: 12px;">
                                            <?php echo $p['is_active'] ? '✅ Active' : '⭕ Inactive'; ?>
                                        </span>
                                    </td>
                                    <td style="padding: 12px; display: flex; gap: 8px;">
                                        <a href="<?php echo SITE_URL; ?>/?page=admin&action=products&edit_id=<?php echo $p['id']; ?>" 
                                           style="background: #e3f2fd; color: #1976d2; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 600; text-decoration: none;">
                                            ✏️ Edit
                                        </a>
                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this product?');">
                                            <input type="hidden" name="action" value="delete_product">
                                            <input type="hidden" name="product_id" value="<?php echo $p['id']; ?>">
                                            <button type="submit" style="background: #ffcdd2; color: #c62828; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; font-size: 12px; font-weight: 600;">
                                                🗑️ Delete
                                            </button>
                                        </form>
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

<script>
function previewImage(event) {
    const file = event.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-img').src = e.target.result;
            document.getElementById('image-preview').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
}
</script>

</body>
</html>