<?php
session_start();

if (isset($_GET['id'])) {
    $event_id = $_GET['id'];

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
            $user = $user_result->fetch_assoc();
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
    // Close the database connection if needed
    // $conn->close();
    //insert data into table alumni_archive from alumni
    $sql_archive = "INSERT INTO event (event_id, title, date, start_time, end_time, venue, address, description, image, going, interested, not_interested, date_created)" .
        "SELECT event_id, title, date, start_time, end_time, venue, address, description, image, going, interested, not_interested, date_created FROM event_archive WHERE event_id=$event_id";
    $conn->query($sql_archive);

    //delete data in table alumni
    $sql_delete = "DELETE FROM event_archive WHERE event_id=$event_id";
    $conn->query($sql_delete);
}
$transfer = $event_id;
header("Location: ./event_archive.php?ide=$transfer");
exit;
