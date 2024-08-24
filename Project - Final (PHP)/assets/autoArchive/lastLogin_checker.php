<?php
session_start();
$conn = new mysqli("localhost", "root", "", "alumni_management_system");

// IMPORTANT CODE ---------------
use PHPMailer\PHPMailer\PHPMailer;
require './vendor/autoload.php';

$_SESSION['checker'] = 'stopLoad';
header('Location: ./homepage.php');
// FOR ALUMNI 
$sql = "SELECT * FROM alumni";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $timezone = new DateTimeZone('Asia/Manila');
    while ($row = $result->fetch_assoc()) {
        $eventDatetime = new DateTime($row["last_login"], $timezone);
        $activity = $row["status"];

        $currentDateTime = new DateTime('now', $timezone);

        $interval = $currentDateTime->diff($eventDatetime);
        $formattedDateCreated = $eventDatetime->format('Y-m-d');
        if (($interval->days >= 10 && $interval->invert == 1) && ($activity === 'Verified')) {
            
            // echo "Status: Inactive for last 10 days or Above (Last login: $formattedDateCreated) <br>";
            $alumni_id = $row["alumni_id"];
            //insert data into table alumni_archive from alumni
            $sql_archive = "INSERT INTO alumni_archive (alumni_id, student_id, fname, mname, lname, gender, course, batch_startYear, batch_endYear, contact, address, email, password, last_login, picture, date_created)" .
                "SELECT alumni_id, student_id, fname, mname, lname, gender, course, batch_startYear, batch_endYear, contact, address, email, password, last_login, picture, date_created FROM alumni WHERE alumni_id=$alumni_id";
            $conn->query($sql_archive);

            $stmt = $conn->prepare("UPDATE alumni_archive SET status = 'Inactive' WHERE alumni_id = ?");
            $stmt->bind_param("s", $alumni_id);
            $stmt->execute();
            $stmt->close();

            // SEND EMAIL TO INFORM ALUMNI
            $sql = "SELECT * FROM alumni WHERE alumni_id=$alumni_id";
            $result = $conn->query($sql);
            $row = $result->fetch_assoc();
            $stud_id = $row["student_id"];
            $email = $row["email"];

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
            $mail->Body    = 'Your account has been updated to <b>Inactive</b>.
                              <br>Please login and verify your account to active again.
                              <br>Thank you and have a nice day.
                              <br><br>This is an automated message please do not reply.';
            $mail->AltBody = 'Your account has been updated to <b>Inactive</b> by alumni administrator.';

            $mail->send();

            //delete data in table alumni
            $sql_delete = "DELETE FROM alumni WHERE alumni_id=$alumni_id";
            $conn->query($sql_delete);
        }
    }
}
