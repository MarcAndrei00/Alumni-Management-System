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

    // Check if user is a alumni_archive
    $stmt = $conn->prepare("SELECT * FROM alumni_archive WHERE alumni_id = ? AND email = ?");
    $stmt->bind_param("ss", $account, $account_email);
    $stmt->execute();
    $user_result = $stmt->get_result();

    if ($user_result->num_rows > 0) {
        session_destroy();
        header("Location: ../homepage.php");
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
            // User is an alumni
            $user = $user_result->fetch_assoc();
        } else {
            $stmt->close();
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


//read data from table alumni
$sql = "SELECT * FROM alumni WHERE alumni_id=$account";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

$file = $row['picture'];

//query for events count
$sql_event = "SELECT COUNT(event_id) AS events_count FROM event";

$result_event = $conn->query($sql_event);
$row_event = $result_event->fetch_assoc();
$event_count = $row_event['events_count'];


// ANNOUNCEMENT
$sql = "SELECT * FROM announcement";
$result = $conn->query($sql);
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .popup {
            display: none;
            margin-bottom: 20px;
        }

        .popup {
            display: none;
            opacity: 0;
            transform: translateY(-50px);
            /* Start off-screen */
            transition: opacity 0.8s ease, transform 0.8s ease;
        }

        .popup.show {
            display: block;
            /* Ensure the popup is displayed */
            opacity: 1;
            /* Fade in */
            transform: translateY(0);
            /* Slide into view */
        }

        .popup.fade-out {
            opacity: 0;
            /* Fade out */
        }

        .popup-content {
            transform: translateY(0);
            transition: transform 0.8s ease;
        }

        .popup-content.slide-out {
            transform: translateY(-50px);
            /* Slide out upwards */
        }


        .announcement-date-display {
            display: block;
            font-size: 0.73rem;
            color: #888;
        }

        .announcement-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .announcement-date-display {
            /* This will align the date to the right */
            text-align: right;
        }

        /* TRASITION */
        .popup-form {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0;
            transition: opacity 0.3s ease, transform 0.3s ease;
            display: none;
        }

        .popup-form.show {
            display: block;
            opacity: 1;
        }

        .popup-form.fade-out {
            opacity: 0;
        }

        .popup-form.slide-out {
            transform: translate(-50%, -50%) translateY(-100%);
        }


        /* TEXTBOX */
        textarea {
            width: 100%;
            padding: 2px;
            border: none #fff;
            background-color: transparent;
            border-radius: 5px;
            resize: none;
            font-size: 0.90rem;
        }

        textarea {
            height: 200px;
            resize: none;
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
                <div>
                    <img id="preview" src="data:image/jpeg;base64,<?php echo base64_encode($row['picture']); ?>" style="width:83px;height:83px; border-radius: 100%;border: 2px solid white;">
                </div>
                <h4 style="overflow-y: hidden;"><?php echo $user['fname']; ?></h4>
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
                <h1 style="overflow-y: hidden;"><Strong>Dashboard</Strong></h1>
            </div>
        </main>
        <div class="col-md-11 notice-box">
            <div class="notice-title">Notice Alumni!</div>
            <div class="notice-text">
                <span>Welcome to Cavite State University - Imus Campus' Alumni Management System. Please be informed that <em>alumni records and transactions are managed separately from current student records</em>. Therefore, updates made in the system may not immediately reflect changes in official university records. For any questions, inquiries, or technical issues regarding the Alumni Management System, please contact the administrator and coordinator of the system.</span><br><br>
            </div>
        </div>
        <div class="page-content">
            <div class="row">
                <div class="col-md-4" style="margin-left: 50px; width:500px; margin-top:30px;">
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
            </div>
        </div>

        <!-- Event Calendar Section -->
        <div class="calendar-container">
            <div class="calendar-header">
                <button id="prevMonth">&lt;</button>
                <h2 id="monthYear" style="overflow-y: hidden;"></h2>
                <button id="nextMonth">&gt;</button>
            </div>
            <div class="calendar-days">
                <div>Sun</div>
                <div>Mon</div>
                <div>Tue</div>
                <div>Wed</div>
                <div>Thu</div>
                <div>Fri</div>
                <div>Sat</div>
            </div>
            <div class="calendar-dates" id="calendarDates"></div>
        </div>

        <!-- Announcement Section -->
        <div class="announcement1">
            <div class="announcement-header">
                <h2 style="overflow-y: hidden;">Announcements</h2>
            </div>
            <div class="announcement-list">
                <?php
                if ($result->num_rows > 0) {
                    while ($rowww = $result->fetch_assoc()) {
                        $announceId = $rowww['announce_id']; // Unique ID for the announcement
                ?>
                        <div class="announcement-item inline" onclick="openPopup('popup-<?php echo $announceId; ?>', this)">
                            <div class="announcement-header">
                                <h3 style="overflow-y: hidden; font-family: 'Poppins', sans-serif;"><?php echo $rowww['title']; ?></h3>
                                <span class="announcement-date-display">Posted on: <?php echo date('m/d/Y', strtotime($rowww['creation_date'])); ?></span>
                            </div>
                            <p class="inline"><?php echo $rowww['body']; ?></p>
                        </div>

                        <!-- Add more Popup Info announcements as needed -->
                        <div id="popup-<?php echo $announceId; ?>" class="popup">
                            <div class="popup-content">
                                <span class="close-button" onclick="closePopup('popup-<?php echo $announceId; ?>')">&times;</span>
                                <h3 style="overflow-y: hidden; font-family: 'Poppins', sans-serif;"><?php echo $rowww['title']; ?></h3>
                                <textarea disabled><?php echo $rowww['body']; ?></textarea>
                                <br>
                                <span class="announcement-date">Posted on: <?php echo date('m/d/Y, h:iA', strtotime($rowww['creation_date'])); ?></span>
                            </div>
                        </div>
                <?php
                    }
                } else {
                    echo "No announcements found.";
                }
                ?>
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
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarDates = document.getElementById('calendarDates');
            const monthYear = document.getElementById('monthYear');
            const prevMonth = document.getElementById('prevMonth');
            const nextMonth = document.getElementById('nextMonth');

            let currentDate = new Date();

            function renderCalendar() {
                const year = currentDate.getFullYear();
                const month = currentDate.getMonth();

                const firstDayOfMonth = new Date(year, month, 1).getDay();
                const lastDateOfMonth = new Date(year, month + 1, 0).getDate();
                const lastDayOfPrevMonth = new Date(year, month, 0).getDate();

                calendarDates.innerHTML = '';
                monthYear.textContent = `${currentDate.toLocaleString('default', { month: 'long' })} ${year}`;

                for (let i = firstDayOfMonth; i > 0; i--) {
                    calendarDates.innerHTML += `<div class="inactive">${lastDayOfPrevMonth - i + 1}</div>`;
                }

                for (let i = 1; i <= lastDateOfMonth; i++) {
                    const dayClass = i === new Date().getDate() && month === new Date().getMonth() && year === new Date().getFullYear() ? 'today' : '';
                    calendarDates.innerHTML += `<div class="${dayClass}">${i}</div>`;
                }
            }

            prevMonth.addEventListener('click', function() {
                currentDate.setMonth(currentDate.getMonth() - 1);
                renderCalendar();
            });

            nextMonth.addEventListener('click', function() {
                currentDate.setMonth(currentDate.getMonth() + 1);
                renderCalendar();
            });

            renderCalendar();
        });

        // ANNOUNCEMENT
        var currentPopupId = null;

        function openPopup(popupId, announcementItem) {
            // If there's already an open pop-up, close it
            if (currentPopupId && currentPopupId !== popupId) {
                closePopup(currentPopupId);
            }

            var popup = document.getElementById(popupId);
            var announcementDiv = announcementItem;
            announcementDiv.style.display = 'none';
            announcementDiv.insertAdjacentElement('afterend', popup);

            // Ensure the popup is displayed before adding the show class
            popup.style.display = 'block';
            setTimeout(() => {
                popup.classList.add('show');
            }, 10); // Add a slight delay to ensure display is applied

            // Update the currentPopupId to the new one
            currentPopupId = popupId;
        }

        function closePopup(popupId) {
            var popup = document.getElementById(popupId);
            var previousAnnouncement = popup.previousElementSibling;
            popup.classList.add('fade-out');
            var content = popup.querySelector('.popup-content');
            content.classList.add('slide-out');

            setTimeout(() => {
                popup.classList.remove('show'); // Remove show class for slide out
                popup.style.display = 'none'; // Ensure popup is hidden
                popup.classList.remove('fade-out');
                content.classList.remove('slide-out');
                previousAnnouncement.style.display = 'block';
            }, 800); // Match this time with the transition duration

            // Reset the currentPopupId
            currentPopupId = null;
        }
    </script>

    </script>


</body>

</html>