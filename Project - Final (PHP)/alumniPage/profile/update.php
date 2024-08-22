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

$contact = "";
$address = "";

$sql = "SELECT * FROM alumni WHERE alumni_id=$account";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

$file = $row['picture'];


if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Show the data of alumni
    if (!isset($_GET['id'])) {
        header("location: ./profile.php");
        exit;
    }
    $alumni_id = $_GET['id'];

    //read data from table alumni
    $sql = "SELECT * FROM alumni WHERE alumni_id=$alumni_id";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();

    if (!$row) {
        header("location: ./profile.php");
        exit;
    }

    $contact = $row['contact'];
    $address_parts = explode(' ', $row['address']);

    // Assign from the end of the array
    $province = array_pop($address_parts); // Cavite
    $city = array_pop($address_parts);     // Dasmarinas
    $brgy = array_pop($address_parts);     // Sabang

    // The remaining parts will be combined into the house_no
    $house_no = implode(' ', $address_parts);
} else {

    $contact = $_POST['contact'];
    $house_no = ucwords($_POST['house_no']);
    $brgy = ucwords($_POST['brgy']);
    $city = ucwords($_POST['city']);
    $province = ucwords($_POST['province']);

    $address = ucwords($_POST['house_no']) . ' ' . ucwords($_POST['brgy']) . ' ' . ucwords($_POST['city']) . ' ' . ucwords($_POST['province']);

    $sql = "UPDATE alumni SET contact='$contact', address='$address' WHERE alumni_id=$account";
    $result = $conn->query($sql);

    $icon = 'success';
    $iconHtml = '<i class="fas fa-check-circle"></i>';
    $title = 'Info updated successfully';
    $redirectUrl = './profile.php';

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
    <title>Update alumni Info</title>
    <link rel="shortcut icon" href="../../assets/cvsu.png" type="image/svg+xml">
    <link rel="stylesheet" href="css/update_info.css">
    <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="	https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
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
                        <a href="./update.php" class="active">
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
                <h1><strong>Profile</strong></h1>
            </div>
            <div class="page-content">
                <div class="row">
                    <div class="container-fluid" id="main-container">
                        <div class="container-fluid" id="content-container">
                            <div class="information">
                                <form action="update.php" method="POST" class="addNew">
                                    <div class="mb-3">
                                        <label for="formGroupExampleInput" class="form-label">CONTACT</label>
                                        <input type="text" id="student_id" name="contact" class="form-control" id="formGroupExampleInput" value="<?php echo htmlspecialchars("$contact"); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="formGroupExampleInput" class="form-label">ADDRESS</label>
                                        <br>
                                        <label for="formGroupExampleInput" class="form-label">House No. | Street | Subdivision</label>
                                        <input type="text" name="house_no" class="form-control" id="formGroupExampleInput" value="<?php echo htmlspecialchars("$house_no"); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="formGroupExampleInput" class="form-label">Barangay</label>
                                        <input type="text" name="brgy" class="form-control" id="formGroupExampleInput" value="<?php echo htmlspecialchars("$brgy"); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="formGroupExampleInput" class="form-label">City</label>
                                        <input type="text" name="city" class="form-control" id="formGroupExampleInput" value="<?php echo htmlspecialchars("$city"); ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="formGroupExampleInput" class="form-label">Province</label>
                                        <input type="text" name="province" class="form-control" id="formGroupExampleInput" value="<?php echo htmlspecialchars("$province"); ?>">
                                    </div>
                                    <div class="buttons">
                                        <button type="submit" class="btn" id="button1" value="Update">UPDATE</button>
                                        <a href="./profile.php"><button type="button" class="btn" id="button1">CANCEL</button></a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <!-- Script to display preview of selected image -->
            <script>
                function getImagePreview(event) {
                    var image = URL.createObjectURL(event.target.files[0]);
                    var preview = document.getElementById('preview');
                    preview.src = image;
                    preview.style.width = '83px';
                    preview.style.height = '83px';
                }

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

                // FOR NO NIGGATIVE NUMBERS
                document.addEventListener("DOMContentLoaded", function() {
                    const studentIdInput = document.getElementById("student_id");

                    studentIdInput.addEventListener("input", function(event) {
                        let value = studentIdInput.value;
                        // Replace all non-numeric characters
                        value = value.replace(/[^0-9]/g, '');
                        studentIdInput.value = value;
                    });
                });
            </script>
</body>

</html>