<?php
session_start();
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


    //insert data into table alumni_archive from alumni
    $sql_restore = "INSERT INTO alumni (alumni_id, student_id, fname, mname, lname, gender, course, batch_startYear, batch_endYear, contact, address, email, recovery_email, password, last_login, picture, date_created, status)
    SELECT alumni_id, student_id, fname, mname, lname, gender, course, batch_startYear, batch_endYear, contact, address, email, recovery_email, password, last_login, picture, date_created, 'Unverified'
    FROM alumni_archive WHERE alumni_id = $alumni_id";
    $conn->query($sql_restore);

    // $stmt = $conn->prepare("UPDATE alumni SET status = 'Unverified' WHERE alumni_id = ?");
    // $stmt->bind_param("s", $alumni_id);
    // $stmt->execute();
    // $stmt->close();

    //delete data in table alumni
    $sql_delete = "DELETE FROM alumni_archive WHERE alumni_id=$alumni_id";
    $conn->query($sql_delete);
}
$transfer = $alumni_id;
header("Location: ./alumni_archive.php?ide=$transfer");
exit;
