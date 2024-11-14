<?php
session_start();
include 'config.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$trip_id = $_GET['trip_id'];
$user_id = $_SESSION['user_id'];

// Get the trip details
$stmt = $conn->prepare("SELECT destination, start_date, end_date FROM trips WHERE trip_id = ?");
$stmt->bind_param("i", $trip_id);
$stmt->execute();
$stmt->bind_result($destination, $start_date, $end_date);
$stmt->fetch();
$stmt->close();

// Fetch users attending the same trip on the same dates
$stmt = $conn->prepare("SELECT u.user_id, u.username, u.profile_picture 
                        FROM users u
                        JOIN trips t ON u.user_id = t.user_id
                        WHERE t.trip_id = ? AND t.start_date = ? AND t.end_date = ?");
$stmt->bind_param("iss", $trip_id, $start_date, $end_date);
$stmt->execute();
$users_result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users on the Same Trip</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Basic reset and general styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 80%;
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        .trip-details {
            text-align: center;
            margin-bottom: 30px;
        }

        .trip-details h3 {
            font-size: 1.5em;
            margin-bottom: 5px;
        }

        .trip-details p {
            font-size: 1.1em;
            color: #555;
        }

        /* Styling the user list */
        .user-list {
            list-style-type: none;
            padding: 0;
        }

        .user-list li {
            display: flex;
            align-items: center;
            background-color: #eaeaea;
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .user-list img {
            border-radius: 50%;
            width: 60px;
            height: 60px;
            margin-right: 20px;
        }

        .user-info {
            flex: 1;
        }

        .user-info h4 {
            margin: 0;
            font-size: 1.2em;
            color: #333;
        }

        .message-button {
            background-color: #007BFF;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .message-button:hover {
            background-color: #0056b3;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                width: 95%;
            }

            .user-list li {
                flex-direction: column;
                text-align: center;
            }

            .user-info {
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Users on the Same Trip</h2>

        <div class="trip-details">
            <h3>Trip to <?php echo htmlspecialchars($destination); ?></h3>
            <p>Start Date: <?php echo htmlspecialchars($start_date); ?> | End Date: <?php echo htmlspecialchars($end_date); ?></p>
        </div>

        <h3>Users Attending this Trip:</h3>
        <ul class="user-list">
            <?php while ($user = $users_result->fetch_assoc()): ?>
                <li>
                    <img src="<?php echo htmlspecialchars($user['profile_picture']) ?: 'default-profile.jpg'; ?>" alt="Profile Picture">
                    <div class="user-info">
                        <h4><?php echo htmlspecialchars($user['username']); ?></h4>
                        <a href="message_user.php?user_id=<?php echo $user['user_id']; ?>" class="message-button">Send Message</a>
                    </div>
                </li>
            <?php endwhile; ?>
        </ul>
    </div>

</body>
</html>

<?php
$stmt->close();
?>
