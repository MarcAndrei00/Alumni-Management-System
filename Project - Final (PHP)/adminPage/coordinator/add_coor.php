<?php
session_start();

// IMPORTANT CODE ---------------
use PHPMailer\PHPMailer\PHPMailer;

require '../../vendor/autoload.php';

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

$fname = "";
$mname = "";
$lname = "";
$contact = "";
$email = "";
$temp_password = "";

// get the data from form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fname = ucwords($_POST['fname']);
    $mname = ucwords($_POST['mname']);
    $lname = ucwords($_POST['lname']);
    $contact = $_POST['contact'];
    $email = strtolower($_POST['email']);

    // email and user existing check
    $emailCheck = mysqli_query($conn, "SELECT * FROM coordinator WHERE email='$email'");
    $emailCheck_archive = mysqli_query($conn, "SELECT * FROM coordinator_archive WHERE email='$email'");

    if (mysqli_num_rows($emailCheck) > 0 || mysqli_num_rows($emailCheck_archive) > 0) {
        // WARNING EXISTING ACCOUNT
        $icon = 'warning';
        $iconHtml = '<i class="fas fa-exclamation-triangle"></i>';
        $title = 'Email Already Exists!';
        $text = 'Please try again.';

        echo "<script>
              document.addEventListener('DOMContentLoaded', function() {
                  warningError('$title', '$text', '$icon', '$iconHtml');
              });
          </script>";
        sleep(2);
    } else {
        $sql = "INSERT INTO coordinator SET fname='$fname', mname='$mname', lname='$lname', contact='$contact', email='$email'";
        $result = $conn->query($sql);

        $tempPass = sprintf("%06d", mt_rand(1, 999999));
        $stmt = $conn->prepare("UPDATE coordinator SET password = '$tempPass' WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->close();
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'alumni.management07@gmail.com'; //NOTE gawa ka ng new email account nyo gaya nito, yan kasi ang magiging bridge/ sya ang mag sesend ng email
        $mail->Password   = 'kcio bmde ffvc sfar';           // di ako sude dito pero eto ata ung password ng email / pagdi tanong mo nalang kay Nyel
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;  // pwede nyo rin naman gamitin nayang email namin pero hingi kalang muna permission kay dhaniel pre, 
        $mail->Port       = 587;

        $mail->setFrom('alumni.management07@gmail.com', 'Alumni Management'); // eto ung email at yung name ng email na makikita sa una
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Account Update'; // eto ung mga laman ng email na isesend
        $mail->Body    = 'Your account has been created by alumni administrator.
                    <br>Your Email:<b>' . $email . '</b>.
                    <br>Your Temporary password:<b>' . $tempPass . '</b>.
                    <br><br>Do not forget to change password once you login.
                    <br>Thank you and have a nice day. 
                    <br><br>This is an automated message please do not reply.';
        $mail->AltBody = 'Your account has been registered by alumni administrator.';

        $mail->send();

        $icon = 'success';
        $iconHtml = '<i class="fas fa-check-circle"></i>';
        $title = 'Coordinator added Successfully.';
        $redirectUrl = './coordinator.php';

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
    <title>Add New Coordinator</title>
    <link rel="shortcut icon" href="../../assets/cvsu.png" type="image/svg+xml">
    <link rel="stylesheet" href="./css/add_coor.css">
    <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
    <link rel="stylesheet" href="	https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="	https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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

        /* FOR SWEETALERT  END LINE*/
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
                        <a href="./add_coor.php" class="active">
                            <span class="las la-user-cog" style="color:#fff"></span>
                            <small>COORDINATOR</small>
                        </a>
                    </li>
                    <li>
                        <a href="../event/event.php">
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
                <h1><strong>Coordinator</strong></h1>
            </div>
        </main>
        <div class="container" id="container-full">
            <div class="container" id="content-container">
                <div class="container-title">
                    <span>Add New Account</span>
                </div>
                <div class="container" id="content">
                    <form action="" method="POST" class="addNew" enctype="multipart/form-data">
                        <div class="container">
                            <div class="row align-items-end">
                                <div class="col">
                                    <label class="col-sm-3 col-form-label" style="font-size: 20px;" for="first-name">First Name:</label>
                                </div>

                                <div class="col">
                                    <input class="form-control" type="text" id="name" name="fname" placeholder="First Name" required value="<?php echo $fname; ?>">
                                </div>
                            </div>
                        </div>
                        <div class="container">
                            <div class="row align-items-end">
                                <div class="col">
                                    <label class="col-sm-3 col-form-label" style="font-size: 20px;" for="middle-name">Middle Name:</label>
                                </div>

                                <div class="col">
                                    <input class="form-control" type="text" id="name" name="mname" placeholder="Middle Name" value="<?php echo $mname; ?>">
                                </div>
                            </div>
                        </div>
                        <div class="container">
                            <div class="row align-items-end">
                                <div class="col">
                                    <label class="col-sm-3 col-form-label" style="font-size: 20px;" for="last-name">Last Name:</label>
                                </div>
                                <div class="col">
                                    <input class="form-control" type="text" id="name" name="lname" placeholder="Last Name" required value="<?php echo $lname; ?>">
                                </div>
                            </div>
                        </div>
                        <div class="container">
                            <div class="row align-items-end">
                                <div class="col">
                                    <label class="col-sm-3 col-form-label" style="font-size: 20px;" for="name">Contact:</label>
                                </div>
                                <div class="col">
                                    <input class="form-control" type="number" id="name" name="contact" placeholder="Enter Phone No." required value="<?php echo $contact; ?>">
                                </div>
                            </div>
                        </div>
                        <div class="container">
                            <div class="row align-items-end">
                                <div class="col">
                                    <label class="col-sm-3 col-form-label" style="font-size: 20px;" for="name">Email:</label>
                                </div>
                                <div class="col">
                                    <input class="form-control" type="email" id="email" name="email" placeholder="Enter Email" required value="<?php echo $email; ?>">
                                </div>
                            </div>
                        </div>
                        <div class="container">
                            <div class="row" style="margin-top:20px;">
                                <div class="col" id="buttons">
                                    <div class="button">
                                        <button type="submit" class="btn btn-warning" name="submit" id="insert" value="submit">Add new</button>
                                        <a class="btn btn-danger" href="./coordinator.php">Cancel</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- SCRIPT FOR DATCH SELECTOR -->
    <script>
        // Ensure SweetAlert2 is loaded
        // document.addEventListener('DOMContentLoaded', function() {
        //     console.log("DOM fully loaded and parsed");

        //     const forms = document.querySelectorAll('.addNew');

        //     forms.forEach(function(form) {
        //         console.log("Attaching event listener to form:", form);

        //         form.addEventListener('submit', function(event) {
        //             event.preventDefault();
        //             console.log("Form submit event triggered");

        //             Swal.fire({
        //                 title: 'Are you sure you want to continue?',
        //                 icon: 'warning',
        //                 iconHtml: '<i class="fas fa-exclamation-triangle"></i>',
        //                 text: 'Once you proceed, this action cannot be undone.',
        //                 showCancelButton: true,
        //                 confirmButtonColor: '#e03444',
        //                 cancelButtonColor: '#ffc404',
        //                 confirmButtonText: 'Ok',
        //                 cancelButtonText: 'Cancel',
        //                 customClass: {
        //                     confirmButton: 'confirm-button-class',
        //                     cancelButton: 'cancel-button-class'
        //                 }
        //             }).then((result) => {
        //                 if (result.isConfirmed) {
        //                     console.log("User confirmed action");
        //                     form.submit(); // Submit the form if confirmed
        //                 } else {
        //                     console.log("User canceled action");
        //                 }
        //             });
        //         });
        //     });
        // });


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