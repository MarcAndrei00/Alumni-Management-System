<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php
session_start();

if (isset($_GET['id'])) {
    $event_id = $_GET['id'];

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


    // Insert data into table event_archive from event
    $sql_archive = "INSERT INTO event_archive (event_id, title, schedule, description, image, going, interested, not_interested, date_created)" .
        " SELECT event_id, title, schedule, description, image, going, interested, not_interested, date_created FROM event WHERE event_id=$event_id";
    $conn->query($sql_archive);

    // Delete data from table event
    $sql_delete = "DELETE FROM event WHERE event_id=$event_id";
    $conn->query($sql_delete);
}
// Output SweetAlert2 message with a timer
$transfer = $event_id;
header("Location: ./event.php?ide=$transfer");
exit;
?>