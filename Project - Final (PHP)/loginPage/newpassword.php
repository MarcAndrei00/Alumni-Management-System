<?php 
session_start();
$connect = new mysqli("localhost","root","","alumni_management_system");

$email = $_SESSION['email'];
if($email == 0){
  header('Location: login.php');
}

if(isset($_POST['submit'])){
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];
    
    if($new_pass == $confirm_pass){
        $match_pass_qry = mysqli_query($connect,"UPDATE alumni SET password = '$confirm_pass' WHERE email = '$email'");
        echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Password has been changed!',
                        text: 'Now you can login with your new password.',
                        customClass: {
                            popup: 'swal-custom'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'login.php';
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
<title>New Password</title>
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
        <img src="cvsu.png" alt="Warning Icon" class="icon-size">
    </div>
    <h5 class="text-center mb-4">New Password</h5>
    <form method="POST">
        <div class="form-group">
        <input type="password" name="new_password" class="form-control" id="password" placeholder="Create new password" required>
        </div>
        <div class="form-group">
        <input type="password" name="confirm_password" class="form-control" id="password" placeholder="Confirm your password" required>
        </div>
        <button type="submit" name="submit" class="btn btn-primary btn-block">Change</button>
    </form>
    </div>
</div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
`