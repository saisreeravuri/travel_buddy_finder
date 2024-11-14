<?php
session_start();  // Start the session to access user data

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if the user is not logged in
    header("Location: login.html");
    exit();
}

// Include the database connection
include 'config.php';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get user input from the form
    $user_id = $_SESSION['user_id'];
    $destination = $_POST['destination'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $interests = $_POST['interests'];

    // Insert the new trip into the database
    $stmt = $conn->prepare("INSERT INTO trips (user_id, destination, start_date, end_date, interests) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $user_id, $destination, $start_date, $end_date, $interests);
    $stmt->execute();

    // Redirect back to the profile page after adding the trip
    header("Location: profile.php");
    exit();
}
?>
