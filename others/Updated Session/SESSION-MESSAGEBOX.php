<?php
// NORMAL SESSION
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
        header('Location: ./adminPage/dashboard_admin.php');
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
        header('Location: ./coordinatorPage/dashboard_coor.php');
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
            // User is a verified alumni
            header('Location: ./alumniPage/dashboard_user.php');
            exit();
        } else {

            $_SESSION['email'] = $account_email;

            // WARNING NOT VERIFIED
            $icon = 'warning';
            $iconHtml = '<i class="fas fa-exclamation-triangle"></i>';
            $title = 'Account Not Verified!';
            $text = 'Verified your Account First to continue.';
            $redirectUrl = './verification_code.php';

            echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        alertMessage('$redirectUrl', '$title', '$text', '$icon', '$iconHtml');
                    });
                </script>";
        }
    } else {
        // Redirect to login if no matching user found
        session_destroy();
        header('Location: ./homepage.php');
        exit();
    }
}





// DASHBOARD SESSION
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
        header('Location: ../coordinatorPage/dashboard_coor.php');
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
        header("Location: ../homepage.php");
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
            header('Location: ../alumniPage/dashboard_user.php');
            exit();
        } else {

            $_SESSION['email'] = $account_email;

            // WARNING NOT VERIFIED
            $icon = 'warning';
            $iconHtml = '<i class="fas fa-exclamation-triangle"></i>';
            $title = 'Account Not Verified!';
            $text = 'Verified your Account First to continue.';
            $redirectUrl = '../loginPage/verification_code.php';

            echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        alertMessage('$redirectUrl', '$title', '$text', '$icon', '$iconHtml');
                    });
                </script>";
        }
    }
} else {
    // Redirect to login if no matching user found
    session_destroy();
    header('Location: ../homepage.php');
    exit();
}





// MESSAGEBOX
// SUCCESS LOGIN ADMIN
$icon = 'success';
$iconHtml = '<i class="fas fa-check-circle"></i>';
$title = 'Login Successful!';
$text = 'You will be redirected shortly to the Dashboard.';
$redirectUrl = '../adminPage/dashboard_admin.php';

echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            alertMessage('$redirectUrl', '$title', '$text', '$icon', '$iconHtml');
        });
    </script>";
sleep(2);

// SUCCESS WITHOUT TEXT AND REDIRECT
$icon = 'success';
$iconHtml = '<i class="fas fa-check-circle"></i>';
$title = 'Account Archived Successfully';

echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            noTextMessage('$title', '$icon', '$iconHtml');
        });
    </script>";
sleep(2);


// WARNING INACTIVE
$icon = 'warning';
$iconHtml = '<i class="fas fa-exclamation-triangle"></i>';
$title = 'Your Account is Inactive!';
$text = 'We send a verification code to your email to active your account.';
$redirectUrl = './inactiveVerification.php';

echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            alertMessage('$redirectUrl', '$title', '$text', '$icon', '$iconHtml');
        });
    </script>";


// WARNING NOT VERIFIED
$icon = 'warning';
$iconHtml = '<i class="fas fa-exclamation-triangle"></i>';
$title = 'Account Not Verified!';
$text = 'Verified your Account First to continue.';
$redirectUrl = './verification_code.php';

echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            alertMessage('$redirectUrl', '$title', '$text', '$icon', '$iconHtml');
        });
    </script>";


// ERROR LOGIN FAILED - NO REDIRECT
$icon = 'error';
$iconHtml = '<i class=\"fas fa-exclamation-circle\"></i>';
$title = 'Incorrect Student ID / Email and Password!';
$text = 'Please try again.';

echo "<script>
      document.addEventListener('DOMContentLoaded', function() {
          warningError('$title', '$text', '$icon', '$iconHtml');
      });
  </script>";
sleep(2);


// WARNING EXISTING ACCOUNT - NO REDIRECT
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


// WARNING EXISTING ACCOUNT - NO REDIRECT
$icon = 'warning';
$iconHtml = '<i class="fas fa-exclamation-triangle"></i>';
$title = 'Student ID Already Exists!';
$text = 'Please try again.';

echo "<script>
      document.addEventListener('DOMContentLoaded', function() {
          warningError('$title', '$text', '$icon', '$iconHtml');
      });
  </script>";
sleep(2);


// WARNING NOT MATCH PASSWORD
$icon = 'warning';
$iconHtml = '<i class="fas fa-exclamation-triangle"></i>';
$title = 'Password do not match!';
$text = 'Please try again.';

echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            warningError('$title', '$text', '$icon', '$iconHtml');
        });
    </script>";
sleep(2);


// WARNING NOT VERIFIED
$icon = 'success';
$iconHtml = '<i class="fas fa-check-circle"></i>';
$title = 'Account successfully register.';
$text = 'We send a verification code to your email to verify your account.';
$redirectUrl = './verification_code.php';

echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            alertMessage('$redirectUrl', '$title', '$text', '$icon', '$iconHtml');
        });
    </script>";


// WARNING NO ALUMNI
$icon = 'warning';
$iconHtml = '<i class="fas fa-exclamation-triangle"></i>';
$title = 'There is no alumni with student ID ' . $stud_id;
$text = 'Please try again.';

echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                warningError('$title', '$text', '$icon', '$iconHtml');
            });
        </script>";
sleep(2);


// FOR MESSAGEBOX WITHOUT TEXT AND REDIRECT
$icon = 'success';
$iconHtml = '<i class="fas fa-check-circle"></i>';
$title = 'Account archived successfully';

echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            noTextMessage('$title', '$icon', '$iconHtml');
        });
    </script>";
sleep(2);


// FOR MESSAGEBOX WITHOUT TEXT ONLY
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
?>
<!-- SCRIPT -->
<!-- PUT THIS ON HEAD TAG THE LINK TAGS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
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

    // FOR MESSAGEBOX WITHOUT TEXT AND REDIRECT
    function noTextMessage(title, icon, iconHtml) {
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




<!-- FOR NO NIGGATIVE NUMBERS -->
<div class="infield">
    <input type="text" id="student_id" placeholder="Student ID" name="student_id" value="<?php echo htmlspecialchars($stud_id); ?>" required />
    <label></label>
</div>
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
</script>




<!-- PASS VISIBILITY -->
<div class="infield" style="position: relative;">
    <input type="password" placeholder="Password" id="password" name="password" value="<?php echo htmlspecialchars($password); ?>" min="0" required />
    <img id="togglePassword" src="eye-close.png" alt="Show/Hide Password" onclick="togglePasswordVisibility('password', 'togglePassword')" style="height: 15px; width: 20px; position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;" />
    <label></label>
</div>
<div class="infield" style="position: relative;">
    <input type="password" placeholder="Confirm Password" id="confirm_password" name="confirm_password" value="<?php echo htmlspecialchars($confirm_password); ?>" required />
    <img id="toggleConfirmPassword" src="eye-close.png" alt="Show/Hide Password" onclick="togglePasswordVisibility('confirm_password', 'toggleConfirmPassword')" style="height: 15px; width: 20px; position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;" />
    <label></label>
</div>
<script>
    // PASS VISIBILITY
    function togglePasswordVisibility(passwordId, toggleId) {
        var passwordField = document.getElementById(passwordId);
        var toggleIcon = document.getElementById(toggleId);

        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            toggleIcon.src = 'eye-open.png'; // Use the image for showing password
        } else {
            passwordField.type = 'password';
            toggleIcon.src = 'eye-close.png'; // Use the image for hiding password
        }
    }
</script>



<!-- VERSION 1 -->
<!-- STYLE FOR MESSAGE BOX FOR BOTH FORM AND BUTTON SUBMITION -->
<style>
    /* FOR SWEETALERT */
    .swal2-popup {
        padding-bottom: 30px;
    }


    .confirm-button-class,
    .cancel-button-class {
        width: 100px;
        height: 40px;
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

<!-- VERSION 2 NO SCROLLBAR -->
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

<!-- VERSION 3 NO SCROLLBAR -->
<style>
    /* FOR SWEETALERT */
    .swal2-popup {
        padding-bottom: 30px;
        /* Adjust the padding as needed */
        overflow: hidden;
        /* Hide the overflow to prevent scroll bars */
    }

    .swal2-title {
        overflow: hidden;
        /* Ensure no overflow in the title */
        text-overflow: ellipsis;
        /* Add ellipsis if the title is too long */
        /* Prevent text wrapping in the title */
        margin-bottom: 20px;
        /* Add margin to adjust spacing */
    }

    .swal2-content {
        overflow: hidden;
        /* Hide the overflow to prevent scroll bars */
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


<!-- MESSAGE BOX FOR FORM SUBMITION -->
<form action="archive.php" method="post" class="archive-form">
    <!-- Your form inputs here -->
    <button type="submit">Archive</button>
</form>
<script>
    // Ensure SweetAlert2 is loaded
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
</script>


<!-- LINK NEEDED FOR SUBMITION FORM -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- VERSION1 -->
<!-- MESSAGE BOX FOR BUTTON SUBMITION -->
<a class='btn btn-danger btn-sm archive' href='./del_alumni.php'>Archive</a>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const archiveButtons = document.querySelectorAll('.archive');

        archiveButtons.forEach(function(button) {
            button.addEventListener('click', function(event) {
                event.preventDefault(); // Prevent the default action (navigation)

                const href = this.getAttribute('href'); // Get the href attribute

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
                        window.location.href = href; // Proceed with the navigation if confirmed
                    }
                });
            });
        });
    });
</script>


<!-- VERSION2 -->
<script>
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
</script>




<?php
// EVENT ARCHIVER
$sql = "SELECT * FROM event";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $timezone = new DateTimeZone('Asia/Manila');
    while ($row = $result->fetch_assoc()) {
        $eventDate = new DateTime($row["date"], $timezone);
        $eventTime = new DateTime($row["end_time"], $timezone);

        $currentDateTime = new DateTime('now', $timezone);
        $interval = $currentDateTime->diff($eventDate);
        $formattedDateCreated = $eventDate->format('Y-m-d');
        if (($interval->days >= 1 && $interval->invert == 1) || ($eventDate->format('Y-m-d') == $currentDateTime->format('Y-m-d') && $eventTime <= (clone $currentDateTime))) {
            echo "Event ID {$row['event_id']} - It has been 1 day or more since the event was created on {$formattedDateCreated}.<br>\n\n";
        }
    }
}


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

        echo "Alumni ID: " . $row["alumni_id"] . " - ";

        if (($interval->days >= 10 && $interval->invert == 1) && ($activity === 'Verified')) {
            echo "Status: Inactive for last 10 days or Above (Last login: $formattedDateCreated) <br>";
        } else {
            echo "Status: Active (Last login: $formattedDateCreated) <br>";
        }

        echo "<br>";
    }
} else {
    echo "No alumni records found.";
}



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
            $sql_archive = "INSERT INTO alumni_archive (alumni_id, student_id, fname, mname, lname, gender, course, batch_startYear, batch_endYear, contact, address, email, password, last_login, picture, date_created)" .
                "SELECT alumni_id, student_id, fname, mname, lname, gender, course, batch_startYear, batch_endYear, contact, address, email, password, last_login, picture, date_created FROM alumni WHERE alumni_id=$alumni_id";
            $conn->query($sql_archive);

            $stmt = $conn->prepare("UPDATE alumni_archive SET status = 'Inactive' WHERE alumni_id = ?");
            $stmt->bind_param("s", $alumni_id);
            $stmt->execute();
            $stmt->close();

            //delete data in table alumni
            $sql_delete = "DELETE FROM alumni WHERE alumni_id=$alumni_id";
            $conn->query($sql_delete);
        }
    }
}



// FOR ALUMNI FOR UNLI UNVERIFIED
$sql = "SELECT * FROM alumni";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $timezone = new DateTimeZone('Asia/Manila');
    while ($row = $result->fetch_assoc()) {
        $eventDatetime = new DateTime($row["last_login"], $timezone);
        $accUpdate_Datetime = new DateTime($row["accUpdate"], $timezone);
        $activity = $row["status"];
        $alumni_id = $row["alumni_id"];
        $currentDateTime = new DateTime('now', $timezone);

        $interval = $currentDateTime->diff($eventDatetime);
        $interval_accUpdate = $currentDateTime->diff($accUpdate_Datetime);

        if (($interval->y >= 2 && $interval->invert == 1) && ($activity === 'Verified')) {

            $sql_archive = "INSERT INTO alumni_archive (alumni_id, student_id, fname, mname, lname, gender, course, batch_startYear, batch_endYear, contact, address, email, password, last_login, picture, date_created)" .
                "SELECT alumni_id, student_id, fname, mname, lname, gender, course, batch_startYear, batch_endYear, contact, address, email, password, last_login, picture, date_created FROM alumni WHERE alumni_id=$alumni_id";
            $conn->query($sql_archive);

            $stmt = $conn->prepare("UPDATE alumni_archive SET status = 'Inactive' WHERE alumni_id = ?");
            $stmt->bind_param("s", $alumni_id);
            $stmt->execute();
            $stmt->close();

            $sql_delete = "DELETE FROM alumni WHERE alumni_id=$alumni_id";
            $conn->query($sql_delete);
        } else if (($interval_accUpdate->y >= 2 && $interval_accUpdate->invert == 1) && ($activity === 'Unverified')) {

            $sql_archive = "INSERT INTO alumni_archive (alumni_id, student_id, fname, mname, lname, gender, course, batch_startYear, batch_endYear, contact, address, email, password, last_login, picture, date_created)" .
                "SELECT alumni_id, student_id, fname, mname, lname, gender, course, batch_startYear, batch_endYear, contact, address, email, password, last_login, picture, date_created FROM alumni WHERE alumni_id=$alumni_id";
            $conn->query($sql_archive);

            $stmt = $conn->prepare("UPDATE alumni_archive SET status = 'Inactive' WHERE alumni_id = ?");
            $stmt->bind_param("s", $alumni_id);
            $stmt->execute();
            $stmt->close();

            $sql_delete = "DELETE FROM alumni WHERE alumni_id=$alumni_id";
            $conn->query($sql_delete);
        }
    }
}



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
            $sql_archive = "INSERT INTO alumni_archive (alumni_id, student_id, fname, mname, lname, gender, course, batch_startYear, batch_endYear, contact, address, email, password, last_login, picture, date_created)" .
                "SELECT alumni_id, student_id, fname, mname, lname, gender, course, batch_startYear, batch_endYear, contact, address, email, password, last_login, picture, date_created FROM alumni WHERE alumni_id=$alumni_id";
            $conn->query($sql_archive);

            $stmt = $conn->prepare("UPDATE alumni_archive SET status = 'Inactive' WHERE alumni_id = ?");
            $stmt->bind_param("s", $alumni_id);
            $stmt->execute();
            $stmt->close();

            //delete data in table alumni
            $sql_delete = "DELETE FROM alumni WHERE alumni_id=$alumni_id";
            $conn->query($sql_delete);
        }
    }
}



// FOR ALUMNI FOR CAN RESTOR ACCOUNT EXCEED %) AND ABOVE OF BEING INACTIVE/ NO LOGIN
$sql = "SELECT * FROM alumni";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $timezone = new DateTimeZone('Asia/Manila');
    while ($row = $result->fetch_assoc()) {
        $eventDatetime = new DateTime($row["last_login"], $timezone);
        $activity = $row["status"];
        $alumni_id = $row["alumni_id"];
        $currentDateTime = new DateTime('now', $timezone);
        $interval = $currentDateTime->diff($eventDatetime);

        if (($interval->y >= 2 && $interval->invert == 1) && ($activity === 'Verified')) {

            $sql_archive = "INSERT INTO alumni_archive (alumni_id, student_id, fname, mname, lname, gender, course, batch_startYear, batch_endYear, contact, address, email, password, last_login, picture, date_created)" .
                "SELECT alumni_id, student_id, fname, mname, lname, gender, course, batch_startYear, batch_endYear, contact, address, email, password, last_login, picture, date_created FROM alumni WHERE alumni_id=$alumni_id";
            $conn->query($sql_archive);

            $stmt = $conn->prepare("UPDATE alumni_archive SET status = 'Inactive' WHERE alumni_id = ?");
            $stmt->bind_param("s", $alumni_id);
            $stmt->execute();
            $stmt->close();

            $sql_delete = "DELETE FROM alumni WHERE alumni_id=$alumni_id";
            $conn->query($sql_delete);
        } else if (($interval->y >= 5 && $interval->invert == 1) && ($activity === 'Unverified')) {

            $sql_archive = "INSERT INTO alumni_archive (alumni_id, student_id, fname, mname, lname, gender, course, batch_startYear, batch_endYear, contact, address, email, password, last_login, picture, date_created)" .
                "SELECT alumni_id, student_id, fname, mname, lname, gender, course, batch_startYear, batch_endYear, contact, address, email, password, last_login, picture, date_created FROM alumni WHERE alumni_id=$alumni_id";
            $conn->query($sql_archive);

            $stmt = $conn->prepare("UPDATE alumni_archive SET status = 'Inactive' WHERE alumni_id = ?");
            $stmt->bind_param("s", $alumni_id);
            $stmt->execute();
            $stmt->close();

            $sql_delete = "DELETE FROM alumni WHERE alumni_id=$alumni_id";
            $conn->query($sql_delete);
        }
    }
}



// RESTORE ALUMNI THAT PREVENT TO RESTO IF LAST LOGIN 5 YEARS AND ABOVE
$sql = "SELECT * FROM alumni_archive";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $timezone = new DateTimeZone('Asia/Manila');
    while ($row = $result->fetch_assoc()) {
        $eventDatetime = new DateTime($row["last_login"], $timezone);
        $activity = $row["status"];
        $alumni_id = $row["alumni_id"];
        $currentDateTime = new DateTime('now', $timezone);
        $interval = $currentDateTime->diff($eventDatetime);

        if ($interval->y >= 2 && $interval->invert == 1) {
            $icon = 'warning';
            $iconHtml = '<i class="fas fa-exclamation-triangle"></i>';
            $title = 'Unsuccessfully';
            $text = 'Account exceed 5 yearc of inactive connot restored.';

            echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        warningError('$title', '$text', '$icon', '$iconHtml');
                    });
                </script>";
            sleep(2);
        } else {

            //insert data into table alumni_archive from alumni
            $sql_restore = "INSERT INTO alumni (alumni_id, student_id, fname, mname, lname, gender, course, batch_startYear, batch_endYear, contact, address, email, password, last_login, picture, date_created)" .
                "SELECT alumni_id, student_id, fname, mname, lname, gender, course, batch_startYear, batch_endYear, contact, address, email, password, last_login, picture, date_created FROM alumni_archive WHERE alumni_id=$alumni_id";
            $conn->query($sql_restore);

            $stmt = $conn->prepare("UPDATE alumni SET status = 'Unverified' WHERE alumni_id = ?");
            $stmt->bind_param("s", $alumni_id);
            $stmt->execute();
            $stmt->close();

            //delete data in table alumni
            $sql_delete = "DELETE FROM alumni_archive WHERE alumni_id=$alumni_id";
            $conn->query($sql_delete);
        }
    }
}

?>