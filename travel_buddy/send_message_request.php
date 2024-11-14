<?php
// send_message_request.php

// Database connection
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'travel_buddy_finder';

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the form data
$recipient = $_POST['recipient'];
$message = $_POST['message'];
$userId = 1;  // You can get the logged-in user id dynamically

// Insert the message into the database
$sql = "INSERT INTO message_requests (sender_id, recipient, message) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('iss', $userId, $recipient, $message);

if ($stmt->execute()) {
    echo "Message sent successfully!";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
