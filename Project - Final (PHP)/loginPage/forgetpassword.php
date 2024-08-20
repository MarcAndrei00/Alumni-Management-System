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
  $row = $user_result->fetch_assoc();

  if ($user_result->num_rows > 0) {

    $sql = "SELECT * FROM alumni WHERE alumni_id=$account";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();

    if ($row['status'] == "Verified") {
      // User is a verified alumni
      header('Location: ../alumniPage/dashboard_user.php');
      exit();
    } else {
      $stmt->close();
      $_SESSION['email'] = $account_email;
      $_SESSION['alert'] = 'Unverified';
      sleep(2);
      header('Location: ./verification_code.php');
      exit();
    }
  } else {
    session_destroy();
    header('Location: ./login.php');
    exit();
  }
}
// NO ACCOUNT IN SESSION
else {
  // Handle form submission
  if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email']) && isset($_POST['submit'])) {
    $email = $_POST['email'];
    $verification_code = sprintf("%06d", mt_rand(1, 999999));
    $email = mysqli_real_escape_string($conn, $email);
    $verification_code = mysqli_real_escape_string($conn, $verification_code);

    $check_email_status_qry = mysqli_query($conn, "SELECT status FROM alumni WHERE email = '$email'");

    if (mysqli_num_rows($check_email_status_qry) > 0) {
      // Email exists and is verified
      $insert_verifcodes_qry = mysqli_query($conn, "INSERT INTO recovery_code(email,verification_code)
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
      $_SESSION['email'] = $email;

      // WARNING NOT VERIFIED
      $icon = 'success';
      $iconHtml = '<i class="fas fa-check-circle"></i>';
      $title = 'Verification code successfully send';
      $text = 'You will be redirected shortly to verify the email.';
      $redirectUrl = './verification_code.php';

      echo "<script>
              document.addEventListener('DOMContentLoaded', function() {
                  alertMessage('$redirectUrl', '$title', '$text', '$icon', '$iconHtml');
              });
          </script>";
    } else {

      // ERROR NOT EXIST
      $icon = 'error';
      $iconHtml = '<i class=\"fas fa-exclamation-circle\"></i>';
      $title = 'The email you input does not exist!';
      $text = 'Please create an account first or try again.';

      echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                warningError('$title', '$text', '$icon', '$iconHtml');
            });
        </script>";
      sleep(2);
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="shortcut icon" href="../assets/cvsu.png" type="image/svg+xml">
  <title>Forgot Password</title>
  <link rel="shortcut icon" href="../assets/cvsu.png" type="image/svg+xml">
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">


  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

    * {
      margin: 0;
      padding: 0;
      text-decoration: none;
      list-style-type: none;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
    }
        
    body,
    html {
      height: 100%;
      margin: 0;
    }


    .background {
      background-image: url("../assets/logins.jpg");
      /* Update the path accordingly if necessary */
      background-position: center;
      background-size: cover;
      opacity: blur(100px);
      /* Adjust the blur level as needed */
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .card {
      max-width: 600px;
      border-radius: 10px;
      border: none;
      box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
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
      <div>
        <div class="card-body">
          <div class="text-center mb-4">
            <img src="cvsu.png" alt="Warning Icon" class="icon-size">
          </div>
          <h5 class="text-center mb-4">Forgot Password</h5>
          <p class="text-center mb-4">Enter your email and we'll send you a link to reset your password.</p>
          <form method="POST">
            <div class="form-group">
              <input type="email" name="email" class="form-control" id="email" placeholder="Enter your email" required>
            </div>
            <button type="submit" name="submit" class="btn btn-primary btn-block">Submit</button>
          </form>
          <a href="login.php" class="back-to-login">Back to Login</a>
        </div>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <script>
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
  </script>
</body>

</html>