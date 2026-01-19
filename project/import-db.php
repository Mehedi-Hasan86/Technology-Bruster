<?php
require_once __DIR__ . '/loader.php';

echo "<h1>Database Import</h1>";
echo "<hr>";

// Read schema file
$schema_file = CONFIG_PATH . '/schema.sql';

if (!file_exists($schema_file)) {
    die("❌ Schema file not found: " . $schema_file);
}

$sql = file_get_contents($schema_file);

// Split queries
$queries = explode(';', $sql);

$count = 0;
$errors = [];

foreach ($queries as $query) {
    $query = trim($query);
    
    if (empty($query)) {
        continue;
    }
    
    if ($conn->query($query)) {
        $count++;
        echo "✅ " . substr($query, 0, 50) . "...<br>";
    } else {
        $errors[] = $conn->error;
        echo "❌ Error: " . $conn->error . "<br>";
    }
}

echo "<hr>";
echo "<h2>Results</h2>";
echo "<p>✅ Executed: " . $count . " queries</p>";

if (!empty($errors)) {
    echo "<p>❌ Errors: " . count($errors) . "</p>";
} else {
    echo "<p>✅ No errors!</p>";
}

echo "<hr>";
echo "<h2>Next Steps</h2>";
echo "<p>1. <a href='" . SITE_URL . "/setup.php'>Create Admin User</a></p>";
echo "<p>2. <a href='" . SITE_URL . "/?page=admin'>Go to Admin Login</a></p>";
?>
