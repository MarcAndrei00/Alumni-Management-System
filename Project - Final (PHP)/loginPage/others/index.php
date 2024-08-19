<?php
// Database configuration
$servername = "localhost";
$db_username = "root";
$db_password = "";
$dbname = "your_database";

// Create connection
$conn = new mysqli($servername, $db_username, $db_password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start session
session_start();

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
    // User is logged in, display session information
    $user_id = $_SESSION['user_id'];
    $fullName = $_SESSION['name'];
    $username = $_SESSION['username'];
    $user_pass = $_SESSION['password'];
    $email = $_SESSION['email'];
    
    echo "User ID: $user_id<br>";
    echo "Name: $fullName<br>";
    echo "Username: $username<br>";
    echo "Password: $user_pass<br>";
    echo "Email: $email<br>";
    // Note: Avoid displaying password for security reasons

} else {
    // User is not logged in, redirect to login page
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
</head>
<body>
    <h1>Welcome to the Dashboard</h1>
    <p>This is a secure area. Only logged-in users can see this content.</p>
    <a href="logout.php">Logout</a>
</body>
</html>
