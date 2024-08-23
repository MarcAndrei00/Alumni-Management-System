<?php
session_start();

// IMPORTANT CODE ---------------
use PHPMailer\PHPMailer\PHPMailer;

require '../../vendor/autoload.php';

if (isset($_GET['id'])) {
    $alumni_id = $_GET['id'];

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

    // Transfer data
    $filePath = '../../assets/profile_icon.jpg';
    $imageData = file_get_contents($filePath);
    $imageDataEscaped = addslashes($imageData);

    $sql = "SELECT * FROM list_of_graduate WHERE alumni_id=$alumni_id";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $stud_id = $row["student_id"];
    $email = $row["email"];
    //insert data into table alumni_archive from alumni
    $sql_restore = "INSERT INTO alumni (student_id, fname, mname, lname, gender, course, contact, address, email, password, picture, date_created) 
                    SELECT student_id, fname, mname, lname, gender, course, contact, address, '$email', '$password', '$imageDataEscaped', date_created 
                    FROM list_of_graduate WHERE alumni_id='$alumni_id'";
    $conn->query($sql_restore);

    $batchYearRange = $row["batch"] ?? ''; // Assuming batch_years are in column 8
    $startYear = $endYear = ''; // Initialize with empty values

    if (strpos($batchYearRange, '-') !== FALSE) {
        list($startYear, $endYear) = explode('-', $batchYearRange);
        // Trim spaces
        $startYear = trim($startYear);
        $endYear = trim($endYear);
    }

    $stmt = $conn->prepare("UPDATE alumni SET batch_startYear = '$startYear', batch_endYear = '$endYear' WHERE student_id = ?");
    $stmt->bind_param("s", $stud_id);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("UPDATE alumni SET status = 'Verified' WHERE student_id = ?");
    $stmt->bind_param("s", $stud_id);
    $stmt->execute();
    $stmt->close();


    $tempPass = sprintf("%06d", mt_rand(1, 999999));
    $hashedpassword = password_hash($tempPass, PASSWORD_BCRYPT);

    $stmt = $conn->prepare("UPDATE alumni SET password = '$hashedpassword' WHERE student_id = ?");
    $stmt->bind_param("s", $stud_id);
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
    $mail->Body    = 'Your account has been registered by alumni administrator.
                    <br>Your Student Id:<b>' . $stud_id . '</b>.
                    <br>Your Email:<b>' . $email . '</b>.
                    <br>Your Temporary password:<b>' . $tempPass . '</b>.
                    <br><br>Do not forget to change password once you login.
                    <br>Thank you and have a nice day. 
                    <br><br>This is an automated message please do not reply.';
    $mail->AltBody = 'Your account has been registered by alumni administrator.';

    $mail->send();

    //delete data in table alumni
    $sql_delete = "DELETE FROM list_of_graduate WHERE student_id=$stud_id";
    $conn->query($sql_delete);
}
// Output SweetAlert2 message with a timer
$transfer = $alumni_id;
header("Location: ./list_of_graduate.php?ide=$transfer");
exit;
