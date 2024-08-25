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
        header('Location: ../../coordinatorPage/dashboard_coor.php');
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
        header("Location: ../../homepage.php");
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
            header('Location: ../../alumniPage/dashboard_user.php');
            exit();
        } else {

            $_SESSION['email'] = $account_email;
            $_SESSION['alert'] = 'Unverified';
            sleep(2);
            header('Location: ../../loginPage/verification_code.php');
            exit();
        }
    }
} else {
    // Redirect to login if no matching user found
    session_destroy();
    header('Location: ../../homepage.php');
    exit();
}

$title = "";
$description = "";
$date = "";
$time = "";
$venue = "";
$address = "";
$eventFor = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = strtoupper($_POST['title']);
    $date = $_POST['date'];
    $startTime = $_POST['startTime'];
    $endTime = $_POST['endTime'];
    $venue = $_POST['venue'];
    $description = ucwords($_POST['description']);

    $house_no = ucwords($_POST['house_no']);
    $brgy = ucwords($_POST['brgy']);
    $city = ucwords($_POST['city']);
    $province = ucwords($_POST['province']);

    // Get selected eventFor values from the form
    if (isset($_POST['eventFor'])) {
        $eventFor = $_POST['eventFor'];
    }

    // List of all possible course values
    $allCourses = ['BAJ', 'BECEd', 'BEEd', 'BSBM', 'BSOA', 'BSEntrep', 'BSHM', 'BSIT', 'BSCS', 'BSc(Psych)'];

    // Check if $eventFor contains all courses
    if (array_diff($allCourses, $eventFor) === []) {
        $eventForString = "ALL";
    } else {
        $eventForString = implode(', ', $eventFor);
    }

    $house_no = ucwords($_POST['house_no']);
    $brgy = ucwords($_POST['brgy']);
    $city = ucwords($_POST['city']);
    $province = ucwords($_POST['province']);

    $address = ucwords($_POST['house_no']) . ', ' . ucwords($_POST['brgy']) . ', ' . ucwords($_POST['city']) . ', ' . ucwords($_POST['province']);

    // for image
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $file = addslashes(file_get_contents($_FILES["image"]["tmp_name"]));
        $sql = "INSERT INTO event SET title='$title', date='$date', start_time='$startTime', end_time='$endTime', venue='$venue', address='$address', event_for='$eventForString', description='$description', image='$file'";
    } else {
        // Path to the image file
        $filePath = '../../assets/no_image_available.png';
        $imageData = file_get_contents($filePath);
        $imageDataEscaped = addslashes($imageData);
        $sql = "INSERT INTO event SET title='$title', date='$date', start_time='$startTime', end_time='$endTime', venue='$venue', address='$address', event_for='$eventForString', description='$description', image='$imageDataEscaped'";
    }

    $result = $conn->query($sql);

    // FOR MESSAGEBOX WITHOUT TEXT ONLY
    $icon = 'success';
    $iconHtml = '<i class="fas fa-check-circle"></i>';
    $title = 'Event added successfully.';
    $redirectUrl = './event.php';

    echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                noTextRedirect('$redirectUrl', '$title', '$icon', '$iconHtml');
            });
        </script>";
    sleep(2);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
    <title>Update Event Info</title>
    <link rel="shortcut icon" href="../../assets/cvsu.png" type="image/svg+xml">
    <link rel="stylesheet" href="css/update_event.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* FOR SWEETALERT */
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
        }

        .confirm-button-class {
            background-color: #e03444 !important;
            color: white;
        }

        .cancel-button-class {
            background-color: #ffc404 !important;
            color: white;
        }


        #preview {
            max-width: 700px;
            max-height: 700px;
            object-fit: contain;
        }

        .input-container {
            display: flex;
            gap: 10px;
            /* Adjust the gap as needed */
        }

        .center-image {
            display: block;
            margin-left: auto;
            margin-right: auto;
            max-width: 100%;
            height: auto;
        }

        .dropdown-menu {
            max-height: absolute;
            /* Limit the height of the dropdown menu */
            overflow-y: auto;
            /* Add scroll if content exceeds height */
            padding: 0;
            /* Remove extra padding if needed */
        }

        .dropdown-menu label {
            display: block;
            /* Ensure each label takes up a full line */
            margin-bottom: 5px;
            /* Space between items */
            white-space: nowrap;
            /* Prevent text from wrapping */
        }

        .dropdown-menu input[type="checkbox"] {
            margin-right: 10px;
            /* Space between checkbox and label text */
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
                        <a href="../alumni/alumni.php">
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
                        <a href="./update_event.php" class="active">
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
                <h1><strong>Event</strong></h1>
            </div>
        </main>
        <form method="POST" class="addNew" enctype="multipart/form-data">
            <div class="container-fluid" id="page-content">
                <div class="row">
                    <div class="container-fluid" id="main-container">
                        <div class="container-fluid" id="content-container">
                            <h3 style="margin-bottom: 2%;">Add New Event</h3>
                            <div class="mb-3">
                                <label for="formGroupExampleInput" class="form-label">Event Title</label>
                                <input type="text" name="title" class="form-control" id="formGroupExampleInput" placeholder="Enter Event Title" required>
                            </div>
                            <div class="mb-3">
                                <label for="formGroupExampleInput2" class="form-label">Date</label>
                                <input type="date" name="date" class="form-control" id="date_input" required placeholder="">
                            </div>
                            <div class="mb-3">
                                <label for="formGroupExampleInput3" class="form-label">Time</label>
                                <div class="input-container">
                                    <input type="time" name="startTime" class="form-control" id="start_time_input" required placeholder="">
                                    <span class="time-separator" style="margin-top:7px;">To</span>
                                    <input type="time" name="endTime" class="form-control" id="end_time_input" required placeholder="">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="formGroupExampleInput4" class="form-label">Venue</label>
                                <input type="text" name="venue" class="form-control" id="formGroupExampleInput4" required placeholder="">
                            </div>
                            <div class="mb-3">
                                <label for="formGroupExampleInput" class="form-label">ADDRESS</label>
                                <br>
                                <div class="row g-3">
                                    <div class="col">
                                        <label for="house_no" class="form-label">House No. | Street | Subdivision</label>
                                        <input type="text" name="house_no" class="form-control" id="house_no" required>
                                    </div>
                                    <div class="col">
                                        <label for="brgy" class="form-label">Barangay</label>
                                        <input type="text" name="brgy" class="form-control" id="brgy" required>
                                    </div>
                                    <div class="col">
                                        <label for="city" class="form-label">City</label>
                                        <input type="text" name="city" class="form-control" id="city" required>
                                    </div>
                                    <div class="col">
                                        <label for="province" class="form-label">Province</label>
                                        <input type="text" name="province" class="form-control" id="province" required>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="eventForDropdown">Event For:</label>
                                <div class="dropdown">
                                    <button class="form-control" type="button" id="eventForDropdown" onclick="toggleDropdown()" style="height: 100%; width: 430px;">Select Courses</button>
                                    <div id="eventForMenu" class="dropdown-menu" style="display:none; position: absolute; background-color: white; border: 1px solid #ccc; padding: 10px;">
                                        <label><input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)" checked> All Courses</label><br>
                                        <label><input type="checkbox" class="eventForCheckbox" name="eventFor[]" value="BAJ" checked> Bachelor of Arts in Journalism</label><br>
                                        <label><input type="checkbox" class="eventForCheckbox" name="eventFor[]" value="BECEd" checked> Bachelor of Secondary Education</label><br>
                                        <label><input type="checkbox" class="eventForCheckbox" name="eventFor[]" value="BEEd" checked> Bachelor of Elementary Education</label><br>
                                        <label><input type="checkbox" class="eventForCheckbox" name="eventFor[]" value="BSBM" checked> Bachelor of Science in Business Management</label><br>
                                        <label><input type="checkbox" class="eventForCheckbox" name="eventFor[]" value="BSOA" checked> Bachelor of Science in Office Administration</label><br>
                                        <label><input type="checkbox" class="eventForCheckbox" name="eventFor[]" value="BSEntrep" checked> Bachelor of Science in Entrepreneurship</label><br>
                                        <label><input type="checkbox" class="eventForCheckbox" name="eventFor[]" value="BSHM" checked> Bachelor of Science in Hospitality Management</label><br>
                                        <label><input type="checkbox" class="eventForCheckbox" name="eventFor[]" value="BSIT" checked> Bachelor of Science in Information Technology</label><br>
                                        <label><input type="checkbox" class="eventForCheckbox" name="eventFor[]" value="BSCS" checked> Bachelor of Science in Computer Science</label><br>
                                        <label><input type="checkbox" class="eventForCheckbox" name="eventFor[]" value="BSc(Psych)" checked> Bachelor of Science in Psychology</label>
                                    </div>
                                </div>
                                <!-- Hidden required checkbox -->
                                <input type="checkbox" id="hiddenCheckbox" style="display:none;" required>
                            </div>
                            <div class="row">
                                <div class="container-fluid">
                                    <div class="mb-3">
                                        <label for="exampleFormControlTextarea1" class="form-label">Enter Description</label>
                                        <textarea name="description" class="form-control" id="exampleFormControlTextarea1" rows="3" required></textarea>
                                    </div>
                                </div>
                                <div class="container-fluid">
                                    <div class="row">
                                        <div class="mb-3">
                                            <input class="form-control" type="file" id="image" name="image" onchange="getImagePreview(event)">
                                        </div>
                                        <div class="col-md-12 mb-md-0 p-md-12" style="text-align: center;">
                                            <?php if (!empty($row['image'])): ?>
                                                <!-- Display image from the database -->
                                                <img id="preview" src="data:image/jpeg;base64,<?php echo base64_encode($row['image']); ?>" alt="EVENT IMAGE" class="center-image">
                                            <?php else: ?>
                                                <!-- Placeholder for the image preview -->
                                                <img id="preview" src="#" alt="EVENT IMAGE" class="center-image" style="display: none;">
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="container-fluid" id="button-response">
                    <div class="row">
                        <div class="d-grid col-4 mx-auto">
                            <button type="submit" class="btn btn-warning">Submit</button>
                        </div>
                        <div class="d-grid col-4 mx-auto">
                            <a class="btn btn-danger" href="./event.php">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Script to display preview of selected image -->
    <script>
        function getImagePreview(event) {
            var file = event.target.files[0];
            if (file) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var preview = document.getElementById('preview');
                    preview.src = e.target.result;
                    preview.style.display = 'block'; // Show the image preview
                };
                reader.readAsDataURL(file);
            }
        }

        // Image validation and insertion script (optional)
        $(document).ready(function() {
            $('#insert').click(function() {
                var image_name = $('#image').val();
                if (image_name == '') {
                    alert("Please select an image.");
                    return false;
                } else {
                    var extension = $('#image').val().split('.').pop().toLowerCase();
                    if ($.inArray(extension, ['gif', 'png', 'jpg', 'jpeg']) == -1) {
                        alert("Invalid image file.");
                        $('#image').val('');
                        return false;
                    }
                }
            });
        });

        // FOR DROPDOWN CHECKLIST
        // Toggle dropdown visibility
        function toggleDropdown() {
            var dropdown = document.getElementById('eventForMenu');
            dropdown.style.display = (dropdown.style.display === 'none' || dropdown.style.display === '') ? 'block' : 'none';
        }

        // Toggle select all checkboxes
        function toggleSelectAll(selectAllCheckbox) {
            var checkboxes = document.querySelectorAll('.eventForCheckbox');
            checkboxes.forEach(function(checkbox) {
                checkbox.checked = selectAllCheckbox.checked;
            });
            updateHiddenCheckbox();
        }

        // Uncheck 'ALL' when any individual checkbox is unchecked
        document.querySelectorAll('.eventForCheckbox').forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                if (!this.checked) {
                    document.getElementById('selectAll').checked = false;
                }
                updateHiddenCheckbox();
            });
        });

        // Set initial state of the dropdown to have all checkboxes checked
        window.onload = function() {
            document.querySelectorAll('.eventForCheckbox').forEach(function(checkbox) {
                checkbox.checked = true;
            });
            document.getElementById('selectAll').checked = true;
            updateHiddenCheckbox();
        };

        // Update the hidden checkbox based on the user's selections
        function updateHiddenCheckbox() {
            var checkedBoxes = document.querySelectorAll('.eventForCheckbox:checked');
            var hiddenCheckbox = document.getElementById('hiddenCheckbox');
            var dropdownButton = document.getElementById('eventForDropdown');

            if (checkedBoxes.length > 0) {
                hiddenCheckbox.checked = true; // Check hidden checkbox if any checkbox is selected
                dropdownButton.textContent = 'Courses Selected'; // Update button text
                dropdownButton.style.border = '1px solid #ccc'; // Reset border if previously marked as required
            } else {
                hiddenCheckbox.checked = false; // Uncheck hidden checkbox
                dropdownButton.textContent = 'Select Courses (Required)'; // Show required message on button
                dropdownButton.style.border = '1px solid red'; // Highlight the button as required
            }
        }
        // FOR MESSAGEBOX WITHOUT TEXT ONLY
        function noTextRedirect(redirectUrl, title, icon, iconHtml) {
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
            }).then(() => {
                window.location.href = redirectUrl; // Redirect to the desired page
            });
        }
        // Wait until the DOM is fully loaded
        document.addEventListener('DOMContentLoaded', function () {
            // Select the date and time input elements
            const dateInput = document.getElementById('date_input');
            const startTimeInput = document.getElementById('start_time_input');
            const endTimeInput = document.getElementById('end_time_input');

            // Function to disable past dates
            function disablePastDates() {
                const today = new Date().toISOString().split('T')[0];
                dateInput.setAttribute('min', today);
            }

            // Function to show SweetAlert if a past date is selected
            function handleDateChange() {
                const selectedDate = new Date(dateInput.value);
                const today = new Date();
                today.setHours(0, 0, 0, 0); // Remove time from the current date
                
                if (selectedDate < today) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Date',
                        text: 'Previous dates are not allowed!',
                    });
                    dateInput.value = ''; // Clear the invalid date
                }
            }

            // Function to validate end time based on start time
            function handleTimeChange() {
                const startTime = startTimeInput.value;
                const endTime = endTimeInput.value;

                if (startTime && endTime && endTime <= startTime) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Time',
                        text: 'End time must be later than start time!',
                    });
                    endTimeInput.value = ''; // Clear the invalid end time
                }
            }

            // Disable past dates on page load
            disablePastDates();

            // Event listeners
            dateInput.addEventListener('change', handleDateChange);
            startTimeInput.addEventListener('change', handleTimeChange);
            endTimeInput.addEventListener('change', handleTimeChange);
        });

    </script>
</body>

</html>