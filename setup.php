<?php
require_once __DIR__ . '/loader.php';

echo "<h1>Technology-Bruster Setup</h1>";
echo "<hr>";

// Step 1: Create admin_users table if not exists
echo "<h2>Step 1: Creating admin_users table...</h2>";

$create_table = "CREATE TABLE IF NOT EXISTS admin_users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'editor') DEFAULT 'editor',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email)
)";

if ($conn->query($create_table)) {
    echo "✅ admin_users table ready<br>";
} else {
    echo "❌ Error creating table: " . $conn->error . "<br>";
    exit;
}

// Step 2: Delete old admin if exists
echo "<h2>Step 2: Checking for existing admin...</h2>";

$delete = $conn->query("DELETE FROM admin_users WHERE email = 'admin@test.com'");
if ($delete) {
    echo "✅ Old admin removed<br>";
} else {
    echo "⚠️ No old admin found<br>";
}

// Step 3: Create new admin user
echo "<h2>Step 3: Creating new admin user...</h2>";

$password = '123456';
$hash = password_hash($password, PASSWORD_BCRYPT);

echo "Generated Hash: <code>" . $hash . "</code><br>";

$stmt = $conn->prepare("INSERT INTO admin_users (name, email, password, role) VALUES (?, ?, ?, ?)");

if (!$stmt) {
    echo "❌ Prepare error: " . $conn->error . "<br>";
    exit;
}

$name = 'Admin User';
$email = 'admin@test.com';
$role = 'super_admin';

$stmt->bind_param("ssss", $name, $email, $hash, $role);

if ($stmt->execute()) {
    echo "✅ Admin user created successfully!<br>";
} else {
    echo "❌ Error inserting admin: " . $stmt->error . "<br>";
    exit;
}

$stmt->close();

// Step 4: Verify admin exists
echo "<h2>Step 4: Verifying admin user...</h2>";

$check = $conn->query("SELECT id, email, password FROM admin_users WHERE email = 'admin@test.com'");

if ($check && $check->num_rows > 0) {
    $admin = $check->fetch_assoc();
    echo "✅ Admin found in database<br>";
    echo "Email: " . htmlspecialchars($admin['email']) . "<br>";
    echo "Password Hash Length: " . strlen($admin['password']) . "<br>";
    
    // Test password verification
    $test = password_verify('123456', $admin['password']);
    echo "Password Verification: " . ($test ? '✅ PASS' : '❌ FAIL') . "<br>";
} else {
    echo "❌ Admin not found in database<br>";
    exit;
}

// Step 5: Success
echo "<hr>";
echo "<h2>✅ Setup Complete!</h2>";
echo "<p><strong>Admin Credentials:</strong></p>";
echo "<p>Email: <code>admin@test.com</code><br>";
echo "Password: <code>123456</code></p>";
echo "<p><a href='" . SITE_URL . "/?page=admin' style='color: #007bff; text-decoration: none; font-weight: bold;'>Go to Admin Login →</a></p>";
?>
