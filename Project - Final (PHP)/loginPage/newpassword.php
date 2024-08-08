<?php
session_start();
$conn = new mysqli("localhost", "root", "", "alumni_management_system");

use PHPMailer\PHPMailer\PHPMailer;

require '../vendor/autoload.php';

if (isset($_SESSION['user_id']) && isset($_SESSION['user_email'])) {
    $account = $_SESSION['user_id'];
    $account_email = $_SESSION['user_email'];

    // Check if user is an admin
    $stmt = $conn->prepare("SELECT * FROM admin WHERE admin_id = ? AND email = ?");
    $stmt->bind_param("ss", $account, $account_email);
    $stmt->execute();
    $user_result = $stmt->get_result();

    if ($user_result->num_rows > 0) {
        // User is an admin
        header('Location: ../adminPage/dashboard_admin.php');
        exit();
    }
    $stmt->close();

    // Check if user is a coordinator
    $stmt = $conn->prepare("SELECT * FROM coordinator WHERE coor_id = ? AND email = ?");
    $stmt->bind_param("ss", $account, $account_email);
    $stmt->execute();
    $user_result = $stmt->get_result();

    if ($user_result->num_rows > 0) {
        // User is a coordinator
        header('Location: ../coordinatorPage/dashboard_coor.php');
        exit();
    }
    $stmt->close();

    // Check if user is an alumni
    $stmt = $conn->prepare("SELECT * FROM alumni WHERE alumni_id = ? AND email = ?");
    $stmt->bind_param("ss", $account, $account_email);
    $stmt->execute();
    $user_result = $stmt->get_result();

    if ($user_result->num_rows > 0) {
        $sql = "SELECT * FROM alumni WHERE alumni_id=$account";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();

        if ($row['status'] == "Verified") {
            // User is a verified alumni
            header('Location: ../alumniPage/dashboard_user.php');
            exit();
        } else {
            $_SESSION['email'] = $account_email;
            echo "<script>
                            // Wait for the document to load
                            document.addEventListener('DOMContentLoaded', function() {
                                // Use SweetAlert2 for the alert
                                Swal.fire({
                                        title: 'Verify Your Account First',
                                        timer: 5000,
                                        showConfirmButton: true, // Show the confirm button
                                        confirmButtonColor: '#4CAF50', // Set the button color to green
                                        confirmButtonText: 'OK' // Change the button text if needed
                                }).then(function() {
                                    // Redirect after the alert closes
                                    window.location.href = './verification_code.php';
                                });
                            });
                        </script>";
        }
    }
    $stmt->close();
} else {
    $email = isset($_SESSION['email']) ? $_SESSION['email'] : '';
    if (empty($email)) {
        header('Location: login.php');
        exit();
    }

    // Handle form submission
    if (isset($_POST['submit'])) {
        $new_pass = $_POST['new_password'];
        $confirm_pass = $_POST['confirm_password'];

        if ($new_pass === $confirm_pass) {
            // Update password
            $match_pass_qry = mysqli_query($conn, "UPDATE alumni SET password = '$confirm_pass' WHERE email = '$email'");
            $_SESSION['alert'] = [
                'type' => 'success',
                'title' => 'Password has been changed!',
                'text' => 'Now you can login with your new password.'
            ];
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $_SESSION['alert'] = [
                'type' => 'error',
                'title' => 'Password does not match!'
            ];
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
        }
    }

    // Check and display alert if set
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: '" . $alert['type'] . "',
                title: '" . addslashes($alert['title']) . "',
                text: '" . (isset($alert['text']) ? addslashes($alert['text']) : '') . "',
                customClass: {
                    popup: 'swal-custom'
                }
            }).then((result) => {
                if (result.isConfirmed && '" . $alert['type'] . "' === 'success') {
                    window.location.href = 'login.php';
                }
            });
        });
      </script>";
        unset($_SESSION['alert']); // Unset the alert after showing it
    }
}
// Redirect if email is not set
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Password</title>
    <link rel="shortcut icon" href="../assets/cvsu.png" type="image/svg+xml">
    <link rel="shortcut icon" href="../assets/cvsu.png" type="image/svg+xml">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body,
        html {
            height: 100%;
            margin: 0;
        }

        .background {
            background-image: url('bg2.png');
            /* Update the path accordingly if necessary */
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .card {
            width: 600px;
            border-radius: 10px;
            border: none;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }

        .card-body {
            padding: 100px;
            height: 517px;
        }

        .form-control {
            border-radius: 5px;
            height: 45px;
        }

        .btn {
            border-radius: 5px;
            height: 40px;
            font-size: 16px;
        }

        .back-to-login {
            display: block;
            text-align: center;
            margin-top: 10px;
        }

        .icon-size {
            width: 48px;
            height: 48px;
        }
    </style>
</head>

<body>
    <div class="background">
        <div class="card">
            <div class="card-body">
                <div class="text-center mb-4">
                    <img src="cvsu.png" alt="Warning Icon" class="icon-size">
                </div>
                <h5 class="text-center mb-4">New Password</h5>
                <form method="POST">
                    <div class="form-group" style="position: relative;">
                        <input type="password" name="new_password" class="form-control" id="password" placeholder="Create new password" required>
                        <img id="togglePassword" src="eye-close.png" alt="Show/Hide Password" onclick="togglePasswordVisibility('password', 'togglePassword')" style="height: 15px; width: 20px; position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;" />
                    </div>
                    <div class="form-group" style="position: relative;">
                        <input type="password" name="confirm_password" class="form-control" id="confirm_password" placeholder="Confirm your password" required>
                        <img id="toggleConfirmPassword" src="eye-close.png" alt="Show/Hide Password" onclick="togglePasswordVisibility('confirm_password', 'toggleConfirmPassword')" style="height: 15px; width: 20px; position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;" />
                    </div>
                    <button type="submit" name="submit" class="btn btn-primary btn-block">Change</button>
                </form>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function togglePasswordVisibility(passwordId, toggleId) {
            var passwordField = document.getElementById(passwordId);
            var toggleIcon = document.getElementById(toggleId);

            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleIcon.src = 'eye-open.png'; // Use the image for showing password
            } else {
                passwordField.type = 'password';
                toggleIcon.src = 'eye-close.png'; // Use the image for hiding password
            }
        }
    </script>

</body>

</html>
`