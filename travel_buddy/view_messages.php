<?php
// view_message.php

// Database connection
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'your_database';

$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get message ID from URL
$messageId = $_GET['message_id'];

// Fetch the message from the database
$sql = "SELECT * FROM message_requests WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $messageId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $message = $result->fetch_assoc();
    echo "<h1>Message from: " . htmlspecialchars($message['recipient']) . "</h1>";
    echo "<p>" . nl2br(htmlspecialchars($message['message'])) . "</p>";
    echo "<p>Sent at: " . $message['sent_at'] . "</p>";
    echo "<p>Status: " . $message['status'] . "</p>";
} else {
    echo "<p>Message not found.</p>";
}

$stmt->close();
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Messages</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f3f4f6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .container {
            width: 100%;
            max-width: 700px;
            padding: 20px;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }
        .message-box {
            border-bottom: 1px solid #ddd;
            padding: 10px;
            margin-bottom: 10px;
        }
        .message-header {
            font-weight: bold;
            color: #555;
            margin-bottom: 5px;
        }
        .message-content {
            color: #333;
            margin-bottom: 5px;
        }
        .message-time {
            color: #888;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Your Messages</h2>
        <?php if ($messages_result->num_rows > 0): ?>
            <?php while ($message = $messages_result->fetch_assoc()): ?>
                <div class="message-box">
                    <div class="message-header">
                        Trip: <?php echo htmlspecialchars($message['destination']); ?><br>
                        From: <?php echo htmlspecialchars($message['sender_name']); ?><br>
                        To: <?php echo htmlspecialchars($message['receiver_name']); ?>
                    </div>
                    <div class="message-content">
                        <?php echo htmlspecialchars($message['message']); ?>
                    </div>
                    <div class="message-time">
                        Sent on: <?php echo htmlspecialchars($message['created_at']); ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No messages to display.</p>
        <?php endif; ?>
    </div>
</body>
</html>
