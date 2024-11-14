<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Include the database connection
include 'config.php';

// Get the user_id from the session
$user_id = $_SESSION['user_id'];

// Initialize variables
$username = 'Unknown User';
$email = 'Not available';
$profile_picture = 'default-profile.jpg';

// Fetch user data from the database
if ($stmt = $conn->prepare("SELECT username, email, profile_picture FROM users WHERE user_id = ?")) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($username, $email, $profile_picture);
    $stmt->fetch();
    $stmt->close();
}

// Handle form submission for editing profile
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_username = $_POST['username'];
    $new_email = $_POST['email'];

    // Handle profile picture upload
    if ($_FILES['profile_picture']['error'] == 0) {
        $profile_picture_path = 'uploads/' . basename($_FILES['profile_picture']['name']);
        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $profile_picture_path)) {
            $profile_picture = $profile_picture_path;
        }
    }

    // Update user data in the database
    if ($update_stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, profile_picture = ? WHERE user_id = ?")) {
        $update_stmt->bind_param("sssi", $new_username, $new_email, $profile_picture, $user_id);
        $update_stmt->execute();
        $update_stmt->close();
        header("Location: profile.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(45deg, #6a11cb, #2575fc);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            width: 80%;
            max-width: 800px;
            background-color: #ffffff;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .container:hover {
            transform: scale(1.02);
            box-shadow: 0px 10px 40px rgba(0, 0, 0, 0.15);
        }
        .form-container {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .form-container h2 {
            margin-bottom: 20px;
            font-size: 28px;
            color: #333;
        }
        .form-container input,
        .form-container textarea,
        .form-container select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 15px;
            font-size: 14px;
        }
        .form-container button {
            background: #4a90e2;
            color: #fff;
            padding: 12px 24px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s ease;
        }
        .form-container button:hover {
            background: #357ab7;
        }
        .form-container img {
            border-radius: 50%;
            width: 120px;
            height: 120px;
            object-fit: cover;
            margin-bottom: 20px;
        }
        .form-container label {
            font-size: 16px;
            color: #333;
        }
        .form-container .upload-btn {
            background: #ff5c5c;
            color: #fff;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
        }
        .form-container .upload-btn:hover {
            background: #e04b4b;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="form-container">
        <h2>Edit Profile</h2>
        <form action="edit_profile.php" method="POST" enctype="multipart/form-data">
            <!-- Profile Picture -->
            <label for="profile_picture">Profile Picture</label>
            <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture">
            <input type="file" name="profile_picture" id="profile_picture" class="upload-btn">
            
            <!-- Username -->
            <label for="username">Username</label>
            <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($username); ?>" required>

            <!-- Email -->
            <label for="email">Email</label>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($email); ?>" required>
            
            <!-- Submit Button -->
            <button type="submit">Save Changes</button>
        </form>
    </div>
</div>

</body>
</html>
