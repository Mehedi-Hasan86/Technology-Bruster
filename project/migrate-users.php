<?php
require_once __DIR__ . '/loader.php';

echo "<h1>Database Migration - Users Table</h1>";
echo "<hr>";

$columns_to_add = [
    'profile_pic' => "VARCHAR(255) AFTER password",
    'phone' => "VARCHAR(15) AFTER profile_pic",
    'bio' => "TEXT AFTER phone"
];

foreach ($columns_to_add as $col_name => $col_def) {
    // Check if column exists
    $result = $conn->query("SHOW COLUMNS FROM users LIKE '$col_name'");
    
    if ($result && $result->num_rows > 0) {
        echo "✅ Column '$col_name' already exists<br>";
    } else {
        // Add column
        $sql = "ALTER TABLE users ADD COLUMN $col_name $col_def";
        if ($conn->query($sql)) {
            echo "✅ Added column '$col_name'<br>";
        } else {
            echo "❌ Error adding '$col_name': " . $conn->error . "<br>";
        }
    }
}

echo "<hr>";
echo "<p>✅ Migration complete!</p>";
echo "<p><a href='" . SITE_URL . "/?page=profile'>Go to Profile</a></p>";
?>
