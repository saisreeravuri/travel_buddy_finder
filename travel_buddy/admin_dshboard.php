<?php
session_start();
include 'config.php';

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header("Location: login.html");
    exit();
}

$stmt_users = $conn->prepare("SELECT user_id, username, email FROM users");
$stmt_users->execute();
$result_users = $stmt_users->get_result();

echo "<h1>Admin Dashboard</h1>";
echo "<h3>Registered Users</h3>";
echo "<ul>";
while ($user = $result_users->fetch_assoc()) {
    echo "<li>{$user['username']} ({$user['email']})</li>";
}
echo "</ul>";
$stmt_users->close();

$stmt_reviews = $conn->prepare("SELECT users.username AS reviewer, reviews.reviewed_user_id, reviews.rating, reviews.comments 
                                FROM reviews 
                                JOIN users ON reviews.user_id = users.user_id");
$stmt_reviews->execute();
$result_reviews = $stmt_reviews->get_result();

echo "<h3>User Reviews</h3>";
echo "<ul>";
while ($review = $result_reviews->fetch_assoc()) {
    echo "<li>{$review['reviewer']} rated user ID {$review['reviewed_user_id']} with a rating of {$review['rating']}: {$review['comments']}</li>";
}
echo "</ul>";
$stmt_reviews->close();
?>
