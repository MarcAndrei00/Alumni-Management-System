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
        $user = $user_result->fetch_assoc();
    }
    $stmt->close();
    
} else {
    header('Location: ../homepage.php');
    exit();
}




//read data from table alumni
$sql = "SELECT * FROM alumni WHERE alumni_id=$account";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

$file = $row['picture'];

//query for events count
$sql_event = "SELECT COUNT(event_id) AS events_count FROM event";

// connect in databse then run the query $sql
$result_event = $conn->query($sql_event);
// retrieve the data from database
$row_event = $result_event->fetch_assoc();
// get the exact query or in short COUNT(event_id) from table event,  COUNT(event_id) rename as events_count
$event_count = $row_event['events_count'];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
    <title>Alumni Dashboard</title>
    <link rel="shortcut icon" href="../assets/cvsu.png" type="image/svg+xml">
    <link rel="stylesheet" href="./dashboard_user.css">
    <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
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
                        <a href="./dashboard_user.php" class="active">
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
                        <a href="./event/event.php">
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
                <!-- <span class="header-title">ALUMNI MANAGEMENT SYSTEM</span>  -->
                <div class="header-menu">
                    <label for="">
                    </label>

                    <div class="notify-icon">
                    </div>

                    <div class="notify-icon">
                    </div>

                    <div class="user">


                        <a href="./logout.php">
                           <span class="las la-power-off" style="font-size: 30px; border-left: 1px solid #fff; padding-left:10px; color:#fff"></span>
                        </a>

                    </div>
                </div>
            </div>
        </header>


        <main>

            <div class="page-header">
                <h1><Strong>Dashboard</Strong></h1>
            </div>
        </main>
        <div class="page-content">
            <div class="row">
                <div class="col-md-4">
                    <div class="card bg-warning text-white mb-4">
                        <div class="card-body">
                            <div class="d-flex align-items-center">
                                <i class="las la-calendar-alt fa-3x" style="font-size: 25px;"></i>
                                <div class="row mb-3">
                                    <!-- Display events Total Count -->
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
                <div class="col-md-8 notice-box">
                    <div class="notice-title">Notice Alumni!</div>
                    <div class="notice-text">
                        <span>Welcome to Cavite State University - Imus Campus' Alumni Management System. Please be informed that <em>alumni records and transactions are managed separately from current student records</em>. Therefore, updates made in the system may not immediately reflect changes in official university records. For any questions, inquiries, or technical issues regarding the Alumni Management System, please contact the administrator and coordinator of the system.</span><br><br>
                    </div>
                </div>
                <div class="container">
                    <div class="quick-links">
                        <h5><i class="fas fa-info-circle"></i> Quick Links</h5>
                        <div class="quick-link-item">
                            <a href="https://cvsu-imus.edu.ph">
                                <i class="fas fa-globe fa-3x"></i>
                                <p>CvSU Imus Website</p>
                            </a>
                        </div>
                        <div class="quick-link-item">
                            <a href="https://elearning.cvsu.edu.ph">
                                <i class="fas fa-chalkboard-teacher fa-3x"></i>
                                <p>CvSU eLearning</p>
                            </a>
                        </div>
                        <div class="quick-link-item">
                            <a href="https://www.facebook.com/CVSUImus">
                                <i class="fab fa-facebook fa-3x"></i>
                                <p>CvSU Imus Facebook page</p>
                            </a>
                        </div>
                        <div class="quick-link-item">
                            <a href="https://mail.google.com">
                                <i class="fas fa-envelope fa-3x"></i>
                                <p>Login your CvSU Email</p>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
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
</body>

</html>
