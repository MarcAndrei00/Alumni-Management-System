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
$address = "";
$contact = "";
$email = "";

// Read data from the table 'about_page'
$data_sql = "SELECT * FROM contact_page WHERE contact_id=1";
$data_result = $conn->query($data_sql);

if ($data_result->num_rows > 0) {
    $data_row = $data_result->fetch_assoc();
    $data_title = $data_row['page_title'];
    $data_contact = $data_row['contact'];
    $data_email = $data_row['email'];
    $address_parts = explode(', ', $data_row['address']);

    // Assign from the end of the array
    $province = array_pop($address_parts); // Cavite
    $city = array_pop($address_parts);     // Dasmarinas
    $brgy = array_pop($address_parts);     // Sabang

    // The remaining parts will be combined into the house_no
    $house_no = implode(' ', $address_parts);
} else {
    header("Location: ./contact.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = strtoupper($conn->real_escape_string($_POST['title']));
    $contact = $_POST['contact'];
    $email = strtolower($conn->real_escape_string($_POST['email']));

    $house_no = ucwords($_POST['house_no']);
    $brgy = ucwords($_POST['brgy']);
    $city = ucwords($_POST['city']);
    $province = ucwords($_POST['province']);

    $address = ucwords($_POST['house_no']) . ', ' . ucwords($_POST['brgy']) . ', ' . ucwords($_POST['city']) . ', ' . ucwords($_POST['province']);

    $sql = "UPDATE contact_page SET page_title='$title', address='$address', contact='$contact', email='$email' WHERE contact_id=1";

    if ($conn->query($sql) === TRUE) {
        $icon = 'success';
        $iconHtml = '<i class="fas fa-check-circle"></i>';
        $title = 'Info updated successfully.';
        $redirectUrl = './about.php';

        echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    noTextRedirect('$redirectUrl', '$title', '$icon', '$iconHtml');
                });
            </script>";
        sleep(2);
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        #preview {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

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
                    <li><a href="../dashboard_admin.php"><span class="las la-home" style="color:#fff"></span><small>DASHBOARD</small></a></li>
                    <li><a href="../profile/profile.php"><span class="las la-user-alt" style="color:#fff"></span><small>PROFILE</small></a></li>
                    <li><a href="../alumni/alumni.php"><span class="las la-th-list" style="color:#fff"></span><small>ALUMNI</small></a></li>
                    <li><a href="../coordinator/coordinator.php"><span class="las la-user-cog" style="color:#fff"></span><small>COORDINATOR</small></a></li>
                    <li><a href="../event/event.php"><span class="las la-calendar" style="color:#fff"></span><small>EVENT</small></a></li>
                    <li><a href="./about.php" class="active"><span class="las la-cog" style="color:#fff"></span><small>SETTINGS</small></a></li>
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
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="contact" role="tabpanel" aria-labelledby="contact-tab">
                        <form method="POST" enctype="multipart/form-data" class="addNew">
                            <div class="mb-3">
                                <label for="pageTitle" class="form-label">Page Title</label>
                                <input name="title" type="text" class="form-control" id="pageTitle" value="<?php echo $data_title; ?>">
                            </div>
                            <div class="row g-3">
                                <div class="col">
                                    <label for="house_no" class="form-label">House No. | Street | Subdivision</label>
                                    <input type="text" name="house_no" class="form-control" id="house_no" required value="<?php echo htmlspecialchars($house_no); ?>">
                                </div>
                                <div class="col">
                                    <label for="brgy" class="form-label">Barangay</label>
                                    <input type="text" name="brgy" class="form-control" id="brgy" required value="<?php echo htmlspecialchars($brgy); ?>">
                                </div>
                                <div class="col">
                                    <label for="city" class="form-label">City</label>
                                    <input type="text" name="city" class="form-control" id="city" required value="<?php echo htmlspecialchars($city); ?>">
                                </div>
                                <div class="col">
                                    <label for="province" class="form-label">Province</label>
                                    <input type="text" name="province" class="form-control" id="province" required value="<?php echo htmlspecialchars($province); ?>">
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="contact" class="form-label">Contact</label>
                                <input name="contact" id="student_id" type="text" class="form-control" id="contact" value="<?php echo $data_contact; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input name="email" type="email" class="form-control" id="email" value="<?php echo $data_email; ?>">
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