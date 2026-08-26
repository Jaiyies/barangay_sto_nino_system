<?php
// test_db.php
require_once 'config/database.php';

$db = new Database();
$conn = $db->getConnection();

echo "<h1>✅ Database Connection Test</h1>";
echo "<p>Connected to: " . $conn->getAttribute(PDO::ATTR_CONNECTION_STATUS) . "</p>";
echo "<p>Database: barangay_online_services</p>";
echo "<p>Port: 3307</p>";
?>