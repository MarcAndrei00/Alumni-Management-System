<?php
session_start();

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
        $user = $user_result->fetch_assoc();
    }
    $stmt->close();
    
} else {
    header('Location: ../../homepage.php');
    exit();
}

$fullname = $user["fname"] . " " . $user["mname"] . " " . $user["lname"];

//read data from table alumni
$sql = "SELECT * FROM alumni WHERE alumni_id=$account";
$result = $conn->query($sql);
$rowpic = $result->fetch_assoc();

$file = $rowpic['picture'];

$title = "";
$schedule = "";
$description = "";
$interested = "";
$not_interested = "";
$going = "";

// for view more details on event
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    // Show the data of admin
    if (!isset($_GET['id'])) {
        header("location: ./event.php");
        exit;
    }
    $event_id = $_GET['id'];

    // Read data from table admin
    $sql = "SELECT * FROM event WHERE event_id=$event_id";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();

    if (!$row) {
        header("location: ./event.php");
        exit;
    }

    $event_id_id = $row['event_id'];
    $title = $row['title'];
    $schedule = $row['schedule'];
    $description = $row['description'];
    $image = $row['image'];

    // for alumni choice
    $sql2 = "SELECT * FROM event_choice WHERE event_id=$event_id_id AND alumni_id=$rowpic[alumni_id]";
    $result2 = $conn->query($sql2);
    $row2 = $result2->fetch_assoc();

    $check = mysqli_query($conn, "SELECT * FROM event_choice WHERE event_id=$event_id_id AND alumni_id=$rowpic[alumni_id]");
    if (mysqli_num_rows($check) > 0) {
        $choice_event_id = $row2['event_id'];
        $event_choice = $row2['event_choice'];
        $event_alumni_id = $row2['alumni_id'];
    }
} else if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the data from form
    $event_id = $_POST['event_id'];

    // Read data from table admin
    $sql = "SELECT * FROM event WHERE event_id=$event_id";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();

    if (!$row) {
        header("location: ./event.php");
        exit;
    }

    $event_id_id = $row['event_id'];

    // for alumni choice
    $sql2 = "SELECT * FROM event_choice WHERE event_id=$event_id_id AND alumni_id=$rowpic[alumni_id]";
    $result2 = $conn->query($sql2);
    $row2 = $result2->fetch_assoc();


    $prev_vote_check = mysqli_query($conn, "SELECT event_choice FROM event_choice WHERE event_id=$event_id_id AND alumni_id=$rowpic[alumni_id]");
    if (mysqli_num_rows($prev_vote_check) > 0) {
        $prev_vote = $row2['event_choice'];
    } else {
        $prev_vote = "";
    }


    // START OF POST METHOD
    $alumni_id = $_POST['alumni_id'];
    $event_choice = $_POST['event_choice'];
    $student_id = $_POST['student_id'];
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];

    // check from previous data
    $sql3 = "SELECT * FROM event WHERE event_id=$event_id_id";
    $result3 = $conn->query($sql3);
    $row3 = $result3->fetch_assoc();

    $going = $row3['going'];
    $interested = $row3['interested'];
    $not_interested = $row3['not_interested'];

    // check the event count change the value
    if ($event_choice == "Going") {
        if ($prev_vote == "Interested") {
            $interested -= 1;
            $going += 1;
            $sql_event = "UPDATE event SET going = $going, interested = $interested WHERE event_id=$event_id_id";
            $result_event = $conn->query($sql_event);
        } else if ($prev_vote == "Not Interested") {
            $not_interested -= 1;
            $going += 1;
            $sql_event = "UPDATE event SET going = $going, not_interested = $not_interested WHERE event_id=$event_id_id";
            $result_event = $conn->query($sql_event);
        } else {
            $going += 1;
            $sql_event = "UPDATE event SET going = $going WHERE event_id=$event_id_id";
            $result_event = $conn->query($sql_event);
        }
    } else if ($event_choice == "Interested") {
        if ($prev_vote == "Going") {
            $going -= 1;
            $interested += 1;
            $sql_event = "UPDATE event SET going = $going, interested = $interested WHERE event_id=$event_id_id";
            $result_event = $conn->query($sql_event);
        } else if ($prev_vote == "Not Interested") {
            $not_interested -= 1;
            $interested += 1;
            $sql_event = "UPDATE event SET not_interested = $not_interested, interested = $interested WHERE event_id=$event_id_id";
            $result_event = $conn->query($sql_event);
        } else {
            $interested += 1;
            $sql_event = "UPDATE event SET interested = $interested WHERE event_id=$event_id_id";
            $result_event = $conn->query($sql_event);
        }
    } else if ($event_choice == "Not Interested") {
        if ($prev_vote == "Going") {
            $going -= 1;
            $not_interested += 1;
            $sql_event = "UPDATE event SET going = $going, not_interested = $not_interested WHERE event_id=$event_id_id";
            $result_event = $conn->query($sql_event);
        } else if ($prev_vote == "Interested") {
            $interested -= 1;
            $not_interested += 1;
            $sql_event = "UPDATE event SET interested = $interested, not_interested = $not_interested WHERE event_id=$event_id_id";
            $result_event = $conn->query($sql_event);
        } else {
            $not_interested += 1;
            $sql_event = "UPDATE event SET not_interested = $not_interested WHERE event_id=$event_id_id";
            $result_event = $conn->query($sql_event);
        }
    }

    // CHECK IF USER HAS A VOTE RECORD IN THIS EVENT
    $check = mysqli_query($conn, "SELECT * FROM event_choice WHERE event_id=$event_id_id AND alumni_id=$rowpic[alumni_id]");

    if (mysqli_num_rows($check) > 0) {
        // insert to event_choice table
        $final_sql = "UPDATE event_choice SET event_choice = '$event_choice' WHERE event_id=$event_id_id AND alumni_id=$rowpic[alumni_id]";
        $final_result = $conn->query($final_sql);

        echo "
            <script>
                // Wait for the document to load
                document.addEventListener('DOMContentLoaded', function() {
                    // Use SweetAlert2 for the alert
                    Swal.fire({
                            title: 'Status updated Successfully',
                            timer: 2000,
                            showConfirmButton: true, // Show the confirm button
                            confirmButtonColor: '#4CAF50', // Set the button color to green
                            confirmButtonText: 'OK' // Change the button text if needed
                    }).then(function() {
                        // Redirect after the alert closes
                        window.location.href = './view_event.php?id=$event_id_id';
                    });
                });
            </script>
            ";
    } else {
        // insert to event_choice table
        $final_sql = "INSERT INTO event_choice (event_id, alumni_id, event_choice, student_id, fullname, email) VALUES ($event_id, $alumni_id, '$event_choice', $student_id, '$fullname', '$email')";
        $final_result = $conn->query($final_sql);

        echo "
            <script>
                // Wait for the document to load
                document.addEventListener('DOMContentLoaded', function() {
                    // Use SweetAlert2 for the alert
                    Swal.fire({
                            title: 'Status updated Successfully',
                            timer: 2000,
                            showConfirmButton: true, // Show the confirm button
                            confirmButtonColor: '#4CAF50', // Set the button color to green
                            confirmButtonText: 'OK' // Change the button text if needed
                    }).then(function() {
                        // Redirect after the alert closes
                        window.location.href = './view_event.php?id=$event_id_id';
                    });
                });
            </script>
            ";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
    <title>Event List</title>
    <link rel="shortcut icon" href="../../assets/cvsu.png" type="image/svg+xml">
    <link rel="stylesheet" href="css/view_event.css">
    <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <input type="checkbox" id="menu-toggle">
    <div class="sidebar">
        <div class="side-header">
            <h3><img src="https://cvsu-imus.edu.ph/student-portal/assets/images/logo-mobile.png"></img><span>CVSU</span></h3>
        </div>

        <div class="side-content">
            <div class="profile">
                <div>
                    <img id="preview" src="data:image/jpeg;base64,<?php echo base64_encode($rowpic['picture']); ?>" style="width:83px;height:83px; border-radius: 100%;border: 2px solid white;">
                </div>
                <h4><?php echo $user['fname']; ?></h4>
                <small style="color: white;"><?php echo $user['email']; ?></small>
            </div>

            <div class="side-menu">
                <ul>
                    <li>
                        <a href="../dashboard_user.php">
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
                        <a href="./view_event.php" class="active">
                            <span class="las la-calendar" style="color:#fff"></span>
                            <small>EVENT</small>
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
                    <span class="las la-bars"></span>
                </label>
                <!-- <span class="header-title">ALUMNI MANAGEMENT SYSTEM</span>  -->
                <div class="header-menu">
                    <label for="">
                    </label>

                    <div class="notify-icon">
                    </div>

                    <div class="notify-icon">
                    </div>

                    <div class="user">
                        <div class="bg-img" style="background-image: url(img/1.jpeg)"></div>

                        <a href="../logout.php">
                            <span class="las la-power-off"></span>
                        </a>

                    </div>
                </div>
            </div>
        </header>


        <main>
            <div class="page-header">
                <h1><strong>Event</strong></h1>
            </div>
        </main>
        <div class="container-fluid" id="page-content">
            <div class="container-fluid" id="content-header">
            </div>
            <div class="container" id="main-container">
                <div class="container-fluid" id="content-container">
                    <div class="container-title">
                        <h4>More Details</h4>
                    </div>
                    <div class="row g-0 position-relative">
                        <div class="col-md-6 mb-md-0 p-md-4">
                            <?php
                            // Assuming $row['image'] contains the binary image data
                            $imageData = $row['image'];

                            // Save the image to a temporary file
                            $tempImagePath = tempnam(sys_get_temp_dir(), 'img');
                            file_put_contents($tempImagePath, $imageData);

                            // Get the image dimensions
                            list($width, $height) = getimagesize($tempImagePath);

                            // Delete the temporary file
                            unlink($tempImagePath);

                            if ($width > $height || $width == $height) {
                                // Landscape or square (1x1) image
                                echo '<div style="display: flex; justify-content: center; align-items: center; height: 65vh;">';
                                echo '<img src="data:image/jpeg;base64,' . base64_encode($imageData) . '" class="w-100" alt="...">';
                                echo '</div>';
                            } else {
                                // Portrait image
                                echo '<div>';
                                echo '<img src="data:image/jpeg;base64,' . base64_encode($imageData) . '" class="w-100" alt="...">';
                                echo '</div>';
                            }
                            ?>
                        </div>
                        <div class="col-md-6 p-4 ps-md-0" id="right-side">
                            <h3 class="mt-0"> <strong><?php echo $row['title'] ?></strong></h3>

                            <fieldset disabled>
                                <div class="description">
                                    <label for="" class="form-label">Event Description:</label>
                                    <textarea class="form-control" id="exampleFormControlTextarea1" rows="10"><?php echo $row['description'] ?></textarea>
                                </div>
                            </fieldset>

                            <div class="row">
                                <div class="container" id="date">
                                    <div class="col">

                                        <fieldset disabled>
                                            <div class="date">
                                                <label for="" class="form-label mt-3">Event Date & Time:</label>
                                                <input type="datetime-local" class="form-control form-label mt-3" value="<?php echo $schedule ?>">
                                            </div>
                                        </fieldset>

                                    </div>
                                    <form method="POST" action="view_event.php" onsubmit="return submitForm(this);">
                                        <div class="col" id="dropdown">
                                            <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">
                                            <input type="hidden" name="alumni_id" value="<?php echo $user['alumni_id']; ?>">
                                            <input type="hidden" name="student_id" value="<?php echo $user['student_id']; ?>">
                                            <input type="hidden" name="fullname" value="<?php echo $fullname; ?>">
                                            <input type="hidden" name="email" value="<?php echo $user['email']; ?>">
                                            <select class="form-control" name="event_choice" id="event" required>
                                                <?php if (($choice_event_id == $event_id_id)  and ($event_alumni_id == $rowpic['alumni_id'])) : ?>
                                                    <option value="" selected hidden disabled><?php echo htmlspecialchars($event_choice); ?></option>
                                                    <?php if ($event_choice != 'Interested') : ?>
                                                        <option value="Interested">Interested</option>
                                                    <?php endif; ?>
                                                    <?php if ($event_choice != 'Not Interested') : ?>
                                                        <option value="Not Interested">Not Interested</option>
                                                    <?php endif; ?>
                                                    <?php if ($event_choice != 'Going') : ?>
                                                        <option value="Going">Going</option>
                                                    <?php endif; ?>
                                                <?php else : ?>
                                                    <option value="" selected hidden disabled>Are you going to the event?</option>
                                                    <option value="Interested">Interested</option>
                                                    <option value="Not Interested">Not Interested</option>
                                                    <option value="Going">Going</option>
                                                <?php endif; ?>
                                            </select>
                                            <div class="submit">
                                                <button type="submit" class="btn btn-success" style="padding-left: 58.9px; padding-right: 58.9px;">Submit</button>
                                                <a class="btn btn-light border border-dark" href='./event.php' style="margin-left: 1%; padding-left: 65.9px; padding-right: 65.9px;">Back</a>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
    </div>
    </div>

    <!-- <script>
        let eventPic = document.getElementById("event-pic");
        let formFile = document.getElementById("formFile");

        formFile.onchange = function() {
            eventPic.src = URL.createObjectURL(formFile.files[0]);
        }
    </script> -->
    <!-- Script to display preview of selected image -->
    <script>
        function getImagePreview(event) {
            var image = URL.createObjectURL(event.target.files[0]);
            var preview = document.getElementById('preview');
            preview.src = image;
        }


        function submitForm(form) {
            Swal.fire({
                    title: 'Do you want to continue?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e03444',
                    cancelButtonColor: '#ffc404',
                    confirmButtonText: 'Submit'
                })
                .then((result) => {
                    if (result.isConfirmed) {
                        form.submit(); // Submit the form
                    }
                });
            return false; // Prevent default form submission
        }
    </script>
</body>

</html>