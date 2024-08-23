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
        $user = $user_result->fetch_assoc();
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


// ALUMNI COUNT
$sql = "SELECT COUNT(student_id) AS alumni_count FROM alumni";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$count = $row['alumni_count'];

// EVENT COUNT
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
    <title>Coordinator Dashboard</title>
    <link rel="shortcut icon" href="../assets/cvsu.png" type="image/svg+xml">
    <link rel="stylesheet" href="./dashboard.css">
    <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
                        <a href="./dashboard_coor.php" class="active">
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
                    <span class="las la-bars"></span>
                </label>

                <div class="header-menu">
                    <label for="">
                    </label>

                    <div class="notify-icon">
                    </div>

                    <div class="notify-icon">
                    </div>

                    <div class="user">
                        <div class="bg-img" style="background-image: url(img/1.jpeg)"></div>

                        <a href="./logout.php">
                            <span class="las la-power-off"></span>
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
                                                    <label class="col-sm-10 col-form-label" style="font-size: 40px;"><?php echo $count; ?></label>
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
                <!-- First container -->
                <div class="container2 animate-item">
                    <h1>
                        Announcement
                        <div class="add-new" id="add-new1">+ Add New</div>
                    </h1>
                    <div class="scroll">
                        <div class="announcement" id="announcement-1" data-title="New Workshop Series" data-date="10/10/2022" data-info=" New Workshop Series Date: September 5, 2024
                                    Details: Join us for an in-depth workshop series covering the latest in web development, including HTML, CSS, JavaScript, and more. Open to all students and professionals. Register now to secure your spot!">
                            <div class="announcement-title inline">Announcement No. # 1</div>
                            <div class="announcement-date">10/10/2022</div>
                        </div>
                        <div class="announcement" id="announcement-1" data-title="Holiday Office Closure" data-date="10/10/2022" data-info="Title: Office Closed for Labor Day Date: September 2, 2024
                                    Details: Please note that our offices will be closed on September 2nd in observance of Labor Day. Regular office hours will resume on September 3rd.">
                            <div class="announcement-title inline">Announcement No. # 2</div>
                            <div class="announcement-date">10/10/2022</div>
                        </div>
                        <div class="announcement" id="announcement-1" data-title="System Maintenance" data-date="10/10/2022" data-info="Title: Scheduled Maintenance on August 30, 2024
                                    Date: August 30, 2024
                                    Details: Our systems will undergo scheduled maintenance from 12:00 AM to 6:00 AM. During this time, some services may be unavailable. We apologize for any inconvenience.">
                            <div class="announcement-title inline">Announcement No. # 3</div>
                            <div class="announcement-date">10/10/2022</div>
                        </div>
                        <div class="announcement" id="announcement-1" data-title="New Feature Release" data-date="10/10/2022" data-info="Title: Introducing Dark Mode
                                    Date: August 25, 2024
                                    Details: We are excited to announce the launch of Dark Mode for the dashboard. You can now switch between light and dark themes in the settings menu for a more comfortable viewing experience.">
                            <div class="announcement-title inline">Announcement No. # 4</div>
                            <div class="announcement-date">10/10/2022</div>
                        </div>
                        <div class="announcement" id="announcement-1" data-title="Team Meeting" data-date="10/10/2022" data-info="Title: Quarterly Team Meeting
                                    Date: September 10, 2024
                                    Details: All team members are required to attend the quarterly meeting on September 10th at 10:00 AM in the main conference room. Please come prepared with your updates and reports.">
                            <div class="announcement-title inline">Announcement No. # 5</div>
                            <div class="announcement-date">10/10/2022</div>
                        </div>
                        <div class="announcement" id="announcement-1" data-title="New Course Enrollment" data-date="10/10/2022" data-info="Title: Enrollment Open for Advanced Python Programming
                        Date: September 15, 2024
                        Details: We are pleased to announce that enrollment is now open for our new course on Advanced Python Programming. This course is designed for intermediate to advanced learners looking to deepen their Python skills. Spaces are limited, so enroll today!">
                            <div class="announcement-title inline">Announcement No. # 6</div>
                            <div class="announcement-date">10/10/2022</div>
                        </div>
                        <div class="announcement" id="announcement-1" data-title="Community Service Event" data-date="10/10/2022" data-info="Title: Join the Community Clean-Up Day
                                    Date: October 12, 2024
                                    Details: We invite all members to participate in our annual Community Clean-Up Day. Let's come together to make a positive impact in our local neighborhoods. All volunteers will receive a free t-shirt and lunch. Sign up now!">
                            <div class="announcement-title inline">Announcement No. # 7</div>
                            <div class="announcement-date">10/10/2022</div>
                        </div>
                        <div class="announcement" id="announcement-1" data-title="Security Update" data-date="10/10/2022" data-info="Title: Important: Password Reset Required
                                    Date: September 20, 2024
                                    Details: Due to recent security updates, all users are required to reset their passwords by September 25th. Please ensure your new password is strong and unique. Visit the account settings page to complete this process.">
                            <div class="announcement-title inline">Announcement No. # 8</div>
                            <div class="announcement-date">10/10/2022</div>
                        </div>
                        <div class="popup-form" id="popup-form">
                            <h2 id="popup-title">Announcement Details</h2>
                            <p id="popup-date"></p>
                            <p id="popup-info"></p>
                            <button class="update-btn" id="update-btn">Update</button>
                            <button class="archive-btn" id="archive-btn">Archive</button>
                            <button class="close-btn" id="close-popup">Close</button>
                        </div>

                        <!-- Update Popup -->
                        <div class="popup-form" id="update-popup" style="display: none;">
                            <h1>Update Announcement</h1>
                            <div class="form-group">
                                <label for="subject">Subject/Title:</label>
                                <input type="text" id="subject" name="subject">
                            </div>
                            <div class="form-group">
                                <label for="message">Message/Body:</label>
                                <textarea id="message" name="message"></textarea>
                            </div>
                            <br>
                            <div class="actions1">
                                <button type="submit">Save</button>
                            </div>
                            <p id="update-details"></p>
                            <button class="close-btn" id="close-update-popup">Close</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Second container -->
            <div class="container3">
                <span id="close-popup" style="cursor: pointer;">&times;</span>
                <h1>Add New Announcement</h1>
                <div class="form-group">
                    <label for="new-subject">Subject/Title:</label>
                    <input type="text" id="new-subject" name="subject">
                </div>
                <div class="form-group">
                    <label for="new-message">Message/Body:</label>
                    <textarea id="new-message" name="message"></textarea>
                </div>
                <br>
                <div class="actions">
                    <button type="button" id="save-announcement">Save</button>
                </div>
            </div>


        </main>
    </div>

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
    </script>

    <script>
        document.getElementById("add-new1").addEventListener("click", function() {
            document.querySelector(".container3").style.display = "block";
        });

        // Optional: To close the popup when clicking outside of it or adding a close button.
        window.onclick = function(event) {
            if (event.target.className === 'container3') {
                document.querySelector(".container3").style.display = "none";
            }
        }
    </script>
    <script>
        document.getElementById("close-popup").addEventListener("click", function() {
            document.querySelector(".container3").style.display = "none";
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Attach click event listener to each announcement
            document.querySelectorAll('.announcement').forEach(announcement => {
                announcement.addEventListener('click', function() {
                    // Get the data attributes for the announcement
                    const title = this.getAttribute('data-title');
                    const date = this.getAttribute('data-date');
                    const info = this.getAttribute('data-info');

                    // Set the pop-up content
                    document.getElementById('popup-title').textContent = title;
                    document.getElementById('popup-date').textContent = date;
                    document.getElementById('popup-info').textContent = info;

                    // Display the pop-up
                    document.getElementById('popup-form').style.display = 'block';
                });
            });

            // Close button functionality
            document.getElementById('close-popup').addEventListener('click', function() {
                document.getElementById('popup-form').style.display = 'none';
            });
        });
    </script>


    <script>
        // Get elements
        const updateBtn = document.getElementById('update-btn');
        const archiveBtn = document.getElementById('archive-btn');
        const closeBtn = document.getElementById('close-popup');
        const closeUpdateBtn = document.getElementById('close-update-popup');
        const closeArchiveBtn = document.getElementById('close-archive-popup');

        const popupForm = document.getElementById('popup-form');
        const updatePopup = document.getElementById('update-popup');
        const archivePopup = document.getElementById('archive-popup');

        // Function to open update popup
        updateBtn.addEventListener('click', function() {
            popupForm.style.display = 'none';
            updatePopup.style.display = 'block';
        });

        // Function to open archive popup
        archiveBtn.addEventListener('click', function() {
            popupForm.style.display = 'none';
            archivePopup.style.display = 'block';
        });

        // Function to close update popup
        closeUpdateBtn.addEventListener('click', function() {
            updatePopup.style.display = 'none';
            popupForm.style.display = 'block';
        });

        // Function to close archive popup
        closeArchiveBtn.addEventListener('click', function() {
            archivePopup.style.display = 'none';
            popupForm.style.display = 'block';
        });

        // Function to close main popup form
        closeBtn.addEventListener('click', function() {
            popupForm.style.display = 'none';
        });
    </script>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Functionality to save the new announcement
            document.getElementById('save-announcement').addEventListener('click', function() {
                // Get the values from the input fields
                const newTitle = document.getElementById('new-subject').value.trim();
                const newInfo = document.getElementById('new-message').value.trim();
                const currentDate = new Date().toLocaleDateString();

                // Check if the input fields are not empty
                if (newTitle !== "" && newInfo !== "") {
                    // Create a new announcement div
                    const newAnnouncement = document.createElement('div');
                    newAnnouncement.classList.add('announcement');
                    newAnnouncement.setAttribute('data-title', newTitle);
                    newAnnouncement.setAttribute('data-date', currentDate);
                    newAnnouncement.setAttribute('data-info', newInfo);

                    // Create the inner HTML for the new announcement
                    newAnnouncement.innerHTML = `
                <div class="announcement-title inline">${newTitle}</div>
                <div class="announcement-date">${currentDate}</div>
            `;

                    // Insert the new announcement at the top of the scroll container in container2
                    const scrollContainer = document.querySelector('.container2 .scroll');
                    scrollContainer.insertBefore(newAnnouncement, scrollContainer.firstChild);

                    // Clear the input fields
                    document.getElementById('new-subject').value = "";
                    document.getElementById('new-message').value = "";

                    // Close the add new announcement form (optional)
                    document.querySelector('.container3').style.display = 'none';

                    // Reattach the click event listener for the new announcement
                    newAnnouncement.addEventListener('click', function() {
                        const title = this.getAttribute('data-title');
                        const date = this.getAttribute('data-date');
                        const info = this.getAttribute('data-info');

                        document.getElementById('popup-title').textContent = title;
                        document.getElementById('popup-date').textContent = date;
                        document.getElementById('popup-info').textContent = info;
                        document.getElementById('popup-form').style.display = 'block';
                    });
                } else {
                    alert('Please fill out both the title and message fields.');
                }
            });

            // Existing functionality for closing the popups, etc.
            // (Insert the previous script logic here)
        });
    </script>





    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const archivePopup = document.getElementById('archive-popup');
            const closeArchivePopup = document.getElementById('close-archive-popup');
            const archiveButton = document.getElementById('archive-btn');
            let selectedAnnouncementId = null;

            // Show archive popup and set the announcement ID
            document.querySelectorAll('.announcement').forEach(announcement => {
                announcement.addEventListener('click', (event) => {
                    selectedAnnouncementId = event.currentTarget.id;
                    const announcementTitle = event.currentTarget.querySelector('.announcement-title').textContent;
                    const announcementDate = event.currentTarget.querySelector('.announcement-date').textContent;
                    const announcementInfo = event.currentTarget.getAttribute('data-info');

                    document.getElementById('archive-details').innerHTML = `
                    <strong>Title:</strong> ${announcementTitle}<br>
                    <strong>Date:</strong> ${announcementDate}<br>
                    <strong>Info:</strong> ${announcementInfo}
                `;

                    archivePopup.style.display = 'block';
                });
            });

            // Archive the announcement
            document.getElementById('archive-btn').addEventListener('click', () => {
                if (selectedAnnouncementId) {
                    const announcementToRemove = document.getElementById(selectedAnnouncementId);
                    if (announcementToRemove) {
                        announcementToRemove.remove();
                    }
                    archivePopup.style.display = 'none';
                    selectedAnnouncementId = null; // Reset the selected announcement
                }
            });

            // Close archive popup
            closeArchivePopup.addEventListener('click', () => {
                archivePopup.style.display = 'none';
            });

            // Handle the 'Add New' button functionality to ensure the newly added announcement is clickable
            document.getElementById('save-announcement').addEventListener('click', () => {
                const newSubject = document.getElementById('new-subject').value;
                const newMessage = document.getElementById('new-message').value;
                if (newSubject && newMessage) {
                    const newAnnouncement = document.createElement('div');
                    newAnnouncement.classList.add('announcement');
                    newAnnouncement.id = `announcement-${Date.now()}`; // Unique ID
                    newAnnouncement.setAttribute('data-title', newSubject);
                    newAnnouncement.setAttribute('data-date', new Date().toLocaleDateString());
                    newAnnouncement.setAttribute('data-info', newMessage);
                    newAnnouncement.innerHTML = `
                    <div class="announcement-title inline">${newSubject}</div>
                    <div class="announcement-date">${new Date().toLocaleDateString()}</div>
                `;

                    // Append the new announcement to container2
                    document.querySelector('.container2 .scroll').appendChild(newAnnouncement);

                    // Clear the input fields
                    document.getElementById('new-subject').value = '';
                    document.getElementById('new-message').value = '';

                    // Add click event to the new announcement
                    newAnnouncement.addEventListener('click', (event) => {
                        selectedAnnouncementId = event.currentTarget.id;
                        const announcementTitle = event.currentTarget.querySelector('.announcement-title').textContent;
                        const announcementDate = event.currentTarget.querySelector('.announcement-date').textContent;
                        const announcementInfo = event.currentTarget.getAttribute('data-info');

                        document.getElementById('archive-details').innerHTML = `
                        <strong>Title:</strong> ${announcementTitle}<br>
                        <strong>Date:</strong> ${announcementDate}<br>
                        <strong>Info:</strong> ${announcementInfo}
                    `;

                        archivePopup.style.display = 'block';
                    });
                }
            });
        });

        function showPopup() {
            document.getElementById('popup-form').classList.add('show');
        }

        // Function to hide the popup
        function hidePopup() {
            document.getElementById('popup-form').classList.remove('show');
        }

        // JavaScript to trigger animation on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Select all elements that should animate
            const items = document.querySelectorAll('.animate-item');

            // Add the animation class to each item
            items.forEach((item, index) => {
                // Delay each item's animation slightly to create a staggered effect
                item.style.animationDelay = `${index * 100}ms`;
                item.classList.add('animate-in');
            });
        });
    </script>


</body>

</html>