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
// FOR PROFILE IMAGE
//read data from table alumni
$sql = "SELECT * FROM alumni WHERE alumni_id=$account";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

$file = $row['picture'];

$file = "";
$myId = "";

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Show the data of alumni
    if (!isset($_GET['id'])) {
        header("location: ./alumni.php");
        exit;
    }
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
    $file = $row['picture'];
} else {

    $alumni_id = $_POST['id'];
    // for image
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $file = addslashes(file_get_contents($_FILES["image"]["tmp_name"]));
        $sql = "UPDATE alumni SET picture='$file' WHERE alumni_id=$alumni_id";
    }

    $result = $conn->query($sql);
    sleep(2);
    header("location: ./profile.php?id=$alumni_id");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
    <title>Update Alumni Profile</title>
    <link rel="shortcut icon" href="../../assets/cvsu.png" type="image/svg+xml">
    <link rel="stylesheet" href="./css/update_profile.css">
    <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
    <link rel="stylesheet" href="	https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="	https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
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
                <h4><?php echo $user['fname']; ?></h4>
                <small style="color: white;"><?php echo $user['email']; ?></small>
            </div>

            <div class="side-menu">
                <ul>
                    <li>
                        <a href="../dashboard_user.php">
                            <span class="las la-home" style="color:#fff"></span>
                            <small>DASHBOARD</small>
                        </a>
                    </li>
                    <li>
                        <a href="./update_profile.php" class="active">
                            <span class="las la-user-alt" style="color:#fff"></span>
                            <small>PROFILE</small>
                        </a>
                    </li>
                    <li>
                        <a href="../event/event.php">
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
                    <span>Update Profile</span>
                </div>
                <div class="container" id="content">
                    <!-- PROFILE -->
                    <form action="" method="POST" enctype="multipart/form-data"> <!-- class="addNew" -->
                        <div class="container text-center" id="start">
                            <div class="row align-items-end">
                                <div class="col">
                                    <!-- Preview image -->
                                    <div class="form-control" style="width:445px;height:435px; border-radius: 100%;">
                                        <img id="previewTWO" src="data:image/jpeg;base64,<?php echo base64_encode($row['picture']); ?>" style="width:420px;height:420px; border-radius: 100%;">
                                    </div>
                                </div>
                            </div>
                            <div class="row align-items-end" style="margin-top:5%;">
                                <div class="col">
                                    <input type="hidden" name="id" value="<?php echo $alumni_id; ?>">
                                    <input class="form-control" type="file" name="image" required onchange="getImagePreview(event)">
                                </div>
                                <div class="col">
                                    <button type="submit" class="btn btn-warning" name="insert" id="insert" value="insert" style="padding-left: 70px; padding-right: 70px; margin-right: 7%;">Update</button>
                                    <?php
                                    echo "
                                        <a class='btn btn-danger' href='./profile.php' style='padding-left: 70px; padding-right: 70px;'>Cancel</a>
                                        "; ?>

                                </div>
                            </div>
                        </div>
                </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Script to display preview of selected image -->
    <script>
        var image = URL.createObjectURL(event.target.files[0]);
        var preview = document.getElementById('preview');
        preview.src = image;
        preview.style.width = '420px';
        preview.style.height = '420px';
    </script>
    <!-- Script to display preview of selected image -->
    <script>
        function getImagePreview(event) {
            var image = URL.createObjectURL(event.target.files[0]);
            var preview = document.getElementById('previewTWO');
            preview.src = image;
            preview.style.width = '420px';
            preview.style.height = '420px';
        }
    </script>
    <!-- script to insert image to database -->
    <Script>
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
    </Script>
</body>

</html>