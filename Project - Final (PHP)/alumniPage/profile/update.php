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
        $user = $user_result->fetch_assoc();
    }
    $stmt->close();
} else {
    header('Location: ../../homepage.php');
    exit();
}

// FOR PROFILE IMAGE
//read data from table alumni
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

$sql = "SELECT * FROM alumni WHERE alumni_id=$account";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

$file = $row['picture'];


if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Show the data of alumni
    if (!isset($_GET['id'])) {
        header("location: ./profile.php");
        exit;
    }
    $alumni_id = $_GET['id'];

    //read data from table alumni
    $sql = "SELECT * FROM alumni WHERE alumni_id=$alumni_id";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();

    if (!$row) {
        header("location: ./profile.php");
        exit;
    }

    // data from table alumni where student_id = $alumni_id = $_GET['id']; get from alumni list update
    // data from table alumni where student_id = $alumni_id = $_GET['id']; get from alumni list update
    $alumni_id = $row['alumni_id'];
    $stud_id = $row['student_id'];
    $fname = $row['fname'];
    $mname = $row['mname'];
    $lname = $row['lname'];
    $gender = $row['gender'];
    $course = $row['course'];
    $fromYear = $row['batch_startYear'];
    $toYear = $row['batch_endYear'];
    $contact = $row['contact'];
    $address = $row['address'];
    $email = $row['email'];
} else {
    // get the data from form
    $alumni_id = $_POST['alumni_id'];
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

    // email and user existing check
    $emailCheck = mysqli_query($conn, "SELECT * FROM pending WHERE email='$email' AND alumni_id != '$alumni_id'");
    $emailCheck_decline = mysqli_query($conn, "SELECT * FROM declined_account WHERE email='$email' AND alumni_id != '$alumni_id'");

    if (mysqli_num_rows($emailCheck) > 0) {
        $errorMessage = "Email Already Exists";
    } else if (mysqli_num_rows($emailCheck_decline) > 0) {
        $errorMessage = "Email Already Exists";
    } else {

        // email and user existing check
        $emailCheck = mysqli_query($conn, "SELECT * FROM alumni WHERE email='$email' AND alumni_id != '$alumni_id'");
        $emailCheck_archive = mysqli_query($conn, "SELECT * FROM alumni_archive WHERE email='$email' AND alumni_id != '$alumni_id'");


        if (mysqli_num_rows($emailCheck) > 0) {
            $errorMessage = "Email Already Exists";
        } else if (mysqli_num_rows($emailCheck_archive) > 0) {
            $errorMessage = "Email Already Exists";
        } else {

            $sql = "UPDATE alumni SET fname='$fname', mname='$mname', lname='$lname', gender='$gender', course='$course', batch_startYear='$fromYear', batch_endYear='$toYear', contact='$contact', address='$address', email='$email' WHERE alumni_id=$alumni_id";
            $result = $conn->query($sql);
            echo "
            <script>
                // Wait for the document to load
                document.addEventListener('DOMContentLoaded', function() {
                    // Use SweetAlert2 for the alert
                    Swal.fire({
                            title: 'Info Updated Successfully',
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
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
    <title>Update alumni Info</title>
    <link rel="shortcut icon" href="../../assets/cvsu.png" type="image/svg+xml">
    <link rel="stylesheet" href="css/update_info.css">
    <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="	https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
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
                <div>
                    <img id="preview" src="data:image/jpeg;base64,<?php echo base64_encode($row['picture']); ?>" style="width:83px;height:83px; border-radius: 100%;border: 2px solid white;">
                </div>
                <h4><?php echo $user['fname']; ?></h4>
                <small style="color: white;"><?php echo $user['email']; ?></small>
            </div>

            <div class="side-menu">
                <ul>
                    <li>
                        <a href="../dashboard_user.php">
                            <span class="las la-home" style="color:#fff"></span>
                            <small>DASHBOARD</small>
                        </a>
                    </li>
                    <li>
                        <a href="./update.php" class="active">
                            <span class="las la-user-alt" style="color:#fff"></span>
                            <small>PROFILE</small>
                        </a>
                    </li>
                    <li>
                        <a href="../event/event.php">
                            <span class="las la-calendar" style="color:#fff"></span>
                            <small>EVENT</small>
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

            <div class="page-content">
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
                            <div class="information">
                                <form action="update.php" method="POST" onsubmit="return submitForm(this);">
                                    <div class="mb-3">
                                        <input type="hidden" name="alumni_id" class="form-control" id="formGroupExampleInput" value="<?php echo $alumni_id; ?>">
                                        <label for="formGroupExampleInput" class="form-label">FIRST NAME</label>
                                        <input type="text" name="fname" class="form-control" id="formGroupExampleInput" required value="<?php echo htmlspecialchars("$fname"); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="formGroupExampleInput" class="form-label">MIDDLE NAME</label>
                                        <input type="text" name="mname" class="form-control" id="formGroupExampleInput" value="<?php echo htmlspecialchars("$mname"); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="formGroupExampleInput" class="form-label">LAST NAME</label>
                                        <input type="text" name="lname" class="form-control" id="formGroupExampleInput" required value="<?php echo htmlspecialchars("$lname"); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="formGroupExampleInput" class="form-label">GENDER</label>
                                        <select class="form-control" name="gender" id="gender" required>
                                            <option value="<?php echo $gender; ?>" selected> <?php echo $gender; ?> </option>
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="formGroupExampleInput" class="form-label">COURSE</label>
                                        <select class="form-control" name="course" id="course" required>
                                            <option value="<?php echo $course; ?>" selected><?php echo $course; ?></option>
                                            <option value="BAJ">BAJ</option>
                                            <option value="BECEd">BECEd</option>
                                            <option value="BEEd">BEEd</option>
                                            <option value="BSBM">BSBM</option>
                                            <option value="BSOA">BSOA</option>
                                            <option value="BSEntrep">BSEntrep</option>
                                            <option value="BSHM">BSHM</option>
                                            <option value="BSIT">BSIT</option>
                                            <option value="BSCS">BSCS</option>
                                            <option value="BSc(Psych)">BSc(Psych)</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="formGroupExampleInput" class="form-label">BATCH</label>
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

                                        <select class="form-control" name="endYear" id="endYear" required disabled>
                                            <option value="" selected hidden disabled>Batch: To Year</option>
                                            <?php
                                            if (isset($fromYear)) {
                                                // Generate options for endYear starting from startYear + 1
                                                for ($year = $fromYear + 1; $year <= $currentYear + $yearRange; $year++) {
                                                    $selected = ($year == $toYear) ? 'selected' : '';
                                                    echo "<option value=\"$year\" $selected>$year</option>";
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="formGroupExampleInput" class="form-label">CONTACT</label>
                                        <input type="number" name="contact" class="form-control" id="formGroupExampleInput" value="<?php echo htmlspecialchars("$contact"); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="formGroupExampleInput" class="form-label">ADDRESS</label>
                                        <input type="text" name="address" class="form-control" id="formGroupExampleInput" value="<?php echo htmlspecialchars("$address"); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="formGroupExampleInput" class="form-label">EMAIL</label>
                                        <input type="text" name="email" class="form-control" id="formGroupExampleInput" value="<?php echo htmlspecialchars("$email"); ?>">
                                    </div>
                                    <div class="buttons">
                                        <button type="submit" class="btn" id="button1" value="Update">UPDATE</button>
                                        <a href="./profile.php"><button type="button" class="btn" id="button1">CANCEL</button></a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- <script>
    let profilePic = document.getElementById("profile-pic");
    let inputFile = document.getElementById("input-file");

    inputFile.onchange = function(){
        profilePic.src = URL.createObjectURL(inputFile.files[0]);
    }
</script> -->

            <!-- Script to display preview of selected image -->
            <script>
                function getImagePreview(event) {
                    var image = URL.createObjectURL(event.target.files[0]);
                    var preview = document.getElementById('preview');
                    preview.src = image;
                    preview.style.width = '83px';
                    preview.style.height = '83px';
                }
            </script>
            <!-- FOR BATCH SELECTOR UPDATE -->
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    var startYearSelect = document.getElementById('startYear');
                    var endYearSelect = document.getElementById('endYear');

                    // Enable endYear if startYear is already selected from the database
                    if (startYearSelect.value) {
                        populateEndYearOptions(parseInt(startYearSelect.value));
                    }

                    startYearSelect.addEventListener('change', function() {
                        var startYear = parseInt(this.value);
                        populateEndYearOptions(startYear);
                    });

                    function populateEndYearOptions(startYear) {
                        var currentYear = new Date().getFullYear();
                        var yearRange = 21; // Adjust this number as needed

                        // Clear all existing options and reset endYear
                        endYearSelect.innerHTML = '<option value="" selected hidden disabled>Batch: To Year</option>';

                        // Enable endYear dropdown
                        endYearSelect.disabled = false;

                        // Generate new options for endYear based on selected startYear
                        for (var year = startYear + 1; year <= currentYear + yearRange; year++) {
                            var option = document.createElement('option');
                            option.value = year;
                            option.text = year;
                            endYearSelect.appendChild(option);
                        }

                        // If endYear is already selected from the database, reselect it
                        if (<?php echo isset($toYear) ? 'true' : 'false'; ?>) {
                            endYearSelect.value = <?php echo isset($toYear) ? $toYear : '""'; ?>;
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