<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $reviewed_user_id = $_POST['reviewed_user_id'];
    $rating = $_POST['rating'];
    $comments = $_POST['comments'];

    $stmt = $conn->prepare("INSERT INTO reviews (user_id, reviewed_user_id, rating, comments) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $user_id, $reviewed_user_id, $rating, $comments);

    if ($stmt->execute()) {
        echo "Review submitted successfully!";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>
