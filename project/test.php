<?php
echo "✅ PHP is working<br>";

$conn = new mysqli('localhost', 'root', '', 'technology_bruster');

if ($conn->connect_error) {
    echo "❌ Database Error: " . $conn->connect_error;
} else {
    echo "✅ Database Connected<br>";
    
    $result = $conn->query("SHOW TABLES");
    echo "✅ Tables: " . $result->num_rows;
}
?>
