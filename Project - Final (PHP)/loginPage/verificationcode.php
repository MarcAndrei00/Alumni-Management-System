<?php
session_start();

$servername = "localhost";
$db_username = "root";
$db_password = "";
$db_name = "alumni_management_system";
$conn = new mysqli($servername, $db_username, $db_password, $db_name);

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

if (isset($_SESSION['user_id']) && isset($_SESSION['user_email'])) {
  $account = $_SESSION['user_id'];
  $account_email = $_SESSION['user_email'];

  // Check if user is an alumni
  $stmt = $conn->prepare("SELECT * FROM alumni WHERE alumni_id = ? AND email = ?");
  $stmt->bind_param("ss", $account, $account_email);
  $stmt->execute();
  $user_result = $stmt->get_result();
  $row = $user_result->fetch_assoc();

  if ($user_result->num_rows > 0) {
    // Fetch the recovery code
    $stmt2 = $conn->prepare("SELECT code FROM recovery_code WHERE email = ? ORDER BY creation_date DESC LIMIT 1");
    $stmt2->bind_param("s", $account_email);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    $row2 = $result2->fetch_assoc();
    $code = $row2['code'];

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
      $inputCode = $_POST['inputCode'];

      if ($code == $inputCode) {
        $stmt = $conn->prepare("UPDATE alumni SET status = 'Verified' WHERE alumni_id = ?");
        $stmt->bind_param("s", $account);
        $stmt->execute();

        echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            title: 'Verification successful',
                            timer: 2000,
                            showConfirmButton: true,
                            confirmButtonColor: '#4CAF50',
                            confirmButtonText: 'OK'
                        }).then(function() {
                            window.location.href = '../alumniPage/dashboard_user.php';
                        });
                    });
                </script>";
      } else {
        echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            title: 'Invalid Code',
                            timer: 2000,
                            showConfirmButton: true,
                            confirmButtonColor: '#4CAF50',
                            confirmButtonText: 'OK'
                        });
                    });
                </script>";
      }
    } else {

      // Check if the user is already verified
      if ($row['status'] == "Verified") {
        header('Location: ../alumniPage/dashboard_user.php');
        exit();
      }
    }
  } else {
    header('Location: ./login.php');
    exit();
  }
} else {
  header('Location: ./login.php');
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verification</title>
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
  <link rel="shortcut icon" href="cvsu.png" type="image/svg+xml">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <style>
    body,
    html {
      height: 100%;
      margin: 0;
    }

    .background {
      background-image: url('bg2.png');
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
          <img src="cvsu.png" alt="Logo" class="icon-size">
        </div>
        <h5 class="text-center mb-4">Verification Code</h5>
        <form action="" method="POST">
          <div class="form-group">
            <input type="text" class="form-control" id="code" name="inputCode" placeholder="Enter a Code" required>
          </div>
          <a href="#" class="back-to-login">Resend Code</a>
          <br>
          <button type="submit" name="submit" class="btn btn-primary btn-block" style="width: 48%; float: left;">Submit</button>
          <a href='./logout.php'>
            <button type="button" class="btn btn-secondary btn-block" style="width: 48%; float: right; margin: 0px;">Back</button>
          </a>
        </form>
      </div>
    </div>
  </div>
  <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>