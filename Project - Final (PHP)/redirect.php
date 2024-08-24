<?php 
session_start();  // Start the session to access the URL

if (isset($_SESSION['checkout_url'])) {
    $checkoutUrl = $_SESSION['checkout_url'];

    // Clear the session variable to prevent repeated redirects
    unset($_SESSION['checkout_url']);

    // Redirect the user to the PayMongo link
    echo "<script>
            window.open('$checkoutUrl', '_blank');
            window.location.href = 'event.php';  // Redirect back to the main page
          </script>";
    exit();
} else {
    // If there's no URL, redirect to the main page
    header('Location: event.php');
    exit();
}
