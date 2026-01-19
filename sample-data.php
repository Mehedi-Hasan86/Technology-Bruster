<?php
require_once __DIR__ . '/loader.php';

echo "<h1>Sample Data Setup</h1>";
echo "<hr>";

// Create sample categories
echo "<h2>Creating Sample Categories...</h2>";

$categories = [
    ['Software', 'Software & Tools'],
    ['eBooks', 'Digital Books'],
    ['Templates', 'Design Templates']
];

foreach ($categories as $cat) {
    $stmt = $conn->prepare("INSERT IGNORE INTO categories (name, description, is_active) VALUES (?, ?, 1)");
    if ($stmt) {
        $stmt->bind_param("ss", $cat[0], $cat[1]);
        if ($stmt->execute()) {
            echo "✅ Category created: " . $cat[0] . "<br>";
        }
        $stmt->close();
    }
}

echo "<hr>";
echo "<h2>Creating Sample Products...</h2>";

// Create sample products
$products = [
    ['WordPress Theme', 'Professional WordPress theme for business', 49.99, 1],
    ['PHP Tutorial eBook', 'Complete guide to PHP programming', 19.99, 2],
    ['React Course', 'Learn React from scratch', 79.99, 1],
];

foreach ($products as $prod) {
    $stmt = $conn->prepare("INSERT INTO products (title, description, price, category_id, is_active) VALUES (?, ?, ?, ?, 1)");
    if ($stmt) {
        $stmt->bind_param("ssdi", $prod[0], $prod[1], $prod[2], $prod[3]);
        if ($stmt->execute()) {
            echo "✅ Product created: " . $prod[0] . "<br>";
        } else {
            echo "❌ Error: " . $stmt->error . "<br>";
        }
        $stmt->close();
    }
}

echo "<hr>";
echo "<h2>Creating Test User...</h2>";

// Create test user
$name = 'Test User';
$email = 'user@test.com';
$password = password_hash('123456', PASSWORD_BCRYPT);

$stmt = $conn->prepare("INSERT IGNORE INTO users (name, email, password) VALUES (?, ?, ?)");
if ($stmt) {
    $stmt->bind_param("sss", $name, $email, $password);
    if ($stmt->execute()) {
        echo "✅ Test user created: user@test.com<br>";
    }
    $stmt->close();
}

echo "<hr>";
echo "<h2>✅ Sample Data Setup Complete!</h2>";
echo "<p><a href='" . SITE_URL . "'>Go to Home Page</a></p>";
?>
