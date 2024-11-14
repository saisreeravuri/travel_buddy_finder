<?php
// Database connection and session start
session_start();
include 'config.php';

$conn = new mysqli('localhost', 'root', '', 'travel_buddy_finder');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: travel_buddy/login.html");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$user_query = "SELECT username, email, profile_picture FROM users WHERE user_id = '$user_id'";
$user_result = $conn->query($user_query);
$user = $user_result->fetch_assoc();

$username = $user['username'] ?? 'Unknown User';
$email = $user['email'] ?? 'Not available';
$profile_picture = $user['profile_picture'] ?? 'default-profile.jpg';

// Update profile functionality
if (isset($_POST['update_profile'])) {
    $new_email = $_POST['email'];
    $stmt = $conn->prepare("UPDATE users SET email = ? WHERE user_id = ?");
    $stmt->bind_param("si", $new_email, $user_id);
    $stmt->execute();
    $email = $new_email;

    // Profile picture update
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $profile_picture_path = 'uploads/' . basename($_FILES['profile_picture']['name']);
        move_uploaded_file($_FILES['profile_picture']['tmp_name'], $profile_picture_path);

        $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE user_id = ?");
        $stmt->bind_param("si", $profile_picture_path, $user_id);
        $stmt->execute();
        $profile_picture = $profile_picture_path;
    }
}

// Handle adding a trip
if (isset($_POST['add_trip'])) {
    $destination = $_POST['destination'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    $stmt = $conn->prepare("INSERT INTO trips (user_id, destination, start_date, end_date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $destination, $start_date, $end_date);
    $stmt->execute();
}

// Handle editing a trip
if (isset($_POST['edit_trip'])) {
    $trip_id = $_POST['trip_id'];
    $destination = $_POST['destination'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    $stmt = $conn->prepare("UPDATE trips SET destination = ?, start_date = ?, end_date = ? WHERE trip_id = ?");
    $stmt->bind_param("sssi", $destination, $start_date, $end_date, $trip_id);
    $stmt->execute();
}

// Handle deleting a trip
if (isset($_POST['delete_trip'])) {
    $trip_id = $_POST['trip_id'];
    $stmt = $conn->prepare("DELETE FROM trips WHERE trip_id = ?");
    $stmt->bind_param("i", $trip_id);
    $stmt->execute();
}

// Fetch trips
$trips_result = $conn->query("SELECT * FROM trips WHERE user_id = '$user_id'");

// Fetch all users for message sending
$users_result = $conn->query("SELECT * FROM users");

/// Send message functionality
// Fetch sent messages with receiver's profile picture

// query("SELECT mr.*, u.username, u.profile_picture AS receiver_profile_picture 
//                                FROM message_requests mr 
//                                JOIN users u ON u.user_id = mr.receiver_id 
//                                WHERE mr.sender_id = '$user_id'")->fetch_all(MYSQLI_ASSOC);
$query ="
SELECT u.user_id, u.username, u.profile_picture, t.destination, t.start_date, t.end_date 
FROM users u
LEFT JOIN trips t ON u.user_id = t.user_id
";
$result = $conn->query($query);
$users = $result->fetch_all(MYSQLI_ASSOC);

// Fetching message requests with sender's username and profile picture
$query = "
    SELECT mr.*, u.username AS sender_username, u.profile_picture AS sender_profile_picture 
    FROM message_requests mr
    JOIN users u ON u.user_id = mr.sender_id
    WHERE mr.receiver_id = ? 
    ORDER BY mr.created_at DESC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);  // Assuming $user_id is the current logged-in user's ID
$stmt->execute();
$result = $stmt->get_result();

// Fetch all message requests
$message_requests = $result->fetch_all(MYSQLI_ASSOC);
// Fetch sent messages
// $sent_messages = $conn->query("SELECT mr.*, u.username, u.profile_picture FROM message_requests mr 
//                                JOIN users u ON u.user_id = mr.receiver_id 
//                                WHERE mr.sender_id = '$user_id'")->fetch_all(MYSQLI_ASSOC);
// Query to fetch sent messages along with recipient's username, profile picture, and trip information
$query = "
    SELECT mr.*, u.username, u.profile_picture, t.destination 
    FROM message_requests mr
    JOIN users u ON mr.receiver_id = u.user_id
    LEFT JOIN trips t ON u.user_id = t.user_id
    WHERE mr.sender_id = ?
";
if (isset($_POST['send_message'])) {
    $receiver_id = $_POST['receiver_id'];
    $message = $_POST['message'];

    $stmt = $conn->prepare("INSERT INTO message_requests (sender_id, receiver_id, message_content, status) VALUES (?, ?, ?, 'Pending')");
    $stmt->bind_param("iis", $user_id, $receiver_id, $message);
    $stmt->execute();

    // Success message
    $message_sent = true;
}

// Optionally display a success message in HTML
if (isset($message_sent)) {
    echo '<div class="alert alert-success">Message sent successfully!</div>';
}

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$sent_messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #1e3c72, #2a5298);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #fff;
            margin: 0;
        }
        .container {
            max-width: 1200px;
            background: #ffffff;
            border-radius: 15px;
            padding: 30px;
            display: grid;
            gap: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }
        .profile-header {
            text-align: center;
            padding-bottom: 20px;
        }
        .profile-header img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
        }
        .profile-header h2 {
            margin: 15px 0;
            font-size: 2rem;
            color: #2c3e50;
        }
        .profile-header p {
            color: #7f8c8d;
            font-size: 1.1rem;
        }
        .profile-form, .trip-form, .trip-list, .message-form, .message-requests {
            background: #f0f4f8;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        .container {
            padding: 30px;
            background: #fff;
            color: #333;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .success-message {
            background-color: #28a745;
            color: #fff;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        /* Styling for profile picture in message requests */
.message-item img {
    width: 40px; /* Adjust the size */
    height: 40px; /* Adjust the size */
    border-radius: 50%; /* Make the image round */
    border: 2px solid #ccc; /* Add a border around the profile picture */
    object-fit: cover; /* Ensure the image fits well */
    margin-right: 10px; /* Space between the image and text */
}

        .message-form {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .message-form select, .message-form textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .message-form button {
            background-color: #28a745;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .message-form button:hover {
            background-color: #218838;
        }
        #profile-preview {
            margin-top: 10px;
            display: none;
        }
        #profile-preview img {
            border-radius: 50%;
        }

        h3 {
    font-size: 1.8rem;
    color: #2c3e50;
    margin-bottom: 15px;
    font-weight: 600;
    /* border-bottom: 2px solid #3498db; */
    padding-bottom: 5px;
}
        button {
            background: linear-gradient(45deg, #3498db, #2ecc71);
            color: #fff;
            padding: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
            border: none;
            border-radius: 5px;
        }
        .trip-item, .message-item {
            background: #fff;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
            margin-bottom: 15px;
            color: #333;
            font-size: 1rem;
        }
        .trip-item p, .message-item p {
            margin: 10px 0;
        }
        .icon {
            cursor: pointer;
            font-size: 24px;
            margin: 10px;
        }
        input, textarea {
            margin-bottom: 10px;
            padding: 10px;
            width: 100%;
            border-radius: 5px;
            border: 1px solid #ddd;
            font-size: 1rem;
        }
        textarea {
            resize: vertical;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="profile-header">
        <img src="<?= htmlspecialchars($profile_picture) ?>" alt="Profile Picture">
        <h2><?= htmlspecialchars($username) ?></h2>
        <p><?= htmlspecialchars($email) ?></p>
    </div>

    <!-- Profile Form -->
    <div class="profile-form">
        <h3>Update Profile</h3>
        <form method="POST" enctype="multipart/form-data">
            <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
            <input type="file" name="profile_picture" accept="image/*">
            <button type="submit" name="update_profile">Update Profile</button>
        </form>
    </div>

    <!-- Add Trip Form -->
    <div class="trip-form">
        <h3>Add Trip</h3>
        <form method="POST">
            <input type="text" name="destination" placeholder="Destination" required>
            <input type="date" name="start_date" required>
            <input type="date" name="end_date" required>
            <button type="submit" name="add_trip">Add Trip</button>
        </form>
    </div>

    <!-- Trips -->
    <div class="trip-list">
        <h3>Your Trips</h3>
        <?php while ($trip = $trips_result->fetch_assoc()): ?>
            <div class="trip-item">
                <strong><?= htmlspecialchars($trip['destination']) ?></strong>
                <p>Start Date: <?= htmlspecialchars($trip['start_date']) ?></p>
                <p>End Date: <?= htmlspecialchars($trip['end_date']) ?></p>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="trip_id" value="<?= $trip['trip_id'] ?>">
                    <input type="text" name="destination" value="<?= htmlspecialchars($trip['destination']) ?>" required>
                    <input type="date" name="start_date" value="<?= htmlspecialchars($trip['start_date']) ?>" required>
                    <input type="date" name="end_date" value="<?= htmlspecialchars($trip['end_date']) ?>" required>
                    <button type="submit" name="edit_trip">Edit Trip</button>
                </form>
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="trip_id" value="<?= $trip['trip_id'] ?>">
                    <button type="submit" name="delete_trip">Delete</button>
                </form>
            </div>
        <?php endwhile; ?>
    </div>
<!-- // send msg form -->
<div class="container">
    <!-- Display success message -->
    <?php if (isset($_SESSION['message_sent'])): ?>
        <div class="success-message">
            <p><?= $_SESSION['message_sent'] ?></p>
        </div>
        <?php unset($_SESSION['message_sent']); ?>
    <?php endif; ?>

    <!-- Send Message Form -->
    <div class="message-form">
    <h3>Send Message</h3>
    <form method="POST">
        <label for="recipient">Recipient:</label>
        <select name="receiver_id" id="recipient" required>
            <?php foreach ($users as $user): ?>
                <option value="<?= htmlspecialchars($user['user_id']) ?>">
                    <?= htmlspecialchars($user['username']) ?> 
                    (<?= htmlspecialchars($user['destination']) ?: 'No Trip' ?>)
                </option>
            <?php endforeach; ?>
        </select>
        <textarea name="message" rows="4" placeholder="Type your message..." required></textarea>
        <button type="submit" name="send_message">Send Message</button>
    </form>
</div>

<div class="message-requests">
    <h3>Your Sent Messages</h3>
    <?php foreach ($sent_messages as $msg): ?>
        <div class="message-item">
            <strong>To: </strong> <?= htmlspecialchars($msg['username']) ?>
            <!-- Profile Image -->
            <img src="<?= htmlspecialchars($msg['profile_picture']) ?: 'default-profile.jpg' ?>" alt="Receiver Profile" class="profile-img">
            <p><strong>Message:</strong> <?= htmlspecialchars($msg['message_content']) ?></p>
            <p><strong>Trip: </strong> <?= htmlspecialchars($msg['destination']) ?: 'No Trip' ?></p>
            <p>Status: <?= htmlspecialchars($msg['status']) ?></p>
        </div>
    <?php endforeach; ?>
</div>

<script>
    // Show profile picture when a user is selected from the dropdown
    document.getElementById('receiver_id').addEventListener('change', function() {
        var selectedOption = this.options[this.selectedIndex];
        var profilePicture = selectedOption.getAttribute('data-img');
        var profilePreview = document.getElementById('profile-preview');
        var profileImg = document.getElementById('profile-img');
        
        if (profilePicture) {
            profilePreview.style.display = 'block';
            profileImg.src = profilePicture;
        } else {
            profilePreview.style.display = 'none';
        }
    });
</script>
  <!-- Sent Messages Display -->
<!--  -->
<!-- Message Requests -->
<div class="message-requests">
    <h3>Your Received Messages</h3>
    <?php foreach ($message_requests as $request): ?>
        <div class="message-item">
            <strong>From: </strong> <?= htmlspecialchars($request['sender_username']) ?>
            <!-- Profile Image with fallback for missing profile picture -->
            <img src="<?= htmlspecialchars($request['sender_profile_picture']) ?: 'default-profile.jpg' ?>" alt="Receiver Profile" class="profile-img">
            <p><?= htmlspecialchars($request['message_content']) ?></p>
        </div>
    <?php endforeach; ?>
</div>

<!-- Received Messages Display -->




    <!-- Message Requests -->
   

    <!-- Logout -->
    <button onclick="window.location.href='logout.php'">Logout</button>
</div>

</body>
</html>
