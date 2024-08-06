<?php 
session_start();
$connect = new mysqli("localhost","root","","alumni_management_system");

use PHPMailer\PHPMailer\PHPMailer;

require 'vendor/autoload.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email']) && isset($_POST['submit'])) {
    $email = $_POST['email'];
    $verification_code = sprintf("%06d", mt_rand(1, 999999));
    $email = mysqli_real_escape_string($connect, $email);
    $verification_code = mysqli_real_escape_string($connect, $verification_code);
    $status = "unverified";
    $_SESSION['verification_code'] = $verification_code;

    $check_email_status_qry = mysqli_query($connect,"SELECT email,status FROM alumni WHERE email = '$email' AND status = 'verified'");
    $check_status_qry = mysqli_query($connect,"SELECT email,status FROM alumni WHERE email = '$email' AND status = '$status'");
    
    if(mysqli_num_rows($check_status_qry) > 0){
      echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Account is not Verified!',
                        text: 'Please verify your account first',
                        customClass: {
                            popup: 'swal-custom'
                        }
                      }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'verification_code.php';
                        }
                    });
                });
            </script>";
            $_SESSION['email'] = $email;
    }
    else if(mysqli_num_rows($check_email_status_qry) > 0){
      $insert_verifcodes_qry = mysqli_query($connect,"INSERT INTO recovery_code(email,verification_code)
        VALUES('$email','$verification_code')");
            $mail = new PHPMailer(true);

            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'alumni.management07@gmail.com';
            $mail->Password   = 'kcio bmde ffvc sfar'; 
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587; 

            $mail->setFrom('alumni.management07@gmail.com', 'Alumni Management');
            $mail->addAddress($email);     

            $mail->isHTML(true);
            $mail->Subject = 'Verification Code';
            $mail->Body    = 'Your verification code is <b>' . $verification_code . '</b>';
            $mail->AltBody = 'Your verification code is ' . $verification_code;

            $mail->send();
            header('location: verification_code.php');
            $_SESSION['email'] = $email;
            exit();
    }
    else{
      echo "<script>
              document.addEventListener('DOMContentLoaded', function() {
                  Swal.fire({
                      icon: 'error',
                      title: 'Inputted Email Doesn't Exist!',
                      text: 'Please create an account first',
                      customClass: {
                          popup: 'swal-custom'
                      }
                  });
              });
            </script>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    body, html {
      height: 100%;
      margin: 0;
    }
    .background {
      background-image: url('bg2.png'); /* Update the path accordingly if necessary */
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
      max-width: 600px;
      border-radius: 10px;
      border: none;
      box-shadow: 0 0 20px rgba(0,0,0,0.1);
    }
    .card-body {
      padding: 100px;
    }
    .form-control {
      border-radius: 5px;
    }
    .btn {
      border-radius: 5px;
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
    .footer {
      margin-top: 20px;
      text-align: center;
      color: #777;
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
      <h5 class="text-center mb-4">Forgot Password</h5>
      <p class="text-center mb-4">Enter your email and we'll send you a link to reset your password.</p>
      <form method="POST">
        <div class="form-group">
          <input type="email" name="email" class="form-control" id="email" placeholder="Enter your email">
        </div>
        <button type="submit" name="submit" class="btn btn-primary btn-block">Submit</button>
      </form>
      <a href="login.php" class="back-to-login">Back to Login</a>
    </div>
  </div>
</div>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
