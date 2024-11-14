<?php
session_start();  // Start session to destroy it

// Destroy the session and log out
session_unset();
session_destroy();

// Redirect to the login page
header("Location: login.html");
exit();
?>
