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
$schedule = "";

// get the data from form

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Show the data of alumni
    if (!isset($_GET['id'])) {
        header("location: ./event.php");
        exit;
    }
    $event_id = $_GET['id'];

    //read data from table alumni
    $sql = "SELECT * FROM event WHERE event_id=$event_id";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();

    if (!$row) {
        header("location: ./event.php");
        exit;
    }
    $event_id = $row['event_id'];
    $title = $row['title'];
    $schedule = $row['schedule'];
    $description = $row['description'];
} else {

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $event_id = $_POST['event_id'];
        $title = ucwords($_POST['title']);
        $schedule = $_POST['schedule'];
        $description = ucwords($_POST['description']);

        // for image
        if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
            $file = addslashes(file_get_contents($_FILES["image"]["tmp_name"]));
            $sql = "UPDATE event SET title='$title', schedule='$schedule', description='$description', image='$file' WHERE event_id='$event_id'";

        } else {
            $sql = "UPDATE event SET title='$title', schedule='$schedule', description='$description' WHERE event_id='$event_id'";
        }

        $result = $conn->query($sql);
        echo "
            <script>
                // Wait for the document to load
                document.addEventListener('DOMContentLoaded', function() {
                    // Use SweetAlert2 for the alert
                    Swal.fire({
                            title: 'Event Updated Successfully',
                            timer: 2000,
                            showConfirmButton: true, // Show the confirm button
                            confirmButtonColor: '#4CAF50', // Set the button color to green
                            confirmButtonText: 'OK' // Change the button text if needed
                    }).then(function() {
                        // Redirect after the alert closes
                        window.location.href = './event.php';
                    });
                });
            </script>
            ";
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        #preview {
            max-width: 700px;
            max-height: 700px;
            object-fit: contain;
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
            <form method="POST" enctype="multipart/form-data" onsubmit="return submitForm(this);">
                <div class="container-fluid" id="page-content">
                    <div class="row">
                        <div class="container-fluid" id="main-container">
                            <div class="container-fluid" id="content-container">
                                <h3 style="margin-bottom: 2%;">Add New Event</h3>
                                <div class="mb-3">
                                    <input type="hidden" name="event_id" class="form-control" id="formGroupExampleInput" value="<?php echo $event_id; ?>">
                                    <label for="formGroupExampleInput" class="form-label">Event Title</label>
                                    <input type="text" name="title" class="form-control" id="formGroupExampleInput" required value="<?php echo $title; ?>">
                                </div>
                                <div class="mb-3">
                                    <label for="formGroupExampleInput2" class="form-label">Schedule</label>
                                    <input type="datetime-local" name="schedule" class="form-control" id="formGroupExampleInput2" required value="<?php echo $schedule; ?>">
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
    <!-- <script>
        let eventPic = document.getElementById("event-pic");
        let formFile = document.getElementById("formFile");

        formFile.onchange = function() {
            eventPic.src = URL.createObjectURL(formFile.files[0]);
        }
    </script> -->
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