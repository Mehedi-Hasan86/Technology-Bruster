<?php
require_once __DIR__ . '/loader.php';

// Check if admin already exists
$check = $conn->query("SELECT id FROM admin_users WHERE email = 'admin@test.com'");

if ($check && $check->num_rows === 0) {
    // Create admin user
    $password = password_hash('123456', PASSWORD_BCRYPT);
    
    $stmt = $conn->prepare("INSERT INTO admin_users (name, email, password, role) VALUES (?, ?, ?, ?)");
    if ($stmt) {
        $name = 'Admin User';
        $email = 'admin@test.com';
        $role = 'super_admin';
        
        $stmt->bind_param("ssss", $name, $email, $password, $role);
        
        if ($stmt->execute()) {
            echo "✅ Admin user created successfully!<br>";
            echo "Email: admin@test.com<br>";
            echo "Password: 123456<br>";
            echo "<a href='" . SITE_URL . "/?page=admin'>Go to Admin Login</a>";
        } else {
            echo "❌ Failed to create admin: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "❌ Database error: " . $conn->error;
    }
} else {
    echo "✅ Admin user already exists!<br>";
    echo "Email: admin@test.com<br>";
    echo "Password: 123456<br>";
    echo "<a href='" . SITE_URL . "/?page=admin'>Go to Admin Login</a>";
}
?>
