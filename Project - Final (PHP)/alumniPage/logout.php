<?php
// Initialize the session
session_start();

// Unset all session variables
$_SESSION = array();

// Destroy the session
session_destroy();

sleep(2);
// Redirect to login page or any other page after logout
header("Location: ../homepage.php");
exit;
?>