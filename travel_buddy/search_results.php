<?php
include 'config.php';

$destination = $_GET['destination'] ?? '';
$start_date = $_GET['start_date'] ?? '';

$query = "SELECT users.username, trips.destination, trips.start_date, trips.end_date 
          FROM trips 
          JOIN users ON trips.user_id = users.user_id 
          WHERE destination LIKE ?";

$params = ["%$destination%"];
$types = "s";

if ($start_date) {
    $query .= " AND start_date >= ?";
    $params[] = $start_date;
    $types .= "s";
}

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$trips = [];
while ($row = $result->fetch_assoc()) {
    $trips[] = $row;
}

echo json_encode($trips);
$stmt->close();
?>
