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
        $user = $user_result->fetch_assoc();
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
// Close the database connection if needed
// $conn->close();

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


if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Show the data of alumni
    if (!isset($_GET['id'])) {
        if (isset($_GET['ide'])) {
            echo "
                <script>
                // Wait for the document to load
                document.addEventListener('DOMContentLoaded', function() {
                    // Use SweetAlert2 for the alert
                    Swal.fire({
                        title: 'Profile Updated Successfully',
                        timer: 2000,
                        showConfirmButton: true, // Show the confirm button
                        confirmButtonColor: '#4CAF50', // Set the button color to green
                        confirmButtonText: 'OK' // Change the button text if needed
                    });
                });
            </script>
            ";
            $alumni_id = $_GET['ide'];

            //read data from table alumni
            $sql = "SELECT * FROM alumni WHERE alumni_id=$alumni_id";
            $result = $conn->query($sql);
            $row = $result->fetch_assoc();

            if (!$row) {
                header("location: ./alumni.php");
                exit;
            }
            // data from table alumni where student_id = $alumni_id = $_GET['id']; get from alumni list update

            $stud_id = $row['student_id'];
            $fname = $row['fname'];
            $mname = $row['mname'];
            $lname = $row['lname'];
            $gender = $row['gender'];
            $course = $row['course'];
            $contact = $row['contact'];
            $address = $row['address'];
            $email = $row['email'];
            $file = $row['picture'];

            $batch = $row["batch_startYear"] . " - " . $row["batch_endYear"];
        } else {
            header("location: ./alumni.php");
            exit;
        }
    } else {
        $alumni_id = $_GET['id'];

        //read data from table alumni
        $sql = "SELECT * FROM alumni WHERE alumni_id=$alumni_id";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();

        if (!$row) {
            header("location: ./alumni.php");
            exit;
        }
        // data from table alumni where student_id = $alumni_id = $_GET['id']; get from alumni list update

        $stud_id = $row['student_id'];
        $fname = $row['fname'];
        $mname = $row['mname'];
        $lname = $row['lname'];
        $gender = $row['gender'];
        $course = $row['course'];
        $contact = $row['contact'];
        $address = $row['address'];
        $email = $row['email'];
        $file = $row['picture'];

        $batch = $row["batch_startYear"] . " - " . $row["batch_endYear"];
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
    <title>Alumni Info</title>
    <link rel="shortcut icon" href="../../assets/cvsu.png" type="image/svg+xml">
    <link rel="stylesheet" href="./css/alumni_info.css">
    <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
    <link rel="stylesheet" href="	https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="	https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
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
                        <a href="../dashboard_coor.php">
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
                        <a href="./alumni_info.php" class="active">
                            <span class="las la-th-list" style="color:#fff"></span>
                            <small>ALUMNI</small>
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
                    <span>Information</span>
                </div>
                <div class="container" id="content">
                    <!-- PROFILE -->
                    <div class="container text-center" id="start">
                        <div class="row align-items-end">
                            <div class="col">
                                <?php echo "
                                    <a class='btn btn-warning' href='./update_profile.php?id=$row[alumni_id]'> Change Picture </a>
                                    ";
                                ?>
                            </div>
                            <div class="col">
                                <!-- Preview image -->
                                <div class="form-control" style="width:225px;height:215px; border-radius: 100%;">
                                    <img id="preview" src="data:image/jpeg;base64,<?php echo base64_encode($row['picture']); ?>" style="width:200px;height:200px; border-radius: 100%;">
                                </div>
                            </div>
                        </div>
                    </div>
                    <br>
                    <div class="container">
                        <div class="row align-items-end">
                            <div class="col">
                                <input type="hidden" name="id" value="<?php echo $alumni_id; ?>">
                                <label class="col-sm-3 col-form-label" style="font-size: 20px;" for="first-name">Student ID:</label>
                            </div>

                            <div class="col">
                                <input class="form-control" type="number" id="name" name="student_id" disabled value="<?php echo $stud_id; ?>">
                            </div>
                        </div>
                    </div>
                    <div class="container">
                        <div class="row align-items-end">

                            <div class="col">
                                <label class="col-sm-3 col-form-label" style="font-size: 20px;" for="first-name">First Name:</label>
                            </div>

                            <div class="col">
                                <input class="form-control" type="text" id="name" name="fname" disabled value="<?php echo $fname; ?>">
                            </div>
                        </div>
                    </div>
                    <div class="container">
                        <div class="row align-items-end">
                            <div class="col">
                                <label class="col-sm-3 col-form-label" style="font-size: 20px;" for="middle-name">Middle Name:</label>
                            </div>

                            <div class="col">
                                <input class="form-control" type="text" id="name" name="mname" disabled value="<?php echo $mname; ?>">
                            </div>
                        </div>
                    </div>
                    <div class="container">
                        <div class="row align-items-end">
                            <div class="col">
                                <label class="col-sm-3 col-form-label" style="font-size: 20px;" for="last-name">Last Name:</label>
                            </div>
                            <div class="col">
                                <input class="form-control" type="text" id="name" name="lname" disabled value="<?php echo $lname; ?>">
                            </div>
                        </div>
                    </div>
                    <div class="container">
                        <div class="row align-items-end">
                            <div class="col">
                                <label class="col-sm-3 col-form-label" style="font-size: 20px;" for="name">Gender:</label>
                            </div>
                            <div class="col">
                                <input class="form-control" type="text" id="name" name="gender" disabled value="<?php echo $gender; ?>">
                            </div>
                        </div>
                    </div>
                    <div class="container">
                        <div class="row align-items-end">
                            <div class="col">
                                <label class="col-sm-3 col-form-label" style="font-size: 20px;" for="name">Course:</label>
                            </div>
                            <div class="col">
                                <input class="form-control" type="text" id="name" name="course" disabled value="<?php echo $course; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="container">
                        <div class="row align-items-end">
                            <div class="col" id="calendar">
                                <label class="col-sm-3 col-form-label" style="font-size: 20px;" for="phone"><span>Batch:</span></label>
                            </div>

                            <div class="col" id="batch">
                                <input class="form-control" type="text" id="name" name="batch" disabled value="<?php echo htmlspecialchars($batch) ?>">
                            </div>
                        </div>
                    </div>
                    <div class="container">
                        <div class="row align-items-end">
                            <div class="col">
                                <label class="col-sm-3 col-form-label" style="font-size: 20px;" for="name">Contact:</label>
                            </div>
                            <div class="col">
                                <input class="form-control" type="number" id="name" name="contact" disabled value="<?php echo $contact; ?>">
                            </div>
                        </div>
                    </div>
                    <div class="container">
                        <div class="row align-items-end">
                            <div class="col">
                                <label class="col-sm-3 col-form-label" style="font-size: 20px;" for="address">Address:</label>
                            </div>
                            <div class="col">
                                <input class="form-control" type="text" id="username" name="address" disabled value="<?php echo $address; ?>">
                            </div>
                        </div>
                    </div>
                    <div class="container">
                        <div class="row align-items-end">
                            <div class="col">
                                <label class="col-sm-3 col-form-label" style="font-size: 20px;" for="name">Email:</label>
                            </div>
                            <div class="col">
                                <input class="form-control" type="email" id="email" name="email" disabled value="<?php echo $email; ?>">
                            </div>
                        </div>
                    </div>
                    <div class="container">
                        <div class="row" style="margin-top:20px;">
                            <div class="col" id="buttons">
                                <div class="button">

                                    <?php 
                                    echo "
                                        <a class='btn btn-warning' href='./update_info.php?id=$row[alumni_id]'>Update Info</a>
                                        <a class='btn btn-danger' href='./del_alumni.php?id=$row[alumni_id]'>Archive</a>
                                        <a class='btn btn-danger' href='./alumni.php'>Cancel</a>
                                    ";?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Script to display preview of selected image -->
    <script>
        function getImagePreview(event) {
            var image = URL.createObjectURL(event.target.files[0]);
            var preview = document.getElementById('preview');
            preview.src = image;
            preview.style.width = '200px';
            preview.style.height = '200px';
        }
    </script>
</body>

</html>