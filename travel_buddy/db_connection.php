<?php
// Database connection details
$host = 'localhost';   // Your database host
$username = 'root';    // Your database username (default is usually 'root' for XAMPP)
$password = '';        // Your database password (default is empty for XAMPP)
$dbname = 'travel_buddy_finder';  // Your database name

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
