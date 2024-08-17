<?php
session_start();
date_default_timezone_set('Asia/Manila'); // Replace with your desired timezone
$conn = new mysqli("localhost", "root", "", "alumni_management_system");

// PHPMAILER
use PHPMailer\PHPMailer\PHPMailer;

require '../vendor/autoload.php';

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
            header('Location: ../loginPage/verification_code.php');
            exit();
        }
    } else {
        // Redirect to login if no matching user found
        session_destroy();
        header('Location: ./login.php');
        exit();
    }
}



$stud_id = "";
$email = "";
$log_email = "";
$pass = "";
$password = "";
$confirm_password = "";

// LOGIN
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['log_email']) && isset($_POST['log_password'])) {
    $log_email = strtolower($_POST['log_email']);
    $pass = $_POST['log_password'];

    // Check in users table
    $user = check_alumni($conn, 'alumni', $log_email, $pass);
    $user_type = 'alumni';

    // Check in admin table if not found in users
    if (!$user) {
        $user = check_login($conn, 'admin', $log_email, $pass);
        $user_type = 'admin';
    }
    if (!$user) {
        $user = check_login($conn, 'coordinator', $log_email, $pass);
        $user_type = 'coordinator';
    }
    if (!$user) {
        $user = check_alumni_archive($conn, 'alumni_archive', $log_email, $pass);
        $user_type = 'alumni_archive';
    }

    if ($user) {
        // Login success, set session variables
        switch ($user_type) {
            case 'alumni':
                $_SESSION['user_id'] = $user['alumni_id'];
                $_SESSION['user_email'] = $user['email'];
                break;
            case 'admin':
                $_SESSION['user_id'] = $user['admin_id'];
                $_SESSION['user_email'] = $user['email'];
                break;
            case 'coordinator':
                $_SESSION['user_id'] = $user['coor_id'];
                $_SESSION['user_email'] = $user['email'];
                break;
            case 'alumni_archive':
                $_SESSION['user_id'] = $user['alumni_id'];
                $_SESSION['user_email'] = $user['email'];
                break;
        }
        if ($user_type == 'admin') {
            // Redirect to a ADMIN DASHBOARD

            $user_id = $_SESSION['user_id'];

            // Update the last_login time
            $current_time = date("Y-m-d H:i:s"); // Format: 2024-08-15 14:35:00
            $sql = "UPDATE admin SET last_login = '$current_time' WHERE admin_id = $user_id";
            $conn->query($sql);

            // SUCCESS LOGIN ADMIN
            $icon = 'success';
            $iconHtml = '<i class="fas fa-check-circle"></i>';
            $title = 'Login Successful!';
            $text = 'You will be redirected shortly to the Dashboard.';
            $redirectUrl = '../adminPage/dashboard_admin.php';

            echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        alertMessage('$redirectUrl', '$title', '$text', '$icon', '$iconHtml');
                    });
                </script>";
            sleep(2);
        } else if ($user_type == 'coordinator') {
            // Redirect to COORDINATOR

            $user_id = $_SESSION['user_id'];

            // Update the last_login time
            $current_time = date("Y-m-d H:i:s"); // Format: 2024-08-15 14:35:00
            $sql = "UPDATE coordinator SET last_login = '$current_time' WHERE coor_id = $user_id";
            $conn->query($sql);

            // SUCCESS LOGIN COORDINATOR
            $icon = 'success';
            $iconHtml = '<i class="fas fa-check-circle"></i>';
            $title = 'Login Successful!';
            $text = 'You will be redirected shortly to the Dashboard.';
            $redirectUrl = '../coordinatorPage/dashboard_coor.php';

            echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        alertMessage('$redirectUrl', '$title', '$text', '$icon', '$iconHtml');
                    });
                </script>";
            sleep(2);
        } else if ($user_type == 'alumni_archive') {
            // ARCHIVE
            if (isset($_SESSION['user_id']) && isset($_SESSION['user_email'])) {
                $account = $_SESSION['user_id'];
                $account_email = $_SESSION['user_email'];

                $sql = "SELECT * FROM alumni_archive WHERE alumni_id=$account";
                $result = $conn->query($sql);
                $row = $result->fetch_assoc();

                if ($row['status'] == "Inactive") {
                    // VERIFY ALUMNI_ARCHIVE INACTIVE ACCOUNT
                    $email = $_POST['log_email'];
                    $verification_code = sprintf("%06d", mt_rand(1, 999999));
                    $email = mysqli_real_escape_string($conn, $email);
                    $verification_code = mysqli_real_escape_string($conn, $verification_code);

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

                    // WARNING INACTIVE
                    $icon = 'warning';
                    $iconHtml = '<i class="fas fa-exclamation-triangle"></i>';
                    $title = 'Your Account is Inactive!';
                    $text = 'We send a verification code to your email to active your account.';
                    $redirectUrl = './inactiveVerification.php';

                    echo "<script>
                            document.addEventListener('DOMContentLoaded', function() {
                                alertMessage('$redirectUrl', '$title', '$text', '$icon', '$iconHtml');
                            });
                        </script>";
                }
            }
        } else {

            if (isset($_SESSION['user_id']) && isset($_SESSION['user_email'])) {
                $account = $_SESSION['user_id'];
                $account_email = $_SESSION['user_email'];

                $sql = "SELECT * FROM alumni WHERE alumni_id=$account";
                $result = $conn->query($sql);
                $row = $result->fetch_assoc();

                if ($row['status'] == "Verified") {
                    // Redirect to ALUMNI DASHBOARD

                    $user_id = $_SESSION['user_id'];

                    // Update the last_login time
                    $current_time = date("Y-m-d H:i:s"); // Format: 2024-08-15 14:35:00
                    $sql = "UPDATE alumni SET last_login = '$current_time' WHERE alumni_id = $user_id";
                    $conn->query($sql);


                    // SUCCESS LOGIN ALUMNI
                    $icon = 'success';
                    $iconHtml = '<i class="fas fa-check-circle"></i>';
                    $title = 'Login Successful!';
                    $text = 'You will be redirected shortly to the Dashboard.';
                    $redirectUrl = '../alumniPage/dashboard_user.php';

                    echo "<script>
                            document.addEventListener('DOMContentLoaded', function() {
                                alertMessage('$redirectUrl', '$title', '$text', '$icon', '$iconHtml');
                            });
                        </script>";
                    sleep(2);
                } else {

                    $email = $_SESSION['user_email'];
                    $verification_code = sprintf("%06d", mt_rand(1, 999999));
                    $email = mysqli_real_escape_string($conn, $email);
                    $verification_code = mysqli_real_escape_string($conn, $verification_code);

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
                    $icon = 'warning';
                    $iconHtml = '<i class="fas fa-exclamation-triangle"></i>';
                    $title = 'Account Not Verified!';
                    $text = 'Verified your Account First to continue.';
                    $redirectUrl = './verification_code.php';

                    echo "<script>
                            document.addEventListener('DOMContentLoaded', function() {
                                alertMessage('$redirectUrl', '$title', '$text', '$icon', '$iconHtml');
                            });
                        </script>";
                }
            }
        }
    } else {
        // ERROR LOGIN FAILED
        $icon = 'error';
        $iconHtml = '<i class=\"fas fa-exclamation-circle\"></i>';
        $title = 'Incorrect Student ID / Email and Password!';
        $text = 'Please try again.';

        echo "<script>
              document.addEventListener('DOMContentLoaded', function() {
                  warningError('$title', '$text', '$icon', '$iconHtml');
              });
          </script>";
        sleep(2);
    }
    // SIGNUP 
} else if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $stud_id = $_POST['student_id'];
    $email = strtolower($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    $hashedpassword = password_hash($password, PASSWORD_BCRYPT); // Hashing the password
    $hashedpassword = password_hash($confirm_password, PASSWORD_BCRYPT); // Hashing the password

    // Check if email or student ID exists in both the active and archive tables
    $emailCheck = mysqli_query($conn, "SELECT * FROM alumni WHERE email='$email'");
    $emailCheck_archive = mysqli_query($conn, "SELECT * FROM alumni_archive WHERE email='$email'");
    $idCheck = mysqli_query($conn, "SELECT * FROM alumni WHERE student_id='$stud_id'");
    $idCheck_archive = mysqli_query($conn, "SELECT * FROM alumni_archive WHERE student_id='$stud_id'");

    // Email and student ID validation
    if (mysqli_num_rows($emailCheck) > 0 || mysqli_num_rows($emailCheck_archive) > 0) {

        // WARNING EXISTING ACCOUNT
        $icon = 'warning';
        $iconHtml = '<i class="fas fa-exclamation-triangle"></i>';
        $title = 'Email Already Exists!';
        $text = 'Please try again.';

        echo "<script>
              document.addEventListener('DOMContentLoaded', function() {
                  warningError('$title', '$text', '$icon', '$iconHtml');
              });
          </script>";
        sleep(2);
    } elseif (mysqli_num_rows($idCheck) > 0 || mysqli_num_rows($idCheck_archive) > 0) {

        // WARNING EXISTING ACCOUNT
        $icon = 'warning';
        $iconHtml = '<i class="fas fa-exclamation-triangle"></i>';
        $title = 'Student ID Already Exists!';
        $text = 'Please try again.';

        echo "<script>
              document.addEventListener('DOMContentLoaded', function() {
                  warningError('$title', '$text', '$icon', '$iconHtml');
              });
          </script>";
        sleep(2);
    } else {
        // Check if password and confirm password match
        if ($password !== $confirm_password) {
            
            // WARNING NOT MATCH PASSWORD
            $icon = 'warning';
            $iconHtml = '<i class="fas fa-exclamation-triangle"></i>';
            $title = 'Password do not match!';
            $text = 'Please try again.';

            echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        warningError('$title', '$text', '$icon', '$iconHtml');
                    });
                </script>";
            sleep(2);
        } else {
            $idCheck_alumni = mysqli_query($conn, "SELECT * FROM list_of_graduate WHERE student_id='$stud_id'");

            if (mysqli_num_rows($idCheck_alumni) > 0) {
                // Check if student exists in the list_of_graduate table
                $stmt = $conn->prepare("SELECT * FROM list_of_graduate WHERE student_id = ? AND email = ?");
                $stmt->bind_param("ss", $stud_id, $email);
                $stmt->execute();
                $user_result = $stmt->get_result();

                if ($user_result->num_rows > 0) {
                    $stmt->close();
                    // Transfer data
                    $filePath = '../assets/profile_icon.jpg';
                    $imageData = file_get_contents($filePath);
                    $imageDataEscaped = addslashes($imageData);

                    // Update graduate information
                    $sql = "UPDATE list_of_graduate SET email='$email', password='$hashedpassword', picture='$imageDataEscaped' WHERE student_id='$stud_id'";
                    $result = $conn->query($sql);

                    // Insert into alumni table
                    $sql_restore = "INSERT INTO alumni (student_id, fname, mname, lname, gender, course, contact, address, email, password, picture, date_created) 
                        SELECT student_id, fname, mname, lname, gender, course, contact, address, '$email', '$hashedpassword', '$imageDataEscaped', date_created 
                        FROM list_of_graduate WHERE student_id='$stud_id'";
                    $conn->query($sql_restore);

                    $sql = "SELECT * FROM list_of_graduate WHERE student_id=$stud_id";
                    $result = $conn->query($sql);
                    $row = $result->fetch_assoc();

                    $batchYearRange = $row["batch"] ?? ''; // Assuming batch_years are in column 8
                    $startYear = $endYear = ''; // Initialize with empty values

                    if (strpos($batchYearRange, '-') !== FALSE) {
                        list($startYear, $endYear) = explode('-', $batchYearRange);
                        // Trim spaces
                        $startYear = trim($startYear);
                        $endYear = trim($endYear);
                    }

                    $stmt = $conn->prepare("UPDATE alumni SET batch_startYear = '$startYear', batch_endYear = '$endYear' WHERE student_id = ?");
                    $stmt->bind_param("s", $stud_id);
                    $stmt->execute();
                    $stmt->close();

                    $stmt = $conn->prepare("UPDATE alumni SET status = 'Unverified' WHERE student_id = ?");
                    $stmt->bind_param("s", $stud_id);
                    $stmt->execute();
                    $stmt->close();

                    // Delete from list_of_graduate table
                    $sql_delete = "DELETE FROM list_of_graduate WHERE student_id='$stud_id'";
                    $conn->query($sql_delete);

                    // Check if user is an alumni
                    $stmt = $conn->prepare("SELECT * FROM alumni WHERE student_id = ? AND email = ?");
                    $stmt->bind_param("ss", $stud_id, $email);
                    $stmt->execute();
                    $user_result = $stmt->get_result();

                    if ($user_result->num_rows > 0) {
                        $verification_code = sprintf("%06d", mt_rand(1, 999999));

                        $insert_verifcodes_qry = mysqli_query($conn, "INSERT INTO recovery_code(email, verification_code) VALUES('$email', '$verification_code')");

                        // PHPMailer setup
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

                        $sql_change = "SELECT alumni_id FROM alumni WHERE student_id = $stud_id";
                        $result = $conn->query($sql_change);
                        $row = $result->fetch_assoc();

                        // Set session variables
                        $_SESSION['email'] = $email;
                        $_SESSION['user_email'] = $email;
                        $_SESSION['user_id'] = $row['alumni_id'];

                        $stmt->close();

                        // WARNING NOT VERIFIED
                        $icon = 'success';
                        $iconHtml = '<i class="fas fa-check-circle"></i>';
                        $title = 'Account successfully register.';
                        $text = 'We send a verification code to your email to verify your account.';
                        $redirectUrl = './verification_code.php';

                        echo "<script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    alertMessage('$redirectUrl', '$title', '$text', '$icon', '$iconHtml');
                                });
                            </script>";
                    } else {
                        session_destroy();
                        header('Location: ./login.php');
                        exit();
                    }
                } else {
                    // WARNING NOT MATCH PASSWORD
                    $icon = 'warning';
                    $iconHtml = '<i class="fas fa-exclamation-triangle"></i>';
                    $title = 'Your cvsu email and student id do not match!';
                    $text = 'Please try again.';
                    echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            warningError('$title', '$text', '$icon', '$iconHtml');
                        });
                    </script>";
                    sleep(2);
                }
            } else {

                // WARNING NO ALUMNI
                $icon = 'error';
                $iconHtml = '<i class=\"fas fa-exclamation-circle\"></i>';
                $title = 'There is no alumni with student ID ' . $stud_id;
                $text = 'Please try again.';

                echo "<script>
                        document.addEventListener('DOMContentLoaded', function() {
                            warningError('$title', '$text', '$icon', '$iconHtml');
                        });
                    </script>";
                sleep(2);
            }
        }
    }
}

// LOGIN CHECK FOR ADMIN AND COORDINATOR
function check_login($conn, $table, $log_email, $pass)
{
    $sql = "SELECT * FROM $table WHERE email = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $log_email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc(); {
            return $user;
        }
    }

    return false;
}

// LOGIN CHECK FOR ALUMNI
function check_alumni($conn, $table, $log_email, $pass)
{
    $sql = "SELECT * FROM $table WHERE (student_id = ? OR email = ?) LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $log_email, $log_email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($pass, $user['password'])) {
            return $user;
        }
    }
    return false;
}

// ALUMNI ARCHIVE CHECKER
function check_alumni_archive($conn, $table, $log_email, $pass)
{
    $sql = "SELECT * FROM $table WHERE (student_id = ? OR email = ?) LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $log_email, $log_email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($pass, $user['password'])) {
            return $user;
        }
    }
    return false;
}

// SIGNUP STRONG PASSWORD

$errors = array();



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log in || Sign up form</title>
    <!-- font awesome icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" integrity="sha512-KfkfwYDsLkIlwQp6LFnl8zNdLGxu9YAA1QvwINks4PhcElQSvqcyVLLD9aMhXd13uQjoXtEKNosOWaZqXgel0g==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="shortcut icon" href="cvsu.png" type="image/svg+xml">
    <!-- css stylesheet -->
    <link rel="stylesheet" href="css/login.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>
    <!-- FOR SIGN UP -->
    <div class="container" id="container">
        <div class="form-container sign-up-container">
            <form action="#" method="POST" id="signup">
                <h1>Sign Up</h1>
                <div class="alert alert-danger text-center error-list" id="real-time-errors"></div>
                <div class="infield">
                    <input type="text" id="student_id" placeholder="Student ID" name="student_id" value="<?php echo htmlspecialchars($stud_id); ?>" required />
                    <label></label>
                </div>
                <div class="infield">
                    <input type="email" placeholder="Email" name="email" value="<?php echo htmlspecialchars($email); ?>" required />
                    <label></label>
                </div>
                <div class="infield" style="position: relative;">
                    <input type="password" placeholder="Password" id="password" name="password" onkeyup="validatePassword()" value="<?php echo htmlspecialchars($password); ?>" min="0" required />
                    <img id="togglePassword" src="eye-close.png" alt="Show/Hide Password" onclick="togglePasswordVisibility('password', 'togglePassword')" style="height: 15px; width: 20px; position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;" />
                    <label></label>
                </div>
                <div class="infield" style="position: relative;">
                    <input type="password" placeholder="Confirm Password" id="confirm_password" onkeyup="validatePassword()" name="confirm_password" value="<?php echo htmlspecialchars($confirm_password); ?>" required />
                    <img id="toggleConfirmPassword" src="eye-close.png" alt="Show/Hide Password" onclick="togglePasswordVisibility('confirm_password', 'toggleConfirmPassword')" style="height: 15px; width: 20px; position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;" />
                    <label></label>
                </div>
                <button type="submit" name="submit">Sign Up</button>
            </form>
        </div>

        <!-- FOR LOG IN -->
        <div class="form-container log-in-container">
            <form action="#" method="POST" id="login" onsubmit="disableButton(this)">
                <h1>Log in</h1>
                <div class="infield">
                    <input type="text" placeholder="Student ID / Email" name="log_email" required />
                    <label></label>
                </div>
                <div class="infield" style="position: relative;">
                    <input type="password" placeholder="Password" id="log_password" name="log_password" required style="padding-right: 30px;" />
                    <img id="toggleLogPassword" src="eye-close.png" alt="Show/Hide Password" onclick="togglePasswordVisibility('log_password', 'toggleLogPassword')" style="height: 15px; width: 20px; position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;" />
                    <label></label>
                </div>
                <br>
                <a href="./forgetpassword.php" class="forgot">Forgot your password?</a>
                <button type="submit">Log In</button>
            </form>
        </div>
        <div class="overlay-container" id="overlayCon">
            <div class="overlay">
                <div class="overlay-panel overlay-left">
                    <img src="cvsu.png" usemap="#logo">
                    <map name="logo">
                        <area shape="poly" coords="101,8,200,106,129,182,73,182,1,110" href="../homepage.php">
                    </map>
                    <br>
                    <br>
                    <button class="ghost" id="logIn">Log In</button>
                </div>
                <div class="overlay-panel overlay-right">
                    <img src="cvsu.png" usemap="#logo">
                    <map name="logo">
                        <area shape="poly" coords="101,8,200,106,129,182,73,182,1,110" href="../homepage.php">
                    </map>
                    <br>
                    <br>
                    <button class="ghost" id="signUp">Sign Up</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', (event) => {
            const signUpButton = document.getElementById('signUp');
            const logInButton = document.getElementById('logIn');
            const container = document.getElementById('container');

            // Function to read URL parameters
            function getQueryParams() {
                const params = {};
                window.location.search.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(str, key, value) {
                    params[key] = value;
                });
                return params;
            }

            // Check URL parameters and activate the appropriate tab
            const params = getQueryParams();
            if (params.tab === 'signup') {
                container.classList.add('right-panel-active');
            } else if (params.tab === 'login') {
                container.classList.remove('right-panel-active');
            }

            signUpButton.addEventListener('click', () => {
                container.classList.add('right-panel-active');
            });

            logInButton.addEventListener('click', () => {
                container.classList.remove('right-panel-active');
            });
        });

        // FOR BATCH
        document.addEventListener('DOMContentLoaded', function() {
            const startYearSelect = document.getElementById('startYear');
            const endYearSelect = document.getElementById('endYear');

            // Disable endYear select by default if no start year is selected
            if (!startYearSelect.value) {
                endYearSelect.disabled = true;
            } else {
                populateEndYearOptions(parseInt(startYearSelect.value));
            }

            startYearSelect.addEventListener('change', function() {
                const selectedStartYear = parseInt(this.value);
                endYearSelect.disabled = false;

                populateEndYearOptions(selectedStartYear);
            });

            function populateEndYearOptions(selectedStartYear) {
                const currentYear = new Date().getFullYear();
                const yearRange = 21; // Adjust this number as needed
                const selectedEndYear = endYearSelect.getAttribute('data-selected'); // Get the selected end year

                // Clear current endYear options
                endYearSelect.innerHTML = '<option value="" selected hidden disabled>Batch: To Year</option>';

                // Generate new options for endYear
                for (let year = selectedStartYear + 1; year <= currentYear + yearRange; year++) {
                    const option = document.createElement('option');
                    option.value = year;
                    option.textContent = year;
                    if (year == selectedEndYear) {
                        option.selected = true; // Preserve the selected end year
                    }
                    endYearSelect.appendChild(option);
                }
            }
        });

        // FOR NO NIGGATIVE NUMBERS
        document.addEventListener("DOMContentLoaded", function() {
            const studentIdInput = document.getElementById("student_id");

            studentIdInput.addEventListener("input", function(event) {
                let value = studentIdInput.value;
                // Replace all non-numeric characters
                value = value.replace(/[^0-9]/g, '');
                studentIdInput.value = value;
            });
        });

        // PASS VISIBILITY
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

        // PREVENT MULTIPLE FORM SUBMITTIONS
        function disableButton(form) {
            const submitButton = form.querySelector('button[type="submit"]');
            submitButton.disabled = true;
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
                } else if (!/[A-Z]/.test(password)) {
                    errorMessages.push("Password must contain at least one uppercase letter.");
                } else if (!/[a-z]/.test(password)) {
                    errorMessages.push("Password must contain at least one lowercase letter.");
                } else if (!/\d/.test(password)) {
                    errorMessages.push("Password must contain at least one digit.");
                } else if (!/[^a-zA-Z\d]/.test(password)) {
                    errorMessages.push("Password must contain at least one special character.");
                } else if (confirmPassword && password !== confirmPassword) {
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