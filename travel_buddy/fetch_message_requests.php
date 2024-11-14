<?php
// fetch_message_requests.php

// Database connection
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'travel_buddy_finder';

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the logged-in user's id
$userId = 1;  // You can get the logged-in user id dynamically

$sql = "SELECT * FROM message_requests WHERE sender_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();

$messageRequests = [];
while ($row = $result->fetch_assoc()) {
    $messageRequests[] = $row;
}

header('Content-Type: application/json');
echo json_encode($messageRequests);

$stmt->close();
$conn->close();
?>
