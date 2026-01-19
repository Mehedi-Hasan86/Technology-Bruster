<?php
$password = '123456';
$hash = password_hash($password, PASSWORD_BCRYPT);

echo "<h2>Password Hash Generator</h2>";
echo "<p><strong>Password:</strong> " . htmlspecialchars($password) . "</p>";
echo "<p><strong>Hash:</strong> " . htmlspecialchars($hash) . "</p>";
echo "<p><strong>Length:</strong> " . strlen($hash) . " characters</p>";

// Test if it matches
$test = password_verify($password, $hash);
echo "<p><strong>Verification:</strong> " . ($test ? '✅ PASS' : '❌ FAIL') . "</p>";

// Copy this hash and run in SQL
echo "<hr>";
echo "<h3>Run this SQL in phpMyAdmin:</h3>";
echo "<pre>";
echo "INSERT INTO admin_users (name, email, password, role) VALUES \n";
echo "('Admin User', 'admin@test.com', '" . $hash . "', 'super_admin');\n";
echo "</pre>";
?>
