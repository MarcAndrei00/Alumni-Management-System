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
$title = "";
$address = "";
$contact = "";
$email = "";

// Read data from the table 'about_page'
$data_sql = "SELECT * FROM contact_page WHERE contact_id=1";
$data_result = $conn->query($data_sql);

if ($data_result->num_rows > 0) {
    $data_row = $data_result->fetch_assoc();
    $data_title = $data_row['page_title'];
    $data_address = $data_row['address'];
    $data_contact = $data_row['contact'];
    $data_email = $data_row['email'];
} else {
    header("Location: ./contact.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = ucwords($conn->real_escape_string($_POST['title']));
    $address = ucwords($conn->real_escape_string($_POST['address']));
    $contact = $_POST['contact'];
    $email = strtolower($conn->real_escape_string($_POST['email']));

    // For image
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $file = addslashes(file_get_contents($_FILES["image"]["tmp_name"]));
        $sql = "UPDATE contact_page SET page_title='$title', address='$address', contact='$contact', email='$email', image='$file' WHERE contact_id=1";
    } else {
        $sql = "UPDATE contact_page SET page_title='$title', address='$address', contact='$contact', email='$email' WHERE contact_id=1";
    }

    if ($conn->query($sql) === TRUE) {
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
                        window.location.href = './contact.php';
                    });
                });
            </script>
            ";
    } else {
        echo "Error updating record: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
    <title>Contact Info</title>
    <link rel="shortcut icon" href="../../assets/cvsu.png" type="image/svg+xml">
    <link rel="stylesheet" href="./css/contact.css">
    <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
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
            <h3><img src="https://cvsu-imus.edu.ph/student-portal/assets/images/logo-mobile.png"><span>CVSU</span></h3>
        </div>
        <div class="side-content">
            <div class="profile">
                <i class="bi bi-person-circle"></i>
                <h4><?php echo $user['fname']; ?></h4>
                <small style="color: white;"><?php echo $user['email']; ?></small>
            </div>
            <div class="side-menu">
                <ul>
                    <li><a href="../dashboard_coor.php"><span class="las la-home" style="color:#fff"></span><small>DASHBOARD</small></a></li>
                    <li><a href="../profile/profile.php"><span class="las la-user-alt" style="color:#fff"></span><small>PROFILE</small></a></li>
                    <li><a href="../alumni/alumni.php"><span class="las la-th-list" style="color:#fff"></span><small>ALUMNI</small></a></li>
                    <li><a href="../event/event.php"><span class="las la-calendar" style="color:#fff"></span><small>EVENT</small></a></li>
                    <li><a href="./contat.php" class="active"><span class="las la-cog" style="color:#fff"></span><small>SETTINGS</small></a></li>
                    <li><a href="../report/report.php"><span class="las la-clipboard-check" style="color:#fff"></span><small>REPORT</small></a></li>
                    <li><a href="../archive/alumni_archive.php"><span class="las la-archive" style="color:#fff"></span><small>ARCHIVE</small></a></li>
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
                <h1><strong>Settings</strong></h1>
            </div>
            <div class="form-style">
                <div class="d-flex justify-content-end my-3">
                    <ul class="nav nav-pills custom-nav-pills" id="myTab" role="tablist">
                        <li class="nav-item mx-4">
                            <button class="btn btn-light border border-dark" id="contact-tab" type="button" role="tab" aria-controls="contact" aria-selected="false" onclick="location.href='about.php'" style="padding-left: 55px; padding-right: 55px;">About</button>
                        </li>
                        <li class="nav-item mx-4">
                            <button class="btn btn-secondary border border-dark" id="about-tab" type="button" role="tab" aria-controls="contact" aria-selected="true" onclick="location.href='contact.php'" style="padding-left: 48px; padding-right: 48px;">Contact</button>
                        </li>
                    </ul>
                </div>

                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="contact" role="tabpanel" aria-labelledby="contact-tab">
                        <form method="POST" enctype="multipart/form-data" onsubmit="return submitForm(this);">
                            <div class="mb-3">
                                <label for="pageTitle" class="form-label">Page Title</label>
                                <input name="title" type="text" class="form-control" id="pageTitle" value="<?php echo $data_title; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="address" class="form-label">Address</label>
                                <input name="address" type="text" class="form-control" id="address" value="<?php echo $data_address; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="contact" class="form-label">Contact</label>
                                <input name="contact" type="number" class="form-control" id="contact" value="<?php echo $data_contact; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input name="email" type="email" class="form-control" id="email" value="<?php echo $data_email; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="upload" class="form-label">Upload</label>
                                <input class="form-control" type="file" name="image" onchange="getImagePreview(event)">
                                <!-- <div class="mt-3">
                                    <img src="/mnt/data/image.png" alt="Upload Image" class="img-thumbnail">
                                </div> -->
                                <div class="mt-3 col-md-12 mb-md-0 p-md-12" style="text-align: center;">
                                    <!-- for display image -->
                                    <img id="preview" src="data:image/jpeg;base64,<?php echo base64_encode($data_row['image']); ?>" alt="EVENT IMAGE">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-warning" style="padding-left: 50px; padding-right: 50px;">Update</button>
                        </form>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
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