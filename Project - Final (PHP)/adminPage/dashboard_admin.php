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
        header('Location: ../coordinatorPage/dashboard_coor.php');
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
        header("Location: ../homepage.php");
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
    }
} else {
    // Redirect to login if no matching user found
    session_destroy();
    header('Location: ../homepage.php');
    exit();
}

// alumni count
$sql_alumni = "SELECT COUNT(student_id) AS alumni_count FROM alumni";
$result_alumni = $conn->query($sql_alumni);
$row_alumni = $result_alumni->fetch_assoc();
$count_alumni = $row_alumni['alumni_count'];

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

// ANNOUNCEMENT
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $subject = $_POST['subject'];
    $message = $_POST['message'];
    $announce_id = isset($_POST['announce_id']) ? $_POST['announce_id'] : null;

    if (isset($_POST['add'])) {
        // Add new announcement
        $sql = "INSERT INTO announcement (title, body, creation_date) VALUES ('$subject', '$message', NOW())";
        if ($conn->query($sql) === TRUE) {
            $icon = 'success';
            $iconHtml = '<i class="fas fa-check-circle"></i>';
            $title = 'New announcement added successfully';

            echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        noTextMessage('$title', '$icon', '$iconHtml');
                    });
                </script>";
            sleep(2);
        }
    } elseif (isset($_POST['update'])) {
        // Update existing announcement
        $sql = "UPDATE announcement SET title='$subject', body='$message' WHERE announce_id='$announce_id'";
        if ($conn->query($sql) === TRUE) {
            $icon = 'success';
            $iconHtml = '<i class="fas fa-check-circle"></i>';
            $title = 'Announcement updated successfully';

            echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        noTextMessage('$title', '$icon', '$iconHtml');
                    });
                </script>";
            sleep(2);
        }
    } elseif (isset($_POST['delete'])) {
        // Archive/Delete announcement
        $sql = "DELETE FROM announcement WHERE announce_id='$announce_id'";
        if ($conn->query($sql) === TRUE) {
            $icon = 'success';
            $iconHtml = '<i class="fas fa-check-circle"></i>';
            $title = 'Announcement deleted successfully';

            echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        noTextMessage('$title', '$icon', '$iconHtml');
                    });
                </script>";
            sleep(2);
        }
    }
}



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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .swal2-popup {
            padding-bottom: 30px;
            /* Adjust the padding as needed */
        }

        .confirm-button-class,
        .cancel-button-class {
            width: 150px;
            /* Set the desired width */
            height: 40px;
            /* Set the desired height */
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            /* Hide the overflow to prevent scroll bars */
            white-space: nowrap;
            /* Prevent text wrapping */
        }

        .confirm-button-class {
            background-color: #e03444 !important;
            color: white;
        }

        .cancel-button-class {
            background-color: #ffc404 !important;
            color: white;
        }

        /* FOR ANNOUNCEMENT */
        .inline {
            font-size: 14px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 65%;
        }

        .inlineBody {
            font-size: 12px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 95%;
            color: #666;
            margin-top: 15.3px;
        }

        .update-btn,
        .add-btn,
        .archive-btn,
        .close-btn {
            width: 120px;
            height: 40px;
            padding: 10px;
            font-size: 16px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
        }

        .announcement-active {
            background-color: #b9f982;
            color: rgb(119, 116, 116);
        }
    </style>
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
                                <div class="col-md-4  animate-item" style="margin-left: 50px; width:500px; margin-top:30px;">
                                    <div class="card bg-primary text-white mb-4">
                                        <div class="card-body" style=" padding-bottom:40px;">
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
                                <div class="col-md-4 animate-item" style="margin-left: -500px; margin-top:250px; width:500px;">
                                    <div class="card bg-warning text-white mb-4">
                                        <div class="card-body" style="padding-bottom:40px;">
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

            <div class="main-container">
                <div class="container2 animate-item">
                    <h1>
                        Announcement
                        <div class="add-new" id="add-new1">+ Add New</div>
                    </h1>
                    <div class="scroll">
                        <?php
                        // Fetch announcements
                        $sql = "SELECT * FROM announcement";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                            while ($rowww = $result->fetch_assoc()) {
                        ?>
                                <div class="announcement" data-id="<?php echo $rowww['announce_id']; ?>" data-title="<?php echo $rowww['title']; ?>" data-body="<?php echo $rowww['body']; ?>" data-date="<?php echo date('m/d/Y, h:iA', strtotime($rowww['creation_date'])); ?>">
                                    <div class="announcement-title inline"><?php echo $rowww['title']; ?></div>
                                    <div class="announcement-date" style="text-align: right;"><small>Posted on: <?php echo date('m/d/Y', strtotime($rowww['creation_date'])); ?></small> </div>
                                    <div class="inlineBody"><?php echo $rowww['body']; ?></div>
                                </div>
                        <?php
                            }
                        } else {
                            echo "No announcements found.";
                        }
                        ?>
                    </div>

                    <div class="popup-form" id="update-popup" style="display: none;">
                        <form method="POST">
                            <h1>Announcement Details</h1>
                            <input type="hidden" id="announce-id" name="announce_id">

                            <div class="form-group">
                                <label for="subject">Title:</label> <div class="announcement-date" style="white-space: nowrap; text-align: right; margin-bottom:5px;">Posted On: <span id="thedate"></span></div>

                                <input type="text" id="subject" name="subject">
                            </div>

                            <div class="form-group">
                                <label for="message">Message:</label>
                                <textarea id="message" name="message"></textarea>
                            </div>
                            <button class="update-btn" type="submit" name="update">Update</button>
                            <button class="archive-btn" type="submit" name="delete">Delete</button>
                            <button class="close-btn" id="close-popup">Close</button>
                        </form>
                    </div>

                    <!-- Add New Announcement Form -->
                    <div class="popup-form" id="add-new-form" style="display: none;">
                        <form method="POST">
                            <h1>Add New Announcement</h1>
                            <div class="form-group">
                                <label name="thedate"></label>
                                <label for="new-subject">Title:</label>
                                <input type="text" id="new-subject" name="subject">
                            </div>
                            <div class="form-group">
                                <label for="new-message">Message:</label>
                                <textarea id="new-message" name="message"></textarea>
                            </div>
                            <button class="add-btn" type="submit" name="add">Add</button>
                            <button class="close-btn" id="close-add-new">Close</button>
                        </form>
                    </div>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // Add new announcement button
                    document.getElementById('add-new1').addEventListener('click', function() {
                        document.getElementById('add-new-form').style.display = 'block';
                        document.getElementById('update-popup').style.display = 'none';
                        // Remove active class from any previously selected announcement
                        document.querySelectorAll('.announcement').forEach(announcement => {
                            announcement.classList.remove('announcement-active');
                        });
                    });

                    // Close add new form
                    document.getElementById('close-add-new').addEventListener('click', function() {
                        document.getElementById('add-new-form').style.display = 'none';
                    });

                    // Click on an announcement to edit
                    document.querySelectorAll('.announcement').forEach(announcement => {
                        announcement.addEventListener('click', function() {
                            const id = this.getAttribute('data-id');
                            const title = this.getAttribute('data-title');
                            const body = this.getAttribute('data-body');
                            const date = this.getAttribute('data-date');

                            document.getElementById('announce-id').value = id;
                            document.getElementById('subject').value = title;
                            document.getElementById('message').value = body;
                            document.getElementById('thedate').innerText = date; // Display the date in the label

                            // Remove active class from any previously selected announcement
                            document.querySelectorAll('.announcement').forEach(ann => {
                                ann.classList.remove('announcement-active');
                            });

                            // Add active class to the clicked announcement
                            this.classList.add('announcement-active');

                            document.getElementById('update-popup').style.display = 'block';
                            document.getElementById('add-new-form').style.display = 'none';
                        });
                    });

                    // Close update form
                    document.getElementById('close-popup').addEventListener('click', function() {
                        document.getElementById('update-popup').style.display = 'none';
                        // Remove active class from the announcement when the popup is closed
                        document.querySelectorAll('.announcement').forEach(announcement => {
                            announcement.classList.remove('announcement-active');
                        });
                    });
                });
            </script>


            <script>
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

                // FOR MESSAGEBOX WITHOUT TEXT AND REDIRECT
                function noTextMessage(title, icon, iconHtml) {
                    Swal.fire({
                        icon: icon,
                        iconHtml: iconHtml, // Custom icon using Font Awesome
                        title: title,
                        customClass: {
                            popup: 'swal-custom'
                        },
                        showConfirmButton: true,
                        confirmButtonColor: '#4CAF50',
                        confirmButtonText: 'OK',
                        timer: 5000
                    });
                }
            </script>
</body>

</html>