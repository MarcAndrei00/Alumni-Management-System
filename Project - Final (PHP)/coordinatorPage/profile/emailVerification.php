<?php
session_start();
$conn = new mysqli("localhost", "root", "", "alumni_management_system");

// IMPORTANT CODE ---------------
use PHPMailer\PHPMailer\PHPMailer;

require '../../vendor/autoload.php';

// SESSION
$inputEmail = $_SESSION['inputEmail'];
if ($inputEmail == 0) {
  header('Location: ./login.php');
  exit();
}

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
    header('Location: ../../adminPage/dashboard_admin.php');
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
    $user = $user_result->fetch_assoc();
  }
  $stmt->close();

  // Check if user is an alumni
  $stmt = $conn->prepare("SELECT * FROM alumni WHERE alumni_id = ? AND email = ?");
  $stmt->bind_param("ss", $account, $account_email);
  $stmt->execute();
  $user_result = $stmt->get_result();

  // TO VERIFIED ACCOUNT 
  if ($user_result->num_rows > 0) {
    $sql = "SELECT * FROM alumni WHERE alumni_id=$account";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();

    if ($row['status'] == "Verified") {
      // User is a verified alumni
      header('Location: ../../alumniPage/dashboard_user.php');
      exit();
    } else {
      $_SESSION['email'] = $account_email;
      $_SESSION['alert'] = 'Unverified';
      sleep(2);
      header('Location: ../../loginPage/verification_code.php');
      exit();
    }
  }
  $stmt->close();

  // Check if user is an alumni_archive
  $stmt = $conn->prepare("SELECT * FROM coordinator WHERE coor_id = ? AND email = ?");
  $stmt->bind_param("ss", $account, $account_email);
  $stmt->execute();
  $user_result = $stmt->get_result();

  if ($user_result->num_rows > 0) {
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['verif_code']) && isset($_POST['submit_code'])) {
      $verif_code = $_POST['verif_code'];

      $check_verifcode_qry = mysqli_query($conn, "SELECT verification_code FROM recovery_code WHERE verification_code = '$verif_code'");

      if (mysqli_num_rows($check_verifcode_qry) > 0) {

        $sql_archive = "UPDATE admin SET email = '$inputEmail' WHERE coor_id=$account";
        $conn->query($sql_archive);

        $_SESSION['user_email'] = $inputEmail;
        $delete_qry = mysqli_query($conn, "DELETE FROM recovery_code WHERE email='$inputEmail'");

        // SUCCESS VERIF 
        $icon = 'success';
        $iconHtml = '<i class="fas fa-check-circle"></i>';
        $title = 'Verification code match!';
        $text = 'You will be redirected shortly to Dashboard';
        $redirectUrl = './profile.php';

        echo "<script>
              document.addEventListener('DOMContentLoaded', function() {
                alertMessage('$redirectUrl', '$title', '$text', '$icon', '$iconHtml');
              });
            </script>";
        sleep(2);
      } else {

        // ERROR VERIF NOT MATCH
        $icon = 'error';
        $iconHtml = '<i class=\"fas fa-exclamation-circle\"></i>';
        $title = 'Verification code does not match!';
        $text = 'Please try again.';

        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
              warningError('$title', '$text', '$icon', '$iconHtml');
            });
          </script>";
        sleep(2);
      }
    } // BACK BUTTON
    else if (isset($_POST['back_btn'])) {
      $sql_delete = "DELETE FROM recovery_code WHERE email='$inputEmail'";
      $conn->query($sql_delete);
      header('Location: ./profile.php');
      exit();
    }
    // RESEND CODE
    else if (isset($_POST['resendCode'])) {
      // DELETE OLD VERIF CODE
      $sql_delete = "DELETE FROM recovery_code WHERE email=?";
      $stmt = $conn->prepare($sql_delete);
      $stmt->bind_param("s", $inputEmail);
      $stmt->execute();
      $stmt->close();

      // Generate new verification code
      $verification_code = sprintf("%06d", mt_rand(1, 999999));

      // Insert new verification code into the database
      $stmt = $conn->prepare("INSERT INTO recovery_code (email, verification_code) VALUES (?, ?)");
      $stmt->bind_param("ss", $inputEmail, $verification_code);
      $stmt->execute();
      $stmt->close();

      // Send verification code via email
      $mail = new PHPMailer(true);

      $mail->isSMTP();
      $mail->Host       = 'smtp.gmail.com';
      $mail->SMTPAuth   = true;
      $mail->Username   = 'alumni.management07@gmail.com';
      $mail->Password   = 'kcio bmde ffvc sfar';  // Ensure this is securely stored and not hardcoded
      $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
      $mail->Port       = 587;

      $mail->setFrom('alumni.management07@gmail.com', 'Alumni Management');
      $mail->addAddress($inputEmail);

      $mail->isHTML(true);
      $mail->Subject = 'Verification Code';
      $mail->Body    = 'Your verification code is <b>' . $verification_code . '</b>';
      $mail->AltBody = 'Your verification code is ' . $verification_code;

      $mail->send();

      // SUCCESS RESEND
      $icon = 'success';
      $iconHtml = '<i class="fas fa-check-circle"></i>';
      $title = 'Verification code successfully send.';
      $text = 'Please check your mail.';

      echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                warningError('$title', '$text', '$icon', '$iconHtml');
            });
        </script>";
    }
  } else {
    // Redirect to login if no matching user found
    session_destroy();
    header('Location: ../../homepage.php');
    exit();
  }
} else {
  // Redirect to login if no matching user found
  session_destroy();
  header('Location: ../../homepage.php');
  exit();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verification</title>
  <link rel="shortcut icon" href="../../assets/cvsu.png" type="image/svg+xml">
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
      background-image: url("../../assets/logins.jpg");
      /* Update the path accordingly if necessary */
      background-position: center;
      background-repeat: no-repeat;
      background-size: cover;
      background-color: #f8f9fa;
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
      display: inline;
      text-align: center;
      margin-top: 10px;
      float: none;
      background: none;
      border: none;
      padding: 0;
      color: #007bff;
      margin: 0px;
      cursor: pointer;
      text-decoration: none;
    }

    .back-to-login:hover {
      color: #0056b3;
      text-decoration: underline;
    }

    .back-to-login:focus {
      outline: none;
      /* Remove default focus outline (borders) */
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
          <img src="../../loginPage/cvsu.png" alt="Logo" class="icon-size">
        </div>
        <h5 class="text-center mb-4">We've send the verification code to your email &nbsp;"<?php echo $_SESSION['inputEmail'] ?>"</h5>
        <form method="POST">
          <div class="form-group">
            <input type="number" name="verif_code" class="form-control" id="code" placeholder="Input the Verification Code">
          </div>
          <div style="text-align: center;">
            <button type="submit" name="resendCode" class="back-to-login">Resend Code</button>
          </div>
          <br>
          <button type="submit" name="submit_code" class="btn btn-primary btn-block" style="width: 48%; float: left;">Submit</button>
          <button type="submit" name="back_btn" class="btn btn-primary btn-block" style="width: 48%; float: right; margin: 0px;">Back</button>
        </form>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <script>
    // FOR NO NIGGATIVE NUMBERS
    document.addEventListener("DOMContentLoaded", function() {
      const studentIdInput = document.getElementById("code");

      studentIdInput.addEventListener("input", function(event) {
        let value = studentIdInput.value;
        // Replace all non-numeric characters
        value = value.replace(/[^0-9]/g, '');
        studentIdInput.value = value;
      });
    });

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