<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $destination = $_POST['destination'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $interests = $_POST['interests'];

    $stmt = $conn->prepare("INSERT INTO trips (user_id, destination, start_date, end_date, interests) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $user_id, $destination, $start_date, $end_date, $interests);

    if ($stmt->execute()) {
        echo "Trip added successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>
