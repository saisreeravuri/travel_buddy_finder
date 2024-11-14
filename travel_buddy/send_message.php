<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$sender_id = $_SESSION['user_id'];
$receiver_id = $_GET['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['message_content'])) {
    $message_content = $_POST['message_content'];
    
    // Insert the message into the database
    $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $sender_id, $receiver_id, $message_content);
    $stmt->execute();
    $stmt->close();

    header("Location: profile.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Message</title>
</head>
<body>

    <div class="container">
        <h2>Send Message</h2>
        
        <form method="post">
            <textarea name="message_content" rows="5" cols="50" required></textarea><br><br>
            <button type="submit">Send Message</button>
        </form>
    </div>

</body>
</html>
