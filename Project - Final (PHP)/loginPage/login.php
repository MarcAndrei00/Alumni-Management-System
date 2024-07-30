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
        // User is an alumni
        header('Location: ../alumniPage/dashboard_user.php');
        exit();
    }
    $stmt->close();

    header('Location: ./login.php');
    exit();
}


$stud_id = "";
$fname = "";
$mname = "";
$lname = "";
$gender = "";
$course = "";
$fromYear = "";
$toYear = "";
$contact = "";
$address = "";
$email = "";
$username = "";
$log_email = "";
$pass = "";
$password = "";

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
        $user = check_alumni($conn, 'pending', $log_email, $pass);
        $user_type = 'pending';
    }

    if (!$user) {
        $user = check_alumni($conn, 'alumni_archive', $log_email, $pass);
        $user_type = 'alumni_arc';
    }

    if (!$user) {
        $user = check_alumni($conn, 'declined_account', $log_email, $pass);
        $user_type = 'declined_account';
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
                         window.location.href = '../coordinatorPage/dashboard_coor.php';
                    });
                });
            </script>
            ";
        } else if ($user_type == 'pending') {
            // PENDING ACCOUNT
            echo "
            <script>
                // Wait for the document to load
                document.addEventListener('DOMContentLoaded', function() {
                    // Use SweetAlert2 for the alert
                    Swal.fire({
                        title: 'Your Account Is Under Review!',
                        timer: 4000,
                        showConfirmButton: true, // Show the confirm button
                        confirmButtonColor: '#4CAF50', // Set the button color to green
                        confirmButtonText: 'OK' // Change the button text if needed
                    });
                });
            </script>";
        } else if ($user_type == 'alumni_arc') {
            // ARCHIED ACCOUNT
            echo "
            <script>
                // Wait for the document to load
                document.addEventListener('DOMContentLoaded', function() {
                    // Use SweetAlert2 for the alert
                    Swal.fire({
                        title: 'Your Account Is Suspended!... If you Think it was Mistaken, Please Contact Adminitrator.',
                        timer: 4000,
                        showConfirmButton: true, // Show the confirm button
                        confirmButtonColor: '#4CAF50', // Set the button color to green
                        confirmButtonText: 'OK' // Change the button text if needed
                    });
                });
            </script>";

        } else if ($user_type == 'declined_account') {
            // DECLINED ACCOUNT
            echo "
            <script>
                // Wait for the document to load
                document.addEventListener('DOMContentLoaded', function() {
                    // Use SweetAlert2 for the alert
                    Swal.fire({
                        title: 'Your Application Had Been Declined',
                        timer: 4000,
                        showConfirmButton: true, // Show the confirm button
                        confirmButtonColor: '#4CAF50', // Set the button color to green
                        confirmButtonText: 'OK' // Change the button text if needed
                    });
                });
            </script>";
            
        } else {
            // Redirect to ALUMNI DASHBOARD
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
                        window.location.href = '../alumniPage/dashboard_user.php';
                    });
                });
            </script>
            ";
        }
    } else {
        // Login failed
        echo "
        <script>
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
    $fname = ucwords($_POST['fname']);
    $mname = ucwords($_POST['mname']);
    $lname = ucwords($_POST['lname']);
    $gender = ucwords($_POST['gender']);
    $course = $_POST['course'];
    $fromYear = $_POST['startYear'];
    $toYear = $_POST['endYear'];
    $contact = $_POST['contact'];
    $address = ucwords($_POST['address']);
    $email = strtolower($_POST['email']);
    $password = $_POST['password'];

    // email and user existing check
    $emailCheck = mysqli_query($conn, "SELECT * FROM pending WHERE email='$email'");
    $emailCheck_decline = mysqli_query($conn, "SELECT * FROM declined_account WHERE email='$email'");
    $idCheck = mysqli_query($conn, "SELECT * FROM declined_account WHERE student_id='$stud_id'");
    $idCheck_decline = mysqli_query($conn, "SELECT * FROM declined_account WHERE student_id='$stud_id'");

    if (mysqli_num_rows($emailCheck) > 0) {
        $errorMessage = "Email Already Exists";
    } else if (mysqli_num_rows($emailCheck_decline) > 0) {
        $errorMessage = "Email Already Exists";
    } else if (mysqli_num_rows($idCheck) > 0) {
        $errorMessage = "Student ID Already Exists";
    } else if (mysqli_num_rows($idCheck_decline) > 0) {
        $errorMessage = "Student ID Already Exists";
    } else {

        // email and user existing check
        $emailCheck = mysqli_query($conn, "SELECT * FROM alumni WHERE email='$email'");
        $emailCheck_archive = mysqli_query($conn, "SELECT * FROM alumni_archive WHERE email='$email'");
        $idCheck = mysqli_query($conn, "SELECT * FROM declined_account WHERE student_id='$stud_id'");
        $idCheck_archive = mysqli_query($conn, "SELECT * FROM declined_account WHERE student_id='$stud_id'");


        if (mysqli_num_rows($emailCheck) > 0) {
            $errorMessage = "Email Already Exists";
        } else if (mysqli_num_rows($emailCheck_archive) > 0) {
            $errorMessage = "Email Already Exists";
        } else if (mysqli_num_rows($idCheck) > 0) {
            $errorMessage = "Student ID Already Exists";
        } else if (mysqli_num_rows($idCheck_archive) > 0) {
            $errorMessage = "Student ID Already Exists";
        } else {

            $filePath = '../assets/profile_icon.jpg';
            $imageData = file_get_contents($filePath);
            $imageDataEscaped = addslashes($imageData);

            $sql = "INSERT INTO pending SET student_id='$stud_id', fname='$fname', mname='$mname', lname='$lname', gender='$gender', course='$course', batch_startYear='$fromYear', batch_endYear='$toYear', contact='$contact', address='$address', email='$email', password='$password', picture='$imageDataEscaped'";
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

    <div class="container" id="container">
        <div class="form-container sign-up-container">
            <form action="#" method="POST" onsubmit="return submitForm(this);">
                <h1>Sign Up</h1>

                <div class="infield">
                    <input type="email" placeholder="Email" name="email" value="<?php echo htmlspecialchars($email); ?>" required />
                    <label></label>
                </div>
                <div class="infield">
                    <input type="text" placeholder="Password" name="password" value="<?php echo htmlspecialchars($password); ?>" required />
                    <label></label>
                </div>
                <div class="infield">
                    <input type="number" placeholder="Student ID" name="student_id" value="<?php echo htmlspecialchars($stud_id); ?>" required />
                    <label></label>
                </div>
                <div class="infield">
                    <input type="text" placeholder="First Name" name="fname" value="<?php echo htmlspecialchars($fname); ?>" required />
                    <label></label>
                </div>
                <div class="infield">
                    <input type="text" placeholder="Middle Name" name="mname" value="<?php echo htmlspecialchars($mname); ?>" />
                    <label></label>
                </div>
                <div class="infield">
                    <input type="text" placeholder="Last Name" name="lname" value="<?php echo htmlspecialchars($lname); ?>" required />
                    <label></label>
                </div>
                <div class="infield">
                    <select name="gender" id="gender" required>
                        <option value="" selected hidden disabled>Select a Gender</option>
                        <option value="Male" <?php echo ($gender == 'Male') ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo ($gender == 'Female') ? 'selected' : ''; ?>>Female</option>
                    </select>
                </div>
                <div class="infield">
                    <select class="form-control" name="course" id="course" required>
                        <option value="" selected hidden disabled>Select a course</option>
                        <option value="BAJ" <?php echo ($course == 'BAJ') ? 'selected' : ''; ?>>BAJ</option>
                        <option value="BECEd" <?php echo ($course == 'BECEd') ? 'selected' : ''; ?>>BECEd</option>
                        <option value="BEEd" <?php echo ($course == 'BEEd') ? 'selected' : ''; ?>>BEEd</option>
                        <option value="BSBM" <?php echo ($course == 'BSBM') ? 'selected' : ''; ?>>BSBM</option>
                        <option value="BSOA" <?php echo ($course == 'BSOA') ? 'selected' : ''; ?>>BSOA</option>
                        <option value="BSEntrep" <?php echo ($course == 'BSEntrep') ? 'selected' : ''; ?>>BSEntrep</option>
                        <option value="BSHM" <?php echo ($course == 'BSHM') ? 'selected' : ''; ?>>BSHM</option>
                        <option value="BSIT" <?php echo ($course == 'BSIT') ? 'selected' : ''; ?>>BSIT</option>
                        <option value="BSCS" <?php echo ($course == 'BSCS') ? 'selected' : ''; ?>>BSCS</option>
                        <option value="BSc(Psych)" <?php echo ($course == 'BSc(Psych)') ? 'selected' : ''; ?>>BSc(Psych)</option>
                    </select>
                </div>
                <div class="infield">
                    <select class="form-control" name="startYear" id="startYear" required>
                        <option value="" selected hidden disabled>Batch: From Year</option>
                        <?php
                        // Get the current year
                        $currentYear = date('Y');

                        // Number of years to include before and after the current year
                        $yearRange = 21; // Adjust this number as needed

                        // Preserve the selected value after form submission
                        $selectedYear = isset($_POST['startYear']) ? $_POST['startYear'] : '';

                        // Generate options for years, from current year minus $yearRange to current year plus $yearRange
                        for ($year = $currentYear - $yearRange; $year <= $currentYear + $yearRange; $year++) {
                            $selected = ($year == $selectedYear) ? 'selected' : '';
                            echo "<option value=\"$year\" $selected>$year</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="infield">
                    <select class="form-control" name="endYear" id="endYear" required data-selected="<?php echo isset($_POST['endYear']) ? $_POST['endYear'] : ''; ?>">
                        <option value="" selected hidden disabled>Batch: To Year</option>
                        <?php
                        if (isset($_POST['startYear'])) {
                            $startYear = $_POST['startYear'];
                            $selectedEndYear = isset($_POST['endYear']) ? $_POST['endYear'] : '';

                            // Generate options for endYear starting from startYear + 1
                            for ($year = $startYear + 1; $year <= $currentYear + $yearRange; $year++) {
                                $selected = ($year == $selectedEndYear) ? 'selected' : '';
                                echo "<option value=\"$year\" $selected>$year</option>";
                            }
                        }
                        ?>
                    </select>
                </div>
                <div class="infield">
                    <input type="number" placeholder="Contact" name="contact" value="<?php echo htmlspecialchars($contact); ?>" required />
                    <label></label>
                </div>
                <div class="infield">
                    <input type="text" placeholder="Address" name="address" value="<?php echo htmlspecialchars($address); ?>" required />
                    <label></label>
                </div>
                <button type="submit" name="submit">Sign Up</button>
            </form>
        </div>
        <div class="form-container log-in-container">
            <form action="#" method="POST">
                <h1>Log in</h1>
                <div class="infield">
                    <input type="text" placeholder="Student ID / Email" name="log_email" required />
                    <label></label>
                </div>
                <div class="infield">
                    <input type="password" placeholder="Password" name="log_password" required />
                    <label></label>
                </div>
                <!-- <a href="#" class="forgot">Forgot your password?</a> -->
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
    <!-- js code -->
    <!-- <script>
        const signUpButton = document.getElementById('signUp');
        const logInButton = document.getElementById('logIn');
        const container = document.getElementById('container');

        signUpButton.addEventListener('click', () => {
            container.classList.add('right-panel-active');
        });

        logInButton.addEventListener('click', () => {
            container.classList.remove('right-panel-active');
        });
    </script> -->

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
    </script>
    <script>
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
    </script>
</body>

</html>