<?php
// Loader already included by index.php

$page = isset($_GET['p']) && is_numeric($_GET['p']) ? (int)$_GET['p'] : 1;
$category_id = isset($_GET['category']) && is_numeric($_GET['category']) ? (int)$_GET['category'] : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$offset = ($page - 1) * ITEMS_PER_PAGE;

// Get all categories
$categories = $conn->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY name ASC");
$category_list = $categories ? $categories->fetch_all(MYSQLI_ASSOC) : [];

// Build query - Fixed to use proper table reference
$base_query = "FROM products p WHERE p.is_active = 1";
$where_parts = [];
$params = [];
$param_types = '';

if ($category_id > 0) {
    $where_parts[] = "p.category_id = ?";
    $params[] = $category_id;
    $param_types .= 'i';
}

if (!empty($search)) {
    $where_parts[] = "(p.title LIKE ? OR p.description LIKE ?)";
    $search_term = '%' . $search . '%';
    $params[] = $search_term;
    $params[] = $search_term;
    $param_types .= 'ss';
}

$where_clause = '';
if (!empty($where_parts)) {
    $where_clause = ' AND ' . implode(' AND ', $where_parts);
}

// Get total count
$count_query = "SELECT COUNT(*) as count $base_query $where_clause";
$count_stmt = $conn->prepare($count_query);

if ($count_stmt && !empty($params)) {
    $count_stmt->bind_param($param_types, ...$params);
}

if ($count_stmt) {
    $count_stmt->execute();
    $count_result = $count_stmt->get_result()->fetch_assoc();
    $total_products = $count_result ? ($count_result['count'] ?? 0) : 0;
    $count_stmt->close();
} else {
    // Fallback count query
    $fallback_count = "SELECT COUNT(*) as count FROM products WHERE is_active = 1";
    if ($category_id > 0) {
        $fallback_count .= " AND category_id = $category_id";
    }
    if (!empty($search)) {
        $search_escaped = $conn->real_escape_string($search);
        $fallback_count .= " AND (title LIKE '%$search_escaped%' OR description LIKE '%$search_escaped%')";
    }
    $count_result = $conn->query($fallback_count)->fetch_assoc();
    $total_products = $count_result ? ($count_result['count'] ?? 0) : 0;
}

$total_pages = $total_products > 0 ? ceil($total_products / ITEMS_PER_PAGE) : 1;

// Get products
$limit = ITEMS_PER_PAGE;
$product_query = "SELECT * $base_query $where_clause ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($product_query);

if (!$stmt) {
    die("❌ Query error: " . $conn->error);
}

$params[] = $limit;
$params[] = $offset;
$param_types .= 'ii';

$stmt->bind_param($param_types, ...$params);
if (!$stmt->execute()) {
    die("❌ Execution error: " . $stmt->error);
}
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Debug: Check if products are empty
if (empty($products) && $total_products > 0) {
    // Retry with a simpler query
    $fallback_query = "SELECT * FROM products WHERE is_active = 1";
    if ($category_id > 0) {
        $fallback_query .= " AND category_id = $category_id";
    }
    if (!empty($search)) {
        $search_escaped = $conn->real_escape_string($search);
        $fallback_query .= " AND (title LIKE '%$search_escaped%' OR description LIKE '%$search_escaped%')";
    }
    $fallback_query .= " ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
    $products = $conn->query($fallback_query)->fetch_all(MYSQLI_ASSOC);
}

// Get selected category
$selected_category = null;
if ($category_id > 0) {
    foreach ($category_list as $cat) {
        if ($cat['id'] == $category_id) {
            $selected_category = $cat;
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/public/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .filter-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 24px;
            box-shadow: 0 1px 4px rgba(0,0,0,0.1);
        }

        .category-filter {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            padding-bottom: 10px;
        }

        .category-btn {
            padding: 8px 16px;
            border: 2px solid #ddd;
            background: white;
            border-radius: 20px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            white-space: nowrap;
            font-size: 0.9rem;
        }

        .category-btn:hover {
            border-color: #007bff;
        }

        .category-btn.active {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }
    </style>
</head>
<body>
<?php include INCLUDES_PATH . '/navbar.php'; ?>

<div class="main-content">
    <div style="max-width: 1200px; margin: 0 auto; padding: 0 16px;">
        <h1 style="font-size: 24px; margin-bottom: 24px;">
            <?php echo $selected_category ? '📦 ' . htmlspecialchars($selected_category['name']) : '📦 All Products'; ?>
        </h1>

        <!-- Filter Section -->
        <div class="filter-section">
            <div style="margin-bottom: 16px;">
                <h3 style="font-size: 14px; font-weight: 600; margin-bottom: 12px;">Categories:</h3>
                <div class="category-filter">
                    <a href="<?php echo SITE_URL; ?>/?page=products" class="category-btn <?php echo $category_id === 0 ? 'active' : ''; ?>">
                        All Categories
                    </a>
                    <?php foreach ($category_list as $cat): ?>
                        <a href="<?php echo SITE_URL; ?>/?page=products&category=<?php echo $cat['id']; ?>" 
                           class="category-btn <?php echo $category_id === $cat['id'] ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Search -->
            <form method="GET" style="display: flex; gap: 8px;">
                <input type="hidden" name="page" value="products">
                <input type="text" name="search" placeholder="🔍 Search products..." 
                       value="<?php echo htmlspecialchars($search); ?>" 
                       style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
                <button type="submit" class="btn btn-primary" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600;">
                    <i class="fas fa-search"></i> Search
                </button>
                <?php if (!empty($search) || $category_id > 0): ?>
                    <a href="<?php echo SITE_URL; ?>/?page=products" class="btn btn-secondary" style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: 600; text-decoration: none;">
                        <i class="fas fa-times"></i> Clear
                    </a>
                <?php endif; ?>
                <?php if ($category_id > 0): ?>
                    <input type="hidden" name="category" value="<?php echo $category_id; ?>">
                <?php endif; ?>
            </form>

            <!-- Search Results Info -->
            <?php if (!empty($search) || $category_id > 0): ?>
                <div style="margin-top: 16px; padding: 12px; background: #e7f3ff; border-left: 4px solid #007bff; border-radius: 4px;">
                    <p style="margin: 0; font-size: 14px;">
                        <?php if (!empty($search) && $category_id > 0 && $selected_category): ?>
                            Found <strong><?php echo $total_products; ?></strong> products in <strong><?php echo htmlspecialchars($selected_category['name']); ?></strong> matching "<strong><?php echo htmlspecialchars($search); ?></strong>"
                        <?php elseif (!empty($search)): ?>
                            Found <strong><?php echo $total_products; ?></strong> products matching "<strong><?php echo htmlspecialchars($search); ?></strong>"
                        <?php elseif ($category_id > 0 && $selected_category): ?>
                            Found <strong><?php echo $total_products; ?></strong> products in <strong><?php echo htmlspecialchars($selected_category['name']); ?></strong>
                        <?php endif; ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Products Grid -->
        <?php if (empty($products)): ?>
            <div style="text-align: center; padding: 40px 0; background: white; border-radius: 8px;">
                <p style="font-size: 16px; opacity: 0.7; margin-bottom: 16px;">No products found</p>
                <a href="<?php echo SITE_URL; ?>/?page=products" class="btn btn-primary" style="padding: 10px 20px;">View All Products</a>
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px; margin-bottom: 40px;">
                <?php foreach ($products as $p): ?>
                    <div style="background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,0.1); transition: transform 0.3s;">
                        <div style="width: 100%; height: 180px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                            <?php if (!empty($p['image_path']) && file_exists(PRODUCT_IMG_DIR . $p['image_path'])): ?>
                                <img src="<?php echo SITE_URL; ?>/uploads/product_images/<?php echo htmlspecialchars($p['image_path']); ?>" 
                                     alt="<?php echo htmlspecialchars($p['title']); ?>"
                                     style="width: 100%; height: 100%; object-fit: cover;">
                            <?php else: ?>
                                <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 24px;">📦</div>
                            <?php endif; ?>
                        </div>

                        <div style="padding: 16px;">
                            <h3 style="font-weight: 600; margin-bottom: 8px; font-size: 16px;"><?php echo htmlspecialchars(substr($p['title'], 0, 50)); ?></h3>
                            <p style="font-size: 13px; opacity: 0.7; margin-bottom: 12px;"><?php echo htmlspecialchars(substr($p['description'], 0, 80)); ?></p>
                            <div style="font-size: 18px; color: var(--primary); font-weight: bold; margin-bottom: 12px;"><?php echo CURRENCY; ?> <?php echo number_format($p['price'], 0); ?></div>
                            <div style="display: flex; gap: 8px;">
                                <a href="<?php echo SITE_URL; ?>/?page=product&id=<?php echo $p['id']; ?>" class="btn btn-primary" style="flex: 1; text-align: center; padding: 10px; font-size: 12px; text-decoration: none;">View</a>
                                <button onclick="addToCart(<?php echo $p['id']; ?>)" class="btn btn-secondary" style="flex: 1; padding: 10px; font-size: 12px;">Cart</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div style="text-align: center; padding: 24px; display: flex; justify-content: center; gap: 8px; flex-wrap: wrap;">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="<?php echo SITE_URL; ?>/?page=products<?php echo $category_id > 0 ? '&category=' . $category_id : ''; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>&p=<?php echo $i; ?>" 
                           style="padding: 8px 12px; border-radius: 4px; background: <?php echo $i == $page ? 'var(--primary)' : 'var(--light)'; ?>; color: <?php echo $i == $page ? 'white' : 'black'; ?>; text-decoration: none;">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php include INCLUDES_PATH . '/footer.php'; ?>
<script src="<?php echo SITE_URL; ?>/public/js/main.js"></script>
</body>
</html>
