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

$title = "";
$description = "";
$date = "";

// get the data from form

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Show the data of alumni
    if (!isset($_GET['id'])) {
        header("location: ./event.php");
        exit;
    }
    $event_id = $_GET['id'];

    // Fetch the event details
    $sql = "SELECT * FROM event WHERE event_id=$event_id";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();

    if (!$row) {
        header("location: ./event.php");
        exit;
    }

    $event_id = $row['event_id'];
    $title = $row['title'];
    $date = $row['date'];
    $startTime = $row['start_time'];
    $endTime = $row['end_time'];
    $venue = $row['venue'];
    $address = $row['address'];
    $description = $row['description'];
    $eventFor = explode(',', $row['event_for']); // Convert the comma-separated string to an array

    // Check if 'ALL' is in $eventFor
    $isAllChecked = in_array('ALL', $eventFor);
} else {

    if (!isset($_GET['id'])) {
        header("location: ./event.php");
        exit;
    }
    $event_id = $_GET['id'];
    $sql = "SELECT * FROM event WHERE event_id=$event_id";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $event_id = $_POST['event_id'];
        $title = ucwords($_POST['title']);
        $date = $_POST['date'];
        $startTime = $_POST['startTime'];
        $endTime = $_POST['endTime'];
        $venue = $_POST['venue'];
        $address = $_POST['address'];
        $description = ucwords($_POST['description']);

        // List of all possible course values
        $allCourses = ['BAJ', 'BECEd', 'BEEd', 'BSBM', 'BSOA', 'BSEntrep', 'BSHM', 'BSIT', 'BSCS', 'BSc(Psych)'];

        // Get selected courses
        $eventFor = isset($_POST['eventFor']) ? $_POST['eventFor'] : [];

        // Check if $eventFor contains all courses
        if (array_diff($allCourses, $eventFor) === []) {
            $eventForString = "ALL";
        } else {
            $eventForString = implode(',', $eventFor);
        }

        // For image
        if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
            $file = addslashes(file_get_contents($_FILES["image"]["tmp_name"]));
            $sql = "UPDATE event SET title='$title', date='$date', start_time='$startTime', end_time='$endTime', venue='$venue', address='$address', event_for='$eventForString', description='$description', image='$file' WHERE event_id='$event_id'";
        } else {
            $sql = "UPDATE event SET title='$title', date='$date', start_time='$startTime', end_time='$endTime', venue='$venue', address='$address', event_for='$eventForString', description='$description' WHERE event_id='$event_id'";
        }

        $result = $conn->query($sql);

        $icon = 'success';
        $iconHtml = '<i class="fas fa-check-circle"></i>';
        $title = 'Event updated successfully.';
        $redirectUrl = './event.php';

        echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    noTextRedirect('$redirectUrl', '$title', '$icon', '$iconHtml');
                });
            </script>";
        sleep(2);
    }
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
        <form method="POST" enctype="multipart/form-data" class="addNew">
            <div class="container-fluid" id="page-content">
                <div class="row">
                    <div class="container-fluid" id="main-container">
                        <div class="container-fluid" id="content-container">
                            <h3 style="margin-bottom: 2%;">Update Event</h3>
                            <div class="mb-3">
                                <input type="hidden" name="event_id" class="form-control" id="formGroupExampleInput" value="<?php echo $event_id; ?>">
                                <label for="formGroupExampleInput" class="form-label">Event Title</label>
                                <input type="text" name="title" class="form-control" id="formGroupExampleInput" required value="<?php echo $title; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="formGroupExampleInput2" class="form-label">Date</label>
                                <input type="date" name="date" class="form-control" id="formGroupExampleInput2" required value="<?php echo $date; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="formGroupExampleInput3" class="form-label">Time</label>
                                <div class="input-container">
                                    <input type="time" name="startTime" class="form-control" id="formGroupExampleInput3" required value="<?php echo $startTime; ?>">
                                    <span class="time-separator" style="margin-top:7px;">To</span>
                                    <input type="time" name="endTime" class="form-control" id="formGroupExampleInput4" required value="<?php echo $endTime; ?>">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="formGroupExampleInput4" class="form-label">Venue</label>
                                <input type="text" name="venue" class="form-control" id="formGroupExampleInput4" required value="<?php echo $venue; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="formGroupExampleInput5" class="form-label">Address</label>
                                <input type="text" name="address" class="form-control" id="formGroupExampleInput5" required value="<?php echo $address; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="eventForDropdown">Event For:</label>
                                <div class="dropdown">
                                    <button class="form-control" type="button" id="eventForDropdown" onclick="toggleDropdown()" style="height: 100%; width: 430px;">Select Courses</button>
                                    <div id="eventForMenu" class="dropdown-menu" style="display:none; position: absolute; background-color: white; border: 1px solid #ccc; padding: 10px;">
                                        <label><input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)" <?php echo $isAllChecked ? 'checked' : ''; ?>> All Courses</label><br>
                                        <label><input type="checkbox" class="eventForCheckbox" name="eventFor[]" value="BAJ" <?php echo $isAllChecked || in_array('BAJ', $eventFor) ? 'checked' : ''; ?>> Batchelor of Arts in Journalism</label><br>
                                        <label><input type="checkbox" class="eventForCheckbox" name="eventFor[]" value="BECEd" <?php echo $isAllChecked || in_array('BECEd', $eventFor) ? 'checked' : ''; ?>> Bachelor Of Secondary EducationBECEd</label><br>
                                        <label><input type="checkbox" class="eventForCheckbox" name="eventFor[]" value="BEEd" <?php echo $isAllChecked || in_array('BEEd', $eventFor) ? 'checked' : ''; ?>> Bachelor Of Elementary Education</label><br>
                                        <label><input type="checkbox" class="eventForCheckbox" name="eventFor[]" value="BSBM" <?php echo $isAllChecked || in_array('BSBM', $eventFor) ? 'checked' : ''; ?>> Bachelor Of Science In Business Management</label><br>
                                        <label><input type="checkbox" class="eventForCheckbox" name="eventFor[]" value="BSOA" <?php echo $isAllChecked || in_array('BSOA', $eventFor) ? 'checked' : ''; ?>> Bachelor of Science in Office Administration</label><br>
                                        <label><input type="checkbox" class="eventForCheckbox" name="eventFor[]" value="BSEntrep" <?php echo $isAllChecked || in_array('BSEntrep', $eventFor) ? 'checked' : ''; ?>> Bachelor Of Science In Entrepreneurship</label><br>
                                        <label><input type="checkbox" class="eventForCheckbox" name="eventFor[]" value="BSHM" <?php echo $isAllChecked || in_array('BSHM', $eventFor) ? 'checked' : ''; ?>> Bachelor Of Science In Hospitality Management</label><br>
                                        <label><input type="checkbox" class="eventForCheckbox" name="eventFor[]" value="BSIT" <?php echo $isAllChecked || in_array('BSIT', $eventFor) ? 'checked' : ''; ?>> Bachelor of Science in Information Technology</label><br>
                                        <label><input type="checkbox" class="eventForCheckbox" name="eventFor[]" value="BSCS" <?php echo $isAllChecked || in_array('BSCS', $eventFor) ? 'checked' : ''; ?>> Bachelor of Science in Computer Science</label><br>
                                        <label><input type="checkbox" class="eventForCheckbox" name="eventFor[]" value="BSc(Psych)" <?php echo $isAllChecked || in_array('BSc(Psych)', $eventFor) ? 'checked' : ''; ?>> Bachelor Of Science In Psychology</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="container-fluid">
                                    <div class="mb-3">
                                        <label for="exampleFormControlTextarea1" class="form-label">Enter Description</label>
                                        <textarea name="description" class="form-control" id="exampleFormControlTextarea1" rows="5" required><?php echo $row['description'] ?></textarea>
                                    </div>
                                </div>
                                <div class="container-fluid">
                                    <div class="row">
                                        <div class="mb-3">
                                            <input class="form-control" type="file" name="image" onchange="getImagePreview(event)">
                                        </div>
                                        <div class="col-md-12 mb-md-0 p-md-12" style="text-align: center;">
                                            <!-- for display image -->
                                            <img id="preview" src="data:image/jpeg;base64,<?php echo base64_encode($row['image']); ?>" alt="EVENT IMAGE">
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
                            <button type="submit" class="btn btn-warning">Update</button>
                        </div>
                        <div class="d-grid col-4 mx-auto">
                            <a class="btn btn-danger" href="./event.php">Cancel</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        function getImagePreview(event) {
            var file = event.target.files[0];
            var reader = new FileReader();
            reader.onload = function(e) {
                var img = new Image();
                img.onload = function() {
                    var canvas = document.createElement('canvas');
                    var ctx = canvas.getContext('2d');
                    canvas.width = img.width;
                    canvas.height = img.height;
                    ctx.drawImage(img, 0, 0, img.width, img.height);
                    var preview = document.getElementById('preview');
                    preview.src = canvas.toDataURL('image/jpeg'); // Adjust format if needed
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }

        // script to insert image to database

        $(document).ready(function() {
            $('#insert').click(function() {
                var image_name = $('#image').val();
                if (image_name == '') {
                    alert("please Select Profile")
                    return false;
                } else {
                    var extension = $('#image').val().split('.').pop().toLowerCase();
                    if (jquery.inArray(extenssion, ['gif', 'png', 'jpg', 'jpeg']) == -1) {
                        alert("Invalid Image File")
                        $('#image').val('');
                        return false;
                    }
                }
            })
        });

        // FOR DROPDOWN CHECKLIST
        function toggleDropdown() {
            var menu = document.getElementById('eventForMenu');
            menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
        }

        function toggleSelectAll(selectAllCheckbox) {
            var checkboxes = document.querySelectorAll('.eventForCheckbox');
            checkboxes.forEach(function(checkbox) {
                checkbox.checked = selectAllCheckbox.checked;
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            var checkboxes = document.querySelectorAll('.eventForCheckbox');
            checkboxes.forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    var isAllChecked = Array.from(checkboxes).every(checkbox => checkbox.checked);
                    document.getElementById('selectAll').checked = isAllChecked;
                });
            });
        });

        // CONFIRM SUBMITION
        document.addEventListener('DOMContentLoaded', function() {
            console.log("DOM fully loaded and parsed");

            const forms = document.querySelectorAll('.addNew');

            forms.forEach(function(form) {
                console.log("Attaching event listener to form:", form);

                form.addEventListener('submit', function(event) {
                    event.preventDefault();
                    console.log("Form submit event triggered");

                    Swal.fire({
                        title: 'Are you sure you want to continue?',
                        icon: 'warning',
                        iconHtml: '<i class="fas fa-exclamation-triangle"></i>',
                        text: 'Once you proceed, this action cannot be undone.',
                        showCancelButton: true,
                        confirmButtonColor: '#e03444',
                        cancelButtonColor: '#ffc404',
                        confirmButtonText: 'Ok',
                        cancelButtonText: 'Cancel',
                        customClass: {
                            confirmButton: 'confirm-button-class',
                            cancelButton: 'cancel-button-class'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            console.log("User confirmed action");
                            form.submit(); // Submit the form if confirmed
                        } else {
                            console.log("User canceled action");
                        }
                    });
                });
            });
        });

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
    </script>
</body>

</html>