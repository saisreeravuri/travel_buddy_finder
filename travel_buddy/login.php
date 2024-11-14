<?php
session_start();  // Start the session to store user data

// Include database connection
include 'config.php';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare SQL statement to fetch user info based on email
    $stmt = $conn->prepare("SELECT user_id, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($user_id, $hashed_password);

    // Check if user exists and password is correct
    if ($stmt->num_rows > 0 && $stmt->fetch() && password_verify($password, $hashed_password)) {
        // Store user id in session variable
        $_SESSION['user_id'] = $user_id;

        // Redirect user to their profile page
        header("Location: profile.php");
        exit();  // Don't forget to call exit() after redirect
    } else {
        // If credentials are invalid
        echo "Invalid email or password.";
    }

    // Close the prepared statement
    $stmt->close();
}
?>
