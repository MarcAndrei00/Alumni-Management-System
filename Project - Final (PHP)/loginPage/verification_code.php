<?php
session_start();
$connect = new mysqli("localhost","root","","alumni_management_system");

$email = $_SESSION['email'];
if($email == 0){
  header('Location: login.php');
}
 
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['verif_code']) && isset($_POST['submit_code'])) {
  $verif_code = $_POST['verif_code'];

  $check_verifcode_qry = mysqli_query($connect,"SELECT verification_code FROM recovery_code WHERE verification_code = '$verif_code'");

  if(mysqli_num_rows($check_verifcode_qry) > 0){
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
                      }
                  });
              });
            </script>";
    $delete_qry = mysqli_query($connect,"DELETE FROM recovery_code WHERE verification_code = '$verif_code'");
  }
  else{
      echo "verification code doesn't match!";
  }
}
else if(isset($_POST['back_btn'])){
  session_destroy();
  header('Location: forgetpassword.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verification</title>
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
      box-shadow: 0 0 20px rgba(0,0,0,0.1);
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
      <h5 class="text-center mb-4">We've sent the verification code to your email &nbsp;"<?php echo $_SESSION['email']?>"</h5>
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
