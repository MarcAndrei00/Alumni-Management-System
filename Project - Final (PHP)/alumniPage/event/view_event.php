<?php
require_once('vendor/autoload.php');

session_start();
$conn = new mysqli("localhost", "root", "", "alumni_management_system");

$client = new \GuzzleHttp\Client();


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
            // User is an alumni
            $user = $user_result->fetch_assoc();
        } else {
            $stmt->close();
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
    $time = $row['start_time'] . ' TO ' . $row['end_time'];
    $description = $row['description'];
    $image = $row['image'];

    $address = $row['address'];
    $displayAddress = str_replace(',', '', $address);

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
} else if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_GET['event_id'])) {
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

        $icon = 'success';
        $iconHtml = '<i class="fas fa-check-circle"></i>';
        $title = 'Status updated successfully.';
        $redirectUrl = "./view_event.php?id=$event_id_id";

        echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    noTextRedirect('$redirectUrl', '$title', '$icon', '$iconHtml');
                });
            </script>";
        sleep(2);
    } else {
        // insert to event_choice table
        $final_sql = "INSERT INTO event_choice (event_id, alumni_id, event_choice, student_id, fullname, email) VALUES ($event_id, $alumni_id, '$event_choice', $student_id, '$fullname', '$email')";
        $final_result = $conn->query($final_sql);

        $icon = 'success';
        $iconHtml = '<i class="fas fa-check-circle"></i>';
        $title = 'Status updated successfully.';
        $redirectUrl = "./view_event.php?id=$event_id_id";

        echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    noTextRedirect('$redirectUrl', '$title', '$icon', '$iconHtml');
                });
            </script>";
        sleep(2);
    }
}

if (isset($_SESSION['donation'])) {
    $Message = $_SESSION['donation'];
} else {
    $Message = 'nodonation';
}

if ($Message == 'DONATION') {
    $icon = 'success';
    $iconHtml = '<i class="fas fa-check-circle"></i>';
    $title = 'Thank you for your generous donation.';
    $text = 'Your support means a lot and helps make this event a success!';
        
    echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    thanks('$title', '$text', '$icon', '$iconHtml');
                });
            </script>";
    $Message = 'nodonation';
    $_SESSION['donation'] = $Message;

}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_btn'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $event_id = $_POST['event_id'];
    $titlee = $_POST['title'];
    $contact_number = $_POST['contact_number'];
    $donation = $_POST['donation_amount'];

    $donationInCents = $donation * 100;

    $donation_qry = mysqli_query($conn, "INSERT INTO donation_table (name, email, event_id, event_title, contact_number, donation_amount)
    VALUES('$name','$email','$event_id','$titlee','$contact_number','$donation')");

    // Define the request body with the required description
    $requestBody = [
        'data' => [
            'attributes' => [
                'amount' => $donationInCents,
                'description' => 'Thank you for your generous donation to ' . $titlee . '. Your support means a lot and helps make this event a success!', // Add a description here
            ],
        ],
    ];
    $_SESSION['donation'] = 'DONATION';
    // Send the request to the PayMongo API
    $response = $client->request('POST', 'https://api.paymongo.com/v1/links', [
        'body' => json_encode($requestBody), // Convert the request body to JSON
        'headers' => [
            'accept' => 'application/json',
            'authorization' => 'Basic c2tfdGVzdF9QSEUyUTNlQ3luc1lOcWNhYW03ejRFenk6',
            'content-type' => 'application/json',
        ],
    ]);

    // Decode the JSON response
    $responseData = json_decode($response->getBody(), true);

    // Extract the checkout URL
    $checkoutUrl = $responseData['data']['attributes']['checkout_url'];

    // Store the URL in the session
    $_SESSION['checkout_url'] = $checkoutUrl;
    header('Location: redirect.php');
    exit();
}
// Convert start_time and end_time to 12-hour format with AM/PM
$startTime = date('g:i A', strtotime($row["start_time"]));
$endTime = date('g:i A', strtotime($row["end_time"]));
$time = $startTime . " - " . $endTime;

// Date and time formatting combined in a single column
$date = date('F j, Y', strtotime($row['date_created']));
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
    <title>Event List</title>
    <link rel="shortcut icon" href="../../assets/cvsu.png" type="image/svg+xml">
    <link rel="stylesheet" href="css/view_event.css">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Playfair+Display:wght@700&family=Dancing+Script:wght@400&family=Cinzel:wght@700&family=Oswald:wght@700&family=Raleway:wght@600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            text-decoration: none;
            list-style-type: none;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        .swal2-popup {
            padding-bottom: 30px;
            /* Adjust the padding as needed */
        }
        .icon{
            overflow-y: hidden;
        }
        .title{
            overflow: hidden;
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

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
            border: 1px solid #f5c6cb;
            text-align: center;
            font-family: Arial, sans-serif;
        }

        .error-message {
            margin: 0;
            font-size: 14px;
        }

        #real-time-errors {
            display: none;
            /* Hidden by default */
        }
    </style>
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
                <h4 style="overflow-y: hidden;"><?php echo $user['fname']; ?></h4>
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
                <h1 style="overflow-y: hidden;"><strong>Event</strong></h1>
            </div>
        </main>
        <div class="container-fluid" id="page-content">
            <div class="container-fluid" id="content-header">
            </div>
            <div class="container" id="main-container">
                <div class="container-fluid" id="content-container">
                    <div class="container-title">
                        <h4 style="overflow-y: hidden;">More Details</h4>
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
                            <h3 class="mt-0" style="overflow-y: hidden;"> <strong><?php echo $row['title'] ?></strong></h3>

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
                                            <div class="date d-flex">
                                                <div class="me-3" style="width: 100%;">
                                                    <label for="">Date:</label>
                                                    <input type="text" class="form-control form-label mt-3" value="<?php echo $date ?>">
                                                </div>
                                                <div style="width: 100%;">
                                                    <label for="">Time:</label>
                                                    <input type="text" class="form-control form-label mt-3" value="<?php echo $startTime . ' To ' . $endTime ?>">
                                                </div>
                                            </div>
                                            <div class="date">
                                                <label for="">Venue:</label>
                                                <input type="text" class="form-control form-label mt-3" value="<?php echo $row['venue'] ?>">
                                            </div>
                                            <div class="date">
                                                <label for="">Address:</label>
                                                <input type="text" class="form-control form-label mt-3" value="<?php echo $displayAddress ?>">
                                            </div>
                                        </fieldset>

                                    </div>
                                    <form method="POST" action="view_event.php" class="addNew">
                                        <div class="col" id="dropdown">
                                            <input type="hidden" name="titlee" value="<?php echo $row['title'] ?>">
                                            <input type="hidden" name="event_idd" value="<?php echo $event_id; ?>">
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
                                            <div class="submit text-center" style="display: flex; justify-content: space-between; align-items: center;">
                                                <button type="submit" class="btn btn-success" style="flex: 1; margin-right: 10px;">Submit</button>
                                                <a class="btn btn-light border border-dark" href='./event.php' style="flex: 1; margin-right: 10px;">Back</a>
                                                <button type="button" class="btn btn-success btnTWO" style="flex: 1; background-color: #28a745; border-color: #28a745;" data-toggle="modal" data-target="#donateModal">Donate</button>

                                            </div>
                                        </div>
                                    </form>
                                    <div class="modal fade" id="donateModal" tabindex="-1" aria-labelledby="donateModalLabel" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="donateModalLabel">Donate</h5>
                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                        <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <form method="POST" id="donationForm">
                                                        <input type="hidden" name="event_id" id="modal_event_id">
                                                        <input type="hidden" name="title" id="modal_event_title">
                                                        <div class="form-group">
                                                            <label for="donationAmount">Donation Amount:</label>
                                                            <span style="margin-left: 10px; color: #155724;">Minimum donation is 100 PHP</span>
                                                            <input type="text" name="donation_amount" class="form-control" id="donationAmount" onkeyup="donationValue()" placeholder="Enter amount" required>
                                                            <div class="alert alert-danger text-center error-list" id="real-time-errors"></div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                            <button type="submit" name="submit_btn" class="btn btn-success">Donate</button>
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
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function getImagePreview(event) {
            var image = URL.createObjectURL(event.target.files[0]);
            var preview = document.getElementById('preview');
            preview.src = image;
        }


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
                            cancelButton: 'cancel-button-class',
                            icon: 'icon',
                            title: 'title',
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

        function donationValue() {
            var donation = document.getElementById("donationAmount").value;
            var errorMessages = [];
            var errorContainer = document.getElementById("real-time-errors");

            // Clear previous error messages
            errorContainer.innerHTML = "";

            // Validation rules
            if (donation >= 0) {
                errorContainer.style.display = 'none';
                return;
            } else {
                errorMessages.push("Please enter a positive donation amount. Negative values are not allowed.");
            }

            // Display error messages
            if (errorMessages.length > 0) {
                errorMessages.forEach(function(error) {
                    var p = document.createElement("p");
                    p.innerText = error;
                    p.className = "error-message";
                    errorContainer.appendChild(p);
                });
                // Ensure the error container is visible
                errorContainer.style.display = 'block';
            } else {
                // Hide the error container if there are no errors
                errorContainer.style.display = 'none';
            }
        }
        document.querySelectorAll('.btnTWO').forEach(button => {
            button.addEventListener('click', function() {
                // Assuming the button is inside a form section that contains the hidden event inputs
                const eventSection = this.closest('form');
                const eventID = eventSection.querySelector('input[name="event_idd"]').value;
                const eventTitle = eventSection.querySelector('input[name="titlee"]').value;

                // Set the values in the modal form
                document.getElementById('modal_event_id').value = eventID;
                document.getElementById('modal_event_title').value = eventTitle;
            });
        });
        function donationValue() {
            var donation = document.getElementById("donationAmount").value;
            var errorMessages = [];
            var errorContainer = document.getElementById("real-time-errors");

            // Clear previous error messages
            errorContainer.innerHTML = "";

            // Validation rules
            if (donation === '') {
                errorContainer.style.display = 'none';
                return;
            } else {
                if (donation > 99) {
                errorContainer.style.display = 'none';
                return;
                } else {
                    errorMessages.push("Minimum donation is 100.");
                }
            }
            // Display error messages
            if (errorMessages.length > 0) {
                errorMessages.forEach(function(error) {
                    var p = document.createElement("p");
                    p.innerText = error;
                    p.className = "error-message";
                    errorContainer.appendChild(p);
                });
                // Ensure the error container is visible
                errorContainer.style.display = 'block';
            } else {
                // Hide the error container if there are no errors
                errorContainer.style.display = 'none';
            }
        }
        document.getElementById('donationForm').addEventListener('submit', function(event) {
            var donationAmount = document.getElementById('donationAmount').value;

            if (donationAmount < 100) {
                // Prevent form submission
                event.preventDefault();

                // Show SweetAlert warning
                Swal.fire({
                    icon: 'warning',
                    title: 'Sorry for interruption.',
                    text: 'Minimum donation is 100. Thank you!',
                    customClass: {
                        icon: 'icon',
                        title: 'title'
                    }
                });
            }
        });
        document.addEventListener("DOMContentLoaded", function() {
            const donationAmount = document.getElementById("donationAmount");

            donationAmount.addEventListener("input", function(event) {
                let value = donationAmount.value;
                // Replace all non-numeric characters
                value = value.replace(/[^0-9]/g, '');
                donationAmount.value = value;
            });
        });
    </script>
</body>

</html>