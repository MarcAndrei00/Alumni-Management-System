<?php
session_start();

if (isset($_GET['id'])) {
    $coor_id = $_GET['id'];

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


    //insert data into table alumni_archive from alumni
    $sql_archive = "INSERT INTO coordinator (coor_id, fname, mname, lname, contact, email, password, date_created)" .
        "SELECT coor_id, fname, mname, lname, contact, email, password, date_created FROM coordinator_archive WHERE coor_id=$coor_id";
    $conn->query($sql_archive);

    //delete data in table alumni
    $sql_delete = "DELETE FROM coordinator_archive WHERE coor_id=$coor_id";
    $conn->query($sql_delete);
}
$transfer = $coor_id;
header("Location: ./coor_archive.php?ide=$transfer");
exit;
