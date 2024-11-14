<?php
session_start();
include 'config.php';

if (isset($_POST['search'])) {
    $destination = $_POST['destination'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $interests = $_POST['interests'];

    $sql = "SELECT trips.trip_id, users.username, trips.destination, trips.start_date, trips.end_date, trips.interests
            FROM trips 
            JOIN users ON trips.user_id = users.user_id 
            WHERE trips.destination LIKE ? 
            AND trips.start_date >= ? 
            AND trips.end_date <= ? 
            AND trips.interests LIKE ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $destination, $start_date, $end_date, $interests);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>

<!-- search_trips.html -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Travel Buddies</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <form action="search_trips.php" method="POST">
        <h2>Search for Travel Buddies</h2>
        <input type="text" name="destination" placeholder="Destination" required>
        <input type="date" name="start_date" required>
        <input type="date" name="end_date" required>
        <input type="text" name="interests" placeholder="Interests (e.g., sightseeing, hiking)">
        <button type="submit" name="search">Search</button>
    </form>

    <div>
        <h3>Search Results:</h3>
        <?php
        if (isset($result) && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<div class='trip'>";
                echo "<h4>" . htmlspecialchars($row['username']) . "'s trip to " . htmlspecialchars($row['destination']) . "</h4>";
                echo "<p>Dates: " . htmlspecialchars($row['start_date']) . " to " . htmlspecialchars($row['end_date']) . "</p>";
                echo "<p>Interests: " . htmlspecialchars($row['interests']) . "</p>";
                echo "</div>";
            }
        } else {
            echo "<p>No trips found matching your criteria.</p>";
        }
        ?>
    </div>
</body>
</html>
