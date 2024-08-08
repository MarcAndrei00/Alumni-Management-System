<?php
session_start();
$conn = new mysqli("localhost", "root", "", "alumni_management_system");

$email = $_SESSION['email'];
if ($email == 0) {
  header('Location: login.php');
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

  // TO VERIFIED ACCOUNT 
  if ($user_result->num_rows > 0) {
    $sql = "SELECT * FROM alumni WHERE alumni_id=$account";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();

    if ($row['status'] == "Verified") {
      // User is a verified alumni
      header('Location: ../alumniPage/dashboard_user.php');
      exit();
    } else {
      if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['verif_code']) && isset($_POST['submit_code'])) {
        $verif_code = $_POST['verif_code'];

        $check_verifcode_qry = mysqli_query($conn, "SELECT verification_code FROM recovery_code WHERE verification_code = '$verif_code'");

        if (mysqli_num_rows($check_verifcode_qry) > 0) {
          $stmt = $conn->prepare("UPDATE alumni SET status = 'Verified' WHERE alumni_id = ?");
          $stmt->bind_param("s", $account);
          $stmt->execute();

          $delete_qry = mysqli_query($conn, "DELETE FROM recovery_code WHERE email='$account_email'");
          echo "<script>
                  document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Verification code match!',
                        text: 'Now proceed to Alumni Dashboard.',
                        customClass: {
                            popup: 'swal-custom'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = '../alumniPage/dashboard_user.php';
                            exit();
                        }
                    });
                });
              </script>";
        } else {
          echo "verification code doesn't match!";
        }
      }
      // BACK BUTTON
      else if (isset($_POST['back_btn'])) {
        $sql_delete = "DELETE FROM recovery_code WHERE email='$account_email'";
        $conn->query($sql_delete);
        session_destroy();
        header('Location: ./login.php');
        exit();
      }
    }
  } else {
    header('Location: ../homepage.php');
    exit();
  }
  // FOR CHANGE PASSWORD
} else if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['verif_code']) && isset($_POST['submit_code'])) {
  $verif_code = $_POST['verif_code'];

  $check_verifcode_qry = mysqli_query($conn, "SELECT verification_code FROM recovery_code WHERE verification_code = '$verif_code'");

  if (mysqli_num_rows($check_verifcode_qry) > 0) {
    $delete_qry = mysqli_query($conn, "DELETE FROM recovery_code WHERE verification_code = '$verif_code'");
    echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                  Swal.fire({
                      icon: 'success',
                      title: 'Verification code match!',
                      text: 'Now proceed to changing your password.',
                      customClass: {
                          popup: 'swal-custom'
                      }
                  }).then((result) => {
                      if (result.isConfirmed) {
                          window.location.href = 'newpassword.php';
                          exit();
                      }
                  });
              });
            </script>";
  } else {
    echo "verification code doesn't match!";
  }
} // BACK BUTTON
else if (isset($_POST['back_btn'])) {
  $sql_delete = "DELETE FROM recovery_code WHERE email='$account_email'";
  $conn->query($sql_delete);
  session_destroy();
  header('Location: ./forgetpassword.php');
  exit();
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verification</title>
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
        <h5 class="text-center mb-4">We've sent the verification code to your email &nbsp;"<?php echo $_SESSION['email'] ?>"</h5>
        <form method="POST">
          <div class="form-group">
            <input type="number" name="verif_code" class="form-control" id="code" placeholder="Input the Verification Code">
          </div>
          <a href="#" class="back-to-login">Resend Code</a>
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
</body>

</html>