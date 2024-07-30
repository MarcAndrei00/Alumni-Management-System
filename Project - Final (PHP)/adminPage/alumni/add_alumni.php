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

    // Check if user is an alumni
    $stmt = $conn->prepare("SELECT * FROM alumni WHERE alumni_id = ? AND email = ?");
    $stmt->bind_param("ss", $account, $account_email);
    $stmt->execute();
    $user_result = $stmt->get_result();

    if ($user_result->num_rows > 0) {
        // User is an alumni
        header('Location: ../../alumniPage/dashboard_user.php');
        exit();
    }
    $stmt->close();
} else {
    header('Location: ../../homepage.php');
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
$temp_password = "";

// get the data from form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $stud_id = $_POST['student_id'];
    $fname = ucwords($_POST['fname']);
    $mname = ucwords($_POST['mname']);
    $lname = ucwords($_POST['lname']);
    $gender = $_POST['gender'];
    $course = $_POST['course'];
    $fromYear = $_POST['startYear'];
    $toYear = $_POST['endYear'];
    $contact = $_POST['contact'];
    $address = ucwords($_POST['address']);
    $email = strtolower($_POST['email']);
    $temp_password = $_POST['temp_pass'];


    // email and user existing check
    $emailCheck = mysqli_query($conn, "SELECT * FROM alumni WHERE email='$email'");
    $emailCheck_decline = mysqli_query($conn, "SELECT * FROM declined_account WHERE email='$email'");
    $idCheck = mysqli_query($conn, "SELECT * FROM alumni WHERE student_id='$stud_id'");
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
        $emailCheck = mysqli_query($conn, "SELECT * FROM pending WHERE email='$email'");
        $emailCheck_archive = mysqli_query($conn, "SELECT * FROM alumni_archive WHERE email='$email'");
        $idCheck = mysqli_query($conn, "SELECT * FROM pending WHERE student_id='$stud_id'");
        $idCheck_archive = mysqli_query($conn, "SELECT * FROM alumni_archive WHERE student_id='$stud_id'");


        if (mysqli_num_rows($emailCheck) > 0) {
            $errorMessage = "Email Already Exists";
        } else if (mysqli_num_rows($emailCheck_archive) > 0) {
            $errorMessage = "Email Already Exists";
        } else if (mysqli_num_rows($idCheck) > 0) {
            $errorMessage = "Student ID Already Exists";
        } else if (mysqli_num_rows($idCheck_archive) > 0) {
            $errorMessage = "Student ID Already Exists";
        } else {
            $filePath = '../../assets/profile_icon.jpg';
            $imageData = file_get_contents($filePath);
            $imageDataEscaped = addslashes($imageData);

            $sql = "INSERT INTO alumni SET student_id='$stud_id', fname='$fname', mname='$mname', lname='$lname', gender='$gender', course='$course', batch_startYear='$fromYear', batch_endYear='$toYear', contact='$contact', address='$address', email='$email', password='$temp_password', picture='$imageDataEscaped'";
            $result = $conn->query($sql);
            echo "
            <script>
                // Wait for the document to load
                document.addEventListener('DOMContentLoaded', function() {
                    // Use SweetAlert2 for the alert
                    Swal.fire({
                            title: 'Alumni Added Successfully',
                            timer: 2000,
                            showConfirmButton: true, // Show the confirm button
                            confirmButtonColor: '#4CAF50', // Set the button color to green
                            confirmButtonText: 'OK' // Change the button text if needed
                    }).then(function() {
                        // Redirect after the alert closes
                        window.location.href = './alumni.php';
                    });
                });
            </script>
            ";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
    <title>Add New Alumni</title>
    <link rel="shortcut icon" href="../../assets/cvsu.png" type="image/svg+xml">
    <link rel="stylesheet" href="./css/add_alumni.css">
    <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
    <link rel="stylesheet" href="	https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="	https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
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
                        <a href="../profile/profile.php">
                            <span class="las la-user-alt" style="color:#fff"></span>
                            <small>PROFILE</small>
                        </a>
                    </li>
                    <li>
                        <a href="./add_alumni.php" class="active">
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
                <h1><strong>Alumni</strong></h1>
            </div>
        </main>
        <div class="container" id="container-full">
            <div class="container" id="content-container">
                <div class="container-title">
                    <span>Add New Account</span>
                </div>

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

                <div class="container" id="content">
                    <form action="" method="POST">
                        <div class="container">
                            <div class="row align-items-end">
                                <div class="col">
                                    <label class="col-sm-3 col-form-label" style="font-size: 20px;" for="Student ID">Student ID:</label>
                                </div>

                                <div class="col">
                                    <input class="form-control" type="number" id="name" name="student_id" placeholder="Student ID" required value="<?php echo $stud_id; ?>">
                                </div>
                            </div>
                        </div>
                        <div class="container">
                            <div class="row align-items-end">
                                <div class="col">
                                    <label class="col-sm-3 col-form-label" style="font-size: 20px;" for="first-name">First Name:</label>
                                </div>

                                <div class="col">
                                    <input class="form-control" type="text" id="name" name="fname" placeholder="First Name" required value="<?php echo $fname; ?>">
                                </div>
                            </div>
                        </div>
                        <div class="container">
                            <div class="row align-items-end">
                                <div class="col">
                                    <label class="col-sm-3 col-form-label" style="font-size: 20px;" for="middle-name">Middle Name:</label>
                                </div>

                                <div class="col">
                                    <input class="form-control" type="text" id="name" name="mname" placeholder="Middle Name" value="<?php echo $mname; ?>">
                                </div>
                            </div>
                        </div>
                        <div class="container">
                            <div class="row align-items-end">
                                <div class="col">
                                    <label class="col-sm-3 col-form-label" style="font-size: 20px;" for="last-name">Last Name:</label>
                                </div>
                                <div class="col">
                                    <input class="form-control" type="text" id="name" name="lname" placeholder="Last Name" required value="<?php echo $lname; ?>">
                                </div>
                            </div>
                        </div>
                        <div class="container">
                            <div class="row align-items-end">
                                <div class="col">
                                    <label class="col-sm-3 col-form-label" style="font-size: 20px;" for="name">Gender:</label>
                                </div>
                                <div class="col">
                                    <select class="form-control" name="gender" id="gender" required>
                                        <option value="" selected hidden disabled>Select a Gender</option>
                                        <option value="Male" <?php echo ($gender == 'Male') ? 'selected' : ''; ?>>Male</option>
                                        <option value="Female" <?php echo ($gender == 'Female') ? 'selected' : ''; ?>>Female</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="container">
                            <div class="row align-items-end">
                                <div class="col">
                                    <label class="col-sm-3 col-form-label" style="font-size: 20px;" for="name">Course:</label>
                                </div>
                                <div class="col">
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
                            </div>
                        </div>

                        <div class="container">
                            <div class="row align-items-end">
                                <div class="col" id="calendar">
                                    <label class="col-sm-3 col-form-label" style="font-size: 20px;" for="phone"><span>Batch:</span></label>
                                </div>

                                <div class="col" id="batch">
                                    <?php
                                    $fromYear = isset($_POST['startYear']) ? $_POST['startYear'] : '';
                                    $toYear = isset($_POST['endYear']) ? $_POST['endYear'] : '';
                                    ?>

                                    <select class="form-control" name="startYear" id="startYear" required>
                                        <option value="" selected hidden disabled>Batch: From Year</option>
                                        <?php
                                        // Get the current year
                                        $currentYear = date('Y');

                                        // Number of years to include before and after the current year
                                        $yearRange = 21; // Adjust this number as needed

                                        // Generate options for years, from current year minus $yearRange to current year plus $yearRange
                                        for ($year = $currentYear - $yearRange; $year <= $currentYear + $yearRange; $year++) {
                                            $selected = ($year == $fromYear) ? 'selected' : '';
                                            echo "<option value=\"$year\" $selected>$year</option>";
                                        }
                                        ?>
                                    </select>

                                    <select class="form-control" name="endYear" id="endYear" required>
                                        <option value="" selected hidden disabled>Batch: To Year</option>
                                        <?php
                                        if ($fromYear) {
                                            // Generate options for endYear starting from startYear + 1
                                            for ($year = $fromYear + 1; $year <= $currentYear + $yearRange; $year++) {
                                                $selected = ($year == $toYear) ? 'selected' : '';
                                                echo "<option value=\"$year\" $selected>$year</option>";
                                            }
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="container">
                            <div class="row align-items-end">
                                <div class="col">
                                    <label class="col-sm-3 col-form-label" style="font-size: 20px;" for="name">Contact:</label>
                                </div>
                                <div class="col">
                                    <input class="form-control" type="number" id="name" name="contact" placeholder="Enter Phone No." required value="<?php echo $contact; ?>">
                                </div>
                            </div>
                        </div>
                        <div class="container">
                            <div class="row align-items-end">
                                <div class="col">
                                    <label class="col-sm-3 col-form-label" style="font-size: 20px;" for="name">Address:</label>
                                </div>
                                <div class="col">
                                    <input class="form-control" type="text" id="name" name="address" placeholder="Enter Address" required value="<?php echo $address; ?>">
                                </div>
                            </div>
                        </div>
                        <div class="container">
                            <div class="row align-items-end">
                                <div class="col">
                                    <label class="col-sm-3 col-form-label" style="font-size: 20px;" for="name">Email:</label>
                                </div>
                                <div class="col">
                                    <input class="form-control" type="email" id="email" name="email" placeholder="Enter Email" required value="<?php echo $email; ?>">
                                </div>
                            </div>
                        </div>
                        <div class="container">
                            <div class="row align-items-end">
                                <div class="col">
                                    <label class="col-sm-3 col-form-label" style="font-size: 20px;" for="username">Temporary Password:</label>
                                </div>
                                <div class="col">
                                    <input class="form-control" type="text" id="temp_pass" name="temp_pass" placeholder="Enter Password" required value="<?php echo $temp_password; ?>">
                                </div>
                            </div>
                        </div>
                        <div class="container">
                            <div class="row" style="margin-top:20px;">
                                <div class="col" id="buttons">
                                    <div class="button">
                                        <button type="submit" class="btn btn-warning" name="submit" id="insert">Add new</button>
                                        <a class="btn btn-danger" href="./alumni.php">Cancel</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- SCRIPT FOR DATCH SELECTOR -->
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
    </script>
</body>

</html>