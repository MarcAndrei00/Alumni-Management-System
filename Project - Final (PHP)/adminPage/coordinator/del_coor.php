<?php
session_start();

if (isset($_GET['id'])) {
    $coor_id = $_GET['id'];

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

    //insert data into table alumni_archive from alumni
    $sql_archive = "INSERT INTO coordinator_archive (coor_id, fname, mname, lname, contact, email, password, date_created)" .
        "SELECT coor_id, fname, mname, lname, contact, email, password, date_created FROM coordinator WHERE coor_id=$coor_id";
    $conn->query($sql_archive);

    //delete data in table alumni
    $sql_delete = "DELETE FROM coordinator WHERE coor_id=$coor_id";
    $conn->query($sql_delete);
}
$transfer = $coor_id;
header("Location: ./coordinator.php?ide=$transfer");
exit;
?>