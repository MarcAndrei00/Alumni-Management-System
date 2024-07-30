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
    
} else {
    header('Location: ../homepage.php');
    exit();
}

// alumni count
$sql_alumni = "SELECT COUNT(student_id) AS alumni_count FROM alumni";
$result_alumni = $conn->query($sql_alumni);
$row_alumni = $result_alumni->fetch_assoc();
$count_alumni = $row_alumni['alumni_count'];

// PENDING ACCOUNT
$sql_pending = "SELECT COUNT(student_id) AS alumni_pending_count FROM pending";
$result_pending = $conn->query($sql_pending);
$row_pending = $result_pending->fetch_assoc();
$count_pending = $row_pending['alumni_pending_count'];

// COORDINATOR count
$sql_coordinator = "SELECT COUNT(coor_id) AS coordinators_count FROM coordinator";
$result_coordinator = $conn->query($sql_coordinator);
$row_coordinator = $result_coordinator->fetch_assoc();
$coordinator_count = $row_coordinator['coordinators_count'];

// events count
$sql_event = "SELECT COUNT(event_id) AS events_count FROM event";
$result_event = $conn->query($sql_event);
$row_event = $result_event->fetch_assoc();
$event_count = $row_event['events_count'];

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
    <title>Admin Dashboard</title>
    <link rel="shortcut icon" href="../assets/cvsu.png" type="image/svg+xml">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
                        <a href="./dashboard_admin.php" class="active">
                            <span class="las la-home" style="color:#fff"></span>
                            <small>DASHBOARD</small>
                        </a>
                    </li>
                    <li>
                        <a href="./profile/profile.php">
                            <span class="las la-user-alt" style="color:#fff"></span>
                            <small>PROFILE</small>
                        </a>
                    </li>
                    <li>
                        <a href="./alumni/alumni.php">
                            <span class="las la-th-list" style="color:#fff"></span>
                            <small>ALUMNI</small>
                        </a>
                    </li>
                    <li>
                        <a href="./coordinator/coordinator.php">
                            <span class="las la-user-cog" style="color:#fff"></span>
                            <small>COORDINATOR</small>
                        </a>
                    </li>
                    <li>
                        <a href="./event/event.php">
                            <span class="las la-calendar" style="color:#fff"></span>
                            <small>EVENT</small>
                        </a>
                    </li>
                    <li>
                        <a href="./settings/about.php">
                            <span class="las la-cog" style="color:#fff"></span>
                            <small>SETTINGS</small>
                        </a>
                    </li>
                    <li>
                        <a href="./report/report.php">
                            <span class="las la-clipboard-check" style="color:#fff"></span>
                            <small>REPORT</small>
                        </a>
                    </li>
                    <li>
                        <a href="./archive/alumni_archive.php">
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


                        <a href="./logout.php">
                            <span class="las la-power-off" style="font-size: 30px; border-left: 1px solid #fff; padding-left:10px; color:#fff"></span>
                        </a>

                    </div>
                </div>
            </div>
        </header>


        <main>
            <div class="container-fluid" id="main-container">
                <div class="page-header">
                    <h1><strong>Dashboard</strong></h1>
                </div>

                <div class="page-content">
                    <!-- Dashboard Items Start -->
                    <div class="container-fluid">
                        <div class="container-fluid" id="content-container">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="card bg-primary text-white mb-4">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <i class="las la-user-graduate fa-3x" style="font-size: 25px;"></i>
                                                <div class="row mb-3">
                                                    <!-- display Alumni Total Count -->
                                                    <label style="font-size: 20px;">Alumni Total Count:</label>
                                                    <!-- display alumni count in database -->
                                                    <label class="col-sm-10 col-form-label" style="font-size: 40px;"><?php echo $count_alumni; ?></label>
                                                </div>
                                            </div>
                                        </div>
                                        <a href="alumni/alumni.php" class="card-footer d-flex justify-content-between text-white" style="text-decoration: none;">
                                            <span>View Details</span>
                                            <i class="las la-arrow-circle-right" style="font-size: 20px;"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-danger text-white mb-4">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <i class="las la-user-graduate fa-3x" style="font-size: 25px;"></i>
                                                <div class="row mb-3">
                                                    <!-- display Alumni Total Count -->
                                                    <label style="font-size: 20px;">Pending Alumni Account:</label>
                                                    <!-- display alumni count in database -->
                                                    <label class="col-sm-10 col-form-label" style="font-size: 40px;"><?php echo $count_pending; ?></label>
                                                </div>
                                            </div>
                                        </div>
                                        <a href="./alumni/pendingAccount/pending.php" class="card-footer d-flex justify-content-between text-white" style="text-decoration: none;">
                                            <span>View Details</span>
                                            <i class="las la-arrow-circle-right"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-success text-white mb-4">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <i class="las la-user-plus fa-3x" style="font-size: 25px;"></i>
                                                <div class="row mb-3">
                                                    <!-- Display coordinator Count -->
                                                    <label style="font-size: 20px;">Coordinators Total Count:</label>
                                                    <!-- display coordinators count in database -->
                                                    <label class="col-sm-10 col-form-label" style="font-size: 40px;"><?php echo $coordinator_count; ?></label>
                                                </div>
                                            </div>
                                        </div>
                                        <a href="coordinator/coordinator.php" class="card-footer d-flex justify-content-between text-white" style="text-decoration: none;">
                                            <span>View Details</span>
                                            <i class="las la-arrow-circle-right"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="card bg-warning text-white mb-4">
                                        <div class="card-body">
                                            <div class="d-flex align-items-center">
                                                <i class="las la-calendar-alt fa-3x" style="font-size: 25px;"></i>
                                                <div class="row mb-3">
                                                    <!-- Display Student Total Count -->
                                                    <label style="font-size: 20px;">Events Total Count:</label>
                                                    <!-- display events count in database -->
                                                    <label class="col-sm-10 col-form-label" style="font-size: 40px;"><?php echo $event_count; ?></label>
                                                </div>
                                            </div>
                                        </div>
                                        <a href="event/event.php" class="card-footer d-flex justify-content-between text-white" style="text-decoration: none;">
                                            <span>View Details</span>
                                            <i class="las la-arrow-circle-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> <!-- Dashboard Items End -->
            </div>

        </main>
    </div>
</body>

</html>