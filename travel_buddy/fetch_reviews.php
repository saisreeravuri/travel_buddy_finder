<?php
session_start();
include 'config.php';

if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
    $sql = "SELECT * FROM reviews WHERE reviewed_user_id = ? ORDER BY timestamp DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reviews</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <h2>Reviews for User</h2>
    <div>
        <?php
        if (isset($result) && $result->num_rows > 0) {
            while ($review = $result->fetch_assoc()) {
                echo "<div class='review'>";
                echo "<p><strong>Rating: " . htmlspecialchars($review['rating']) . "/5</strong></p>";
                echo "<p>Comments: " . htmlspecialchars($review['comments']) . "</p>";
                echo "<p><small>Reviewed by: " . htmlspecialchars($review['user_id']) . " on " . htmlspecialchars($review['timestamp']) . "</small></p>";
                echo "</div>";
            }
        } else {
            echo "<p>No reviews found for this user.</p>";
        }
        ?>
    </div>
</body>
</html>
