<?php
session_start();
$conn = new mysqli("localhost", "root", "", "alumni_management_system");

// SESSION
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
        $user = $user_result->fetch_assoc();
    }
    $stmt->close();

    // Check if user is a coordinator
    $stmt = $conn->prepare("SELECT * FROM coordinator WHERE coor_id = ? AND email = ?");
    $stmt->bind_param("ss", $account, $account_email);
    $stmt->execute();
    $user_result = $stmt->get_result();

    if ($user_result->num_rows > 0) {
        // User is a coordinator
        header('Location: ../../coordinatorPage/dashboard_coor.php');
        exit();
    }
    $stmt->close();

    // Check if user is a alumni_archive
    $stmt = $conn->prepare("SELECT * FROM alumni_archive WHERE alumni_id = ? AND email = ?");
    $stmt->bind_param("ss", $account, $account_email);
    $stmt->execute();
    $user_result = $stmt->get_result();

    if ($user_result->num_rows > 0) {
        session_destroy();
        header("Location: ../../homepage.php");
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
} else {
    // Redirect to login if no matching user found
    session_destroy();
    header('Location: ../../homepage.php');
    exit();
}

$confirmPass = "";
$newPass = "";
$password = "";
$errorMessage = "";

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Show the data of admin
    if (!isset($_GET['id'])) {
        header("location: ./profile.php");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $currentPass = $_POST['currentPass'];
    $newPass = $_POST['newPass'];
    $confirmPass = $_POST['confirmPass'];
    $userId = $_GET['id'];

    $query = "SELECT password FROM admin WHERE admin_id = $userId";
    $result = mysqli_query($conn, $query);
    $account = mysqli_fetch_assoc($result);

    // Check if the current password is correct
    if ($currentPass !== $account['password']) {
        $errorMessage = "Current password is incorrect.";
    }
    // Check if new password and confirm password match
    elseif ($newPass !== $confirmPass) {
        $errorMessage = "New password and confirm password do not match.";
    } else {
        // Update the password in the database
        $updateQuery = "UPDATE admin SET password = '$newPass' WHERE admin_id = $userId";
        if (mysqli_query($conn, $updateQuery)) {
            echo "
            <script>
                // Wait for the document to load
                document.addEventListener('DOMContentLoaded', function() {
                    // Use SweetAlert2 for the alert
                    Swal.fire({
                            title: 'Password Change Successfully',
                            timer: 2000,
                            showConfirmButton: true, // Show the confirm button
                            confirmButtonColor: '#4CAF50', // Set the button color to green
                            confirmButtonText: 'OK' // Change the button text if needed
                    }).then(function() {
                        // Redirect after the alert closes
                        window.location.href = './profile.php';
                    });
                });
            </script>
            ";
        } else {
            $errorMessage = "Error changing password. Please try again.";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
    <title>Change Password</title>
    <link rel="shortcut icon" href="../../assets/cvsu.png" type="image/svg+xml">
    <link rel="stylesheet" href="css/change_pass.css">
    <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-growl/1.0.0/jquery.bootstrap-growl.min.js" integrity="sha512-pBoUgBw+mK85IYWlMTSeBQ0Djx3u23anXFNQfBiIm2D8MbVT9lr+IxUccP8AMMQ6LCvgnlhUCK3ZCThaBCr8Ng==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>
    <input type="checkbox" id="menu-toggle">
    <div class="sidebar">
        <div class="side-header">
            <h3><img src="https://cvsu-imus.edu.ph/student-portal/assets/images/logo-mobile.png"></img><span>CVSU</span></h3>
        </div>

        <div class="side-content">
            <div class="profile">
                <i class="bi bi-person-circle"></i>
                <h4><?php echo $user['fname']; ?></h4>
                <small style="color: white;"><?php echo $user['email']; ?></small>
            </div>

            <div class="side-menu">
                <ul>
                    <li>
                        <a href="../dashboard_admin.php">
                            <span class="las la-home" style="color:#fff"></span>
                            <small>DASHBOARD</small>
                        </a>
                    </li>
                    <li>
                        <a href="./change_pass.php" class="active">
                            <span class="las la-user-alt" style="color:#fff"></span>
                            <small>PROFILE</small>
                        </a>
                    </li>
                    <li>
                        <a href="../alumni/alumni.php">
                            <span class="las la-th-list" style="color:#fff"></span>
                            <small>ALUMNI</small>
                        </a>
                    </li>
                    <li>
                        <a href="../coordinator/coordinator.php">
                            <span class="las la-user-cog" style="color:#fff"></span>
                            <small>COORDINATOR</small>
                        </a>
                    </li>
                    <li>
                        <a href="../event/event.php">
                            <span class="las la-calendar" style="color:#fff"></span>
                            <small>EVENT</small>
                        </a>
                    </li>
                    <li>
                        <a href="../settings/about.php">
                            <span class="las la-cog" style="color:#fff"></span>
                            <small>SETTINGS</small>
                        </a>
                    </li>
                    <li>
                        <a href="../report/report.php">
                            <span class="las la-clipboard-check" style="color:#fff"></span>
                            <small>REPORT</small>
                        </a>
                    </li>
                    <li>
                        <a href="../archive/alumni_archive.php">
                            <span class="las la-archive" style="color:#fff"></span>
                            <small>ARCHIVE</small>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="main-content">
        <header>
            <div class="header-content">
                <label for="menu-toggle">
                    <span class="las la-bars bars" style="color: white;"></span>
                </label>

                <div class="header-menu">
                    <label for="">
                    </label>

                    <div class="user">
                        <a href="../logout.php">
                            <span class="las la-power-off" style="font-size: 30px; border-left: 1px solid #fff; padding-left:10px; color:#fff"></span>
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <main>
            <div class="page-header">
                <h1><strong>Profile</strong></h1>
            </div>

            <div class="container-fluid" id="page-content">
                <?php
                if (!empty($errorMessage)) {
                    echo "<script>";
                    echo "Swal.fire({";
                    echo "  icon: 'error',";
                    echo "  title: 'Oops...',";
                    echo "  text: '$errorMessage',";
                    echo "  timer: 2000,";
                    echo "})";
                    echo "</script>";
                }
                ?>


                <div class="row">
                    <div class="container-fluid" id="main-container">
                        <div class="container-fluid" id="content-container">
                            <span>
                                <h3>CHANGE PASSWORD</h3>
                            </span>
                            <br>
                            <form method="POST" onsubmit="return submitForm(this);">
                                <div class="mb-3">
                                    <label for="currentPass" class="form-label">Enter Current Password</label>
                                    <div class="form-group password-container" style="position: relative; display: flex; align-items: center;">
                                        <input type="password" id="currentPass" name="currentPass" class="form-control" required>
                                        <img id="toggleCurrentPass" src="eye-close.png" alt="Show/Hide Password" onclick="togglePasswordVisibility('currentPass', 'toggleCurrentPass')" style="height: 15px; width: 20px; position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;" />
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="newPass" class="form-label">Change Password</label>
                                    <div class="form-group password-container" style="position: relative; display: flex; align-items: center;">
                                        <input type="password" id="newPass" name="newPass" class="form-control" required>
                                        <img id="toggleNewPass" src="eye-close.png" alt="Show/Hide Password" onclick="togglePasswordVisibility('newPass', 'toggleNewPass')" style="height: 15px; width: 20px; position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;" />
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="confirmPass" class="form-label">Confirm Password</label>
                                    <div class="form-group password-container" style="position: relative; display: flex; align-items: center;">
                                        <input type="password" id="confirmPass" name="confirmPass" class="form-control" required>
                                        <img id="toggleConfirmPass" src="eye-close.png" alt="Show/Hide Password" onclick="togglePasswordVisibility('confirmPass', 'toggleConfirmPass')" style="height: 15px; width: 20px; position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;" />
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="container-fluid">
                                        <div class="buttons">
                                            <button type="submit" class="btn" id="button1">CHANGE PASSWORD</button>
                                            <a href="./profile.php"><button type="button" class="btn" id="button2">CANCEL</button></a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
    </div>
    <script>
        function submitForm(form) {
            Swal.fire({
                    title: 'Do you want to continue?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e03444',
                    cancelButtonColor: '#ffc404',
                    confirmButtonText: 'Submit'
                })
                .then((result) => {
                    if (result.isConfirmed) {
                        form.submit(); // Submit the form
                    }
                });
            return false; // Prevent default form submission
        }

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