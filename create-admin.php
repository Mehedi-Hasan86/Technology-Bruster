<?php
require_once __DIR__ . '/loader.php';

echo "<h1>Create Admin User</h1>";
echo "<hr>";

$email = 'admin@test.com';
$password = '123456';
$name = 'Admin User';
$role = 'super_admin';

// Generate fresh password hash
$hash = password_hash($password, PASSWORD_BCRYPT);

echo "<p><strong>Creating admin with:</strong></p>";
echo "<ul>";
echo "<li>Email: $email</li>";
echo "<li>Password: $password</li>";
echo "<li>Hash: $hash</li>";
echo "</ul>";

// Delete existing admin if exists
$delete = $conn->query("DELETE FROM admin_users WHERE email = '$email'");
echo "<p>✅ Cleared old admin user</p>";

// Create new admin
$stmt = $conn->prepare("INSERT INTO admin_users (name, email, password, role) VALUES (?, ?, ?, ?)");

if (!$stmt) {
    echo "<p>❌ Prepare error: " . $conn->error . "</p>";
    exit;
}

$stmt->bind_param("ssss", $name, $email, $hash, $role);

if ($stmt->execute()) {
    echo "<p>✅ Admin user created successfully!</p>";
    echo "<hr>";
    echo "<h2>Login Details</h2>";
    echo "<p><strong>Email:</strong> admin@test.com</p>";
    echo "<p><strong>Password:</strong> 123456</p>";
    echo "<p><a href='" . SITE_URL . "/?page=admin' style='color: #007bff; text-decoration: none; font-weight: bold;'>Go to Admin Login →</a></p>";
} else {
    echo "<p>❌ Error: " . $stmt->error . "</p>";
}

$stmt->close();
?>
