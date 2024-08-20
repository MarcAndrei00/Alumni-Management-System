<?php
session_start();
$conn = new mysqli("localhost", "root", "", "alumni_management_system");

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
            $_SESSION['alert'] = 'Unverified';
            sleep(2);
            header('Location: ./verification_code.php');
            exit();
        }
    }
    $stmt->close();
} else {
    $email = isset($_SESSION['email']) ? $_SESSION['email'] : '';
    if (empty($email)) {
        session_destroy();
        header('Location: login.php');
        exit();
    }

    // Handle form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
        $new_pass = $_POST['password'];
        $confirm_pass = $_POST['confirm_password'];

        $hashedpassword = password_hash($confirm_pass, PASSWORD_BCRYPT);

        $archive_qry = mysqli_query($conn,"SELECT * FROM alumni_archive WHERE email = '$email'");
        $archive_row = mysqli_fetch_assoc($archive_qry);

        if($new_pass === $confirm_pass){
            if($archive_row['status'] == "Inactive"){
                $match_pass_qry = mysqli_query($conn, "UPDATE alumni_archive SET password = '$hashedpassword' WHERE email = '$email'");
    
                // SUCCESS CHANGE PASS
                $icon = 'success';
                $iconHtml = '<i class="fas fa-check-circle"></i>';
                $title = 'Your Account is still Inactive!';
                $text = 'Now you can login with your new password and Activate your account.';
                $redirectUrl = './login.php';
    
                echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            alertMessage('$redirectUrl', '$title', '$text', '$icon', '$iconHtml');
                        });
                    </script>";
                sleep(2);
            }
            else{
                $match_pass_qry = mysqli_query($conn, "UPDATE alumni SET password = '$hashedpassword' WHERE email = '$email'");
                $match_pass_qry = mysqli_query($conn, "UPDATE admin SET password = '$hashedpassword' WHERE email = '$email'");
                $match_pass_qry = mysqli_query($conn, "UPDATE coordinator SET password = '$hashedpassword' WHERE email = '$email'");
    
                // SUCCESS CHANGE PASS
                $icon = 'success';
                $iconHtml = '<i class="fas fa-check-circle"></i>';
                $title = 'Password has been changed!';
                $text = 'Now you can login with your new password.';
                $redirectUrl = './login.php';
    
                echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            alertMessage('$redirectUrl', '$title', '$text', '$icon', '$iconHtml');
                        });
                    </script>";
                sleep(2);
            }
        } 
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

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
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
            border: 1px solid #f5c6cb;
            text-align: center;
            font-family: Arial, sans-serif;
        }

        .error-message {
            margin: 0;
            font-size: 14px;
        }

        #real-time-errors {
            display: none; /* Hidden by default */
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
                <div class="alert alert-danger text-center error-list" id="real-time-errors"></div>
                <h5 class="text-center mb-4">New Password</h5>
                <form method="POST">
                    <div class="form-group" style="position: relative;">
                        <input type="password" name="password" class="form-control" id="password" onkeyup="validatePassword()" placeholder="Create new password" required>
                        <img id="togglePassword" src="eye-close.png" alt="Show/Hide Password" onclick="togglePasswordVisibility('password', 'togglePassword')" style="height: 15px; width: 20px; position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;" />
                    </div>
                    <div class="form-group" style="position: relative;">
                        <input type="password" name="confirm_password" class="form-control" id="confirm_password" onkeyup="validatePassword()" placeholder="Confirm your password" required>
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

        // FOR MESSAGEBOX
        function alertMessage(redirectUrl, title, text, icon, iconHtml) {
            Swal.fire({
                icon: icon,
                iconHtml: iconHtml, // Custom icon using Font Awesome
                title: title,
                text: text,
                customClass: {
                    popup: 'swal-custom'
                },
                showConfirmButton: true,
                confirmButtonColor: '#4CAF50',
                confirmButtonText: 'OK',
                timer: 5000
            }).then(() => {
                window.location.href = redirectUrl; // Redirect to the desired page
            });
        }

        // WARNING FOR DUPE ACCOUNT
        function warningError(title, text, icon, iconHtml) {
            Swal.fire({
                icon: icon,
                iconHtml: iconHtml, // Custom icon using Font Awesome
                title: title,
                text: text,
                customClass: {
                    popup: 'swal-custom'
                },
                showConfirmButton: true,
                confirmButtonColor: '#4CAF50',
                confirmButtonText: 'OK',
                timer: 5000,
            });
        }
        function validatePassword() {
            var password = document.getElementById("password").value;
            var confirmPassword = document.getElementById("confirm_password").value;
            var errorMessages = [];
            var errorContainer = document.getElementById("real-time-errors");

            // Clear previous error messages
            errorContainer.innerHTML = "";

            // Validation rules

            if (password === '') {
                errorContainer.style.display = 'none';
                return;
            } else {
                if (password.length < 8) {
                    errorMessages.push("Password must be at least 8 characters long.");
                }
                else if (!/[A-Z]/.test(password)) {
                    errorMessages.push("Password must contain at least one uppercase letter.");
                }
                else if (!/[a-z]/.test(password)) {
                    errorMessages.push("Password must contain at least one lowercase letter.");
                }
                else if (!/\d/.test(password)) {
                    errorMessages.push("Password must contain at least one digit.");
                }
                else if (!/[^a-zA-Z\d]/.test(password)) {
                    errorMessages.push("Password must contain at least one special character.");
                }
                else if (confirmPassword && password !== confirmPassword) {
                    errorMessages.push("Passwords do not match.");
                }
            }

            // Display error messages
            if (errorMessages.length > 0) {
                errorMessages.forEach(function(error) {
                    var p = document.createElement("p");
                    p.innerText = error;
                    p.className = "error-message";
                    errorContainer.appendChild(p);
                });

                // Ensure the error container is visible
                errorContainer.style.display = 'block';
            } else {
                // Hide the error container if there are no errors
                errorContainer.style.display = 'none';
            }
        }
    </script>

</body>

</html>
`