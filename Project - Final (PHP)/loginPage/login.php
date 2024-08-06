<?php
session_start();


$servername = "localhost";
$db_username = "root";
$db_password = "";
$db_name = "alumni_management_system";
$conn = new mysqli($servername, $db_username, $db_password, $db_name);

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
            // $_SESSION = array();
            // session_destroy();
            // header("Location: ./login.php");

            header("Location: ./verificationcode.php");
            exit;
            
        }
    }
    $stmt->close();

    // Redirect to login if no matching user found
    header('Location: ./login.php');
    exit();
}


$stud_id = "";
$email = "";
$log_email = "";
$pass = "";
$password = "";
$confirm_password = "";

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
        }

        if ($user_type == 'admin') {
            // Redirect to a ADMIN DASHBOARD
            echo "
            <script>
                // Wait for the document to load
                document.addEventListener('DOMContentLoaded', function() {
                    // Use SweetAlert2 for the alert
                    Swal.fire({
                            title: 'Login Successfully',
                            timer: 2000,
                            showConfirmButton: true, // Show the confirm button
                            confirmButtonColor: '#4CAF50', // Set the button color to green
                            confirmButtonText: 'OK' // Change the button text if needed
                    }).then(function() {
                        // Redirect after the alert closes
                        window.location.href = '../adminPage/dashboard_admin.php';
                    });
                });
            </script>
            ";
        } else if ($user_type == 'coordinator') {
            // Redirect to COORDINATOR
            echo "<script>
                // Wait for the document to load
                document.addEventListener('DOMContentLoaded', function() {
                    // Use SweetAlert2 for the alert
                    Swal.fire({
                            title: 'Login Successfully',
                            timer: 2000,
                            showConfirmButton: true, // Show the confirm button
                            confirmButtonColor: '#4CAF50', // Set the button color to green
                            confirmButtonText: 'OK' // Change the button text if needed
                    }).then(function() {
                        // Redirect after the alert closes
                         window.location.href = '../coordinatorPage/dashboard_coor.php';
                    });
                });
            </script>";
        } else {

            if (isset($_SESSION['user_id']) && isset($_SESSION['user_email'])) {
                $account = $_SESSION['user_id'];
                $account_email = $_SESSION['user_email'];

                $sql = "SELECT * FROM alumni WHERE alumni_id=$account";
                $result = $conn->query($sql);
                $row = $result->fetch_assoc();

                if ($row['status'] == "Verified") {
                    // Redirect to ALUMNI DASHBOARD
                    echo "<script>
                        // Wait for the document to load
                        document.addEventListener('DOMContentLoaded', function() {
                            // Use SweetAlert2 for the alert
                            Swal.fire({
                                    title: 'Login Successfully',
                                    timer: 2000,
                                    showConfirmButton: true, // Show the confirm button
                                    confirmButtonColor: '#4CAF50', // Set the button color to green
                                    confirmButtonText: 'OK' // Change the button text if needed
                            }).then(function() {
                                // Redirect after the alert closes
                                window.location.href = '../alumniPage/dashboard_user.php';
                            });
                        });
                    </script>";
                } else {
                    echo "<script>
                    // Wait for the document to load
                    document.addEventListener('DOMContentLoaded', function() {
                        // Use SweetAlert2 for the alert
                        Swal.fire({
                                title: 'Verified your Account First',
                                timer: 2000,
                                showConfirmButton: true, // Show the confirm button
                                confirmButtonColor: '#4CAF50', // Set the button color to green
                                confirmButtonText: 'OK' // Change the button text if needed
                        }).then(function() {
                            // Redirect after the alert closes
                            window.location.href = './verificationcode.php';
                        });
                    });
                </script>";
                }
            }
        }
    } else {
        // Login failed
        echo "<script>
            // Wait for the document to load
            document.addEventListener('DOMContentLoaded', function() {
                // Use SweetAlert2 for the alert
                Swal.fire({
                    title: 'Incorrect Student ID / Email and Password',
                    timer: 4000,
                    showConfirmButton: true, // Show the confirm button
                    confirmButtonColor: '#4CAF50', // Set the button color to green
                    confirmButtonText: 'OK' // Change the button text if needed
                });
            });
        </script>";
    }
} else if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $stud_id = $_POST['student_id'];
    $email = strtolower($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // email and user existing check
    $emailCheck = mysqli_query($conn, "SELECT * FROM alumni WHERE email='$email'");
    $emailCheck_archive = mysqli_query($conn, "SELECT * FROM alumni_archive WHERE email='$email'");
    $idCheck = mysqli_query($conn, "SELECT * FROM alumni WHERE student_id='$stud_id'");
    $idCheck_archive = mysqli_query($conn, "SELECT * FROM alumni_archive WHERE student_id='$stud_id'");


    if (mysqli_num_rows($emailCheck) > 0) {
        // $errorMessage = "Email Already Exists";
        echo "<script>
            // Wait for the document to load
            document.addEventListener('DOMContentLoaded', function() {
                // Use SweetAlert2 for the alert
                Swal.fire({
                    title: 'Email Already Exists',
                    timer: 4000,
                    showConfirmButton: true, // Show the confirm button
                    confirmButtonColor: '#4CAF50', // Set the button color to green
                    confirmButtonText: 'OK' // Change the button text if needed
                });
            });
        </script>";
    } else if (mysqli_num_rows($emailCheck_archive) > 0) {
        // $errorMessage = "Email Already Exists";
        echo "<script>
            // Wait for the document to load
            document.addEventListener('DOMContentLoaded', function() {
                // Use SweetAlert2 for the alert
                Swal.fire({
                    title: 'Email Already Exists',
                    timer: 4000,
                    showConfirmButton: true, // Show the confirm button
                    confirmButtonColor: '#4CAF50', // Set the button color to green
                    confirmButtonText: 'OK' // Change the button text if needed
                });
            });
        </script>";
    } else if (mysqli_num_rows($idCheck) > 0) {
        // $errorMessage = "Student ID Already Exists";
        echo "<script>
            // Wait for the document to load
            document.addEventListener('DOMContentLoaded', function() {
                // Use SweetAlert2 for the alert
                Swal.fire({
                    title: 'Student ID Already Exists',
                    timer: 4000,
                    showConfirmButton: true, // Show the confirm button
                    confirmButtonColor: '#4CAF50', // Set the button color to green
                    confirmButtonText: 'OK' // Change the button text if needed
                });
            });
        </script>";
    } else if (mysqli_num_rows($idCheck_archive) > 0) {
        // $errorMessage = "Student ID Already Exists";
        echo "<script>
            // Wait for the document to load
            document.addEventListener('DOMContentLoaded', function() {
                // Use SweetAlert2 for the alert
                Swal.fire({
                    title: 'Student ID Already Exists',
                    timer: 4000,
                    showConfirmButton: true, // Show the confirm button
                    confirmButtonColor: '#4CAF50', // Set the button color to green
                    confirmButtonText: 'OK' // Change the button text if needed
                });
            });
        </script>";
    } else {

        // Check if new password and confirm password match
        if ($password !== $confirm_password) {
            // $errorMessage = "New password and confirm password do not match.";
            echo "<script>
            // Wait for the document to load
            document.addEventListener('DOMContentLoaded', function() {
                // Use SweetAlert2 for the alert
                Swal.fire({
                    title: 'Password and confirm password do not match!',
                    timer: 4000,
                    showConfirmButton: true, // Show the confirm button
                    confirmButtonColor: '#4CAF50', // Set the button color to green
                    confirmButtonText: 'OK' // Change the button text if needed
                });
            });
        </script>";
        } else {

            $filePath = '../assets/profile_icon.jpg';
            $imageData = file_get_contents($filePath);
            $imageDataEscaped = addslashes($imageData);

            $sql = "INSERT INTO alumni SET student_id='$stud_id', email='$email', password='$password', picture='$imageDataEscaped'";
            $result = $conn->query($sql);

            if ($result) {
                // $successMessage = "Coordinator Edited Successfully";
                echo "
            <script>
                // Wait for the document to load
                document.addEventListener('DOMContentLoaded', function() {
                    // Use SweetAlert2 for the alert
                    Swal.fire({
                        title: 'Account Successfully Registered',
                        timer: 2000,
                        showConfirmButton: true, // Show the confirm button
                        confirmButtonColor: '#4CAF50', // Set the button color to green
                        confirmButtonText: 'OK' // Change the button text if needed
                        }).then(function() {
                        // Redirect after the alert closes
                        window.location.href = './login.php';
                    });
                });
            </script>
            ";
            }
        }
    }
}

// LOGIN CHECK FOR ADMIN AND COORDINATOR
function check_login($conn, $table, $log_email, $pass)
{
    $sql = "SELECT * FROM $table WHERE email = ? AND password = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $log_email, $pass);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }

    return false;
}

function check_alumni($conn, $table, $log_email, $pass)
{
    $sql = "SELECT * FROM $table WHERE (student_id = ? OR email = ?) AND password = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $log_email, $log_email, $pass);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    return false;
}
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
            <form action="#" method="POST">
                <h1>Sign Up</h1>

                <div class="infield">
                    <input type="text" id="student_id" placeholder="Student ID" name="student_id" value="<?php echo htmlspecialchars($stud_id); ?>" required />
                    <label></label>
                </div>
                <div class="infield">
                    <input type="email" placeholder="Email" name="email" value="<?php echo htmlspecialchars($email); ?>" required />
                    <label></label>
                </div>
                <div class="infield" style="position: relative;">
                    <input type="password" placeholder="Password" id="password" name="password" value="<?php echo htmlspecialchars($password); ?>" min="0" required />
                    <img id="togglePassword" src="eye-close.png" alt="Show/Hide Password" onclick="togglePasswordVisibility('password', 'togglePassword')" style="height: 15px; width: 20px; position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;" />
                    <label></label>
                </div>
                <div class="infield" style="position: relative;">
                    <input type="password" placeholder="Confirm Password" id="confirm_password" name="confirm_password" value="<?php echo htmlspecialchars($confirm_password); ?>" required />
                    <img id="toggleConfirmPassword" src="eye-close.png" alt="Show/Hide Password" onclick="togglePasswordVisibility('confirm_password', 'toggleConfirmPassword')" style="height: 15px; width: 20px; position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;" />
                    <label></label>
                </div>
                <button type="submit" name="submit">Sign Up</button>
            </form>
        </div>

        <!-- FOR LOG IN -->
        <div class="form-container log-in-container">
            <form action="#" method="POST">
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
                <a href="#" class="forgot">Forgot your password?</a>
                <button>Log In</button>
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