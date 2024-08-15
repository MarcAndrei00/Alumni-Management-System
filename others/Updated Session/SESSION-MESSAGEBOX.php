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
sleep(3);


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
sleep(3);


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
sleep(3);


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
sleep(3);


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
sleep(3);


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
sleep(3);

?>

<!-- FOR NO NIGGATIVE NUMBERS -->
<div class="infield">
    <input type="text" id="student_id" placeholder="Student ID" name="student_id" value="<?php echo htmlspecialchars($stud_id); ?>" required />
    <label></label>
</div>


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