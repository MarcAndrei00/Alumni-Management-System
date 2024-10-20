<?php
require_once '../vendor/autoload.php';

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
            $_SESSION['alert'] = 'Unverified';
            sleep(2);
            header('Location: ./loginPage/verification_code.php');
            exit();
        }
    } else {
        // Redirect to login if no matching user found
        session_destroy();
        header('Location: ./event.php');
        exit();
    }
}
// DONATE MESSAGE
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

// Read data from table alumni
$sql = "SELECT * FROM event";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cavite State University - Imus Campus</title>
    <link rel="shortcut icon" href="assets/cvsu.png" type="image/svg+xml">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Playfair+Display:wght@700&family=Dancing+Script:wght@400&family=Cinzel:wght@700&family=Oswald:wght@700&family=Raleway:wght@600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


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

        body {
            background-color: #0F5123;
            margin: 0;
            padding: 0;
            scroll-behavior: smooth;
            overflow-x: hidden;
        }

        .navbar {
            background: linear-gradient(90deg, rgb(7 108 17) 0%, rgba(42, 145, 52, 1) 100%);
            color: white;

            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand img {
            height: 60px;
        }

        .navbar-brand,
        .navbar-nav .nav-link {
            color: white;
            font-weight: bold;
        }

        .navbar-nav .nav-link {
            position: relative;
            font-size: 1.1rem;
            padding: 0.5rem 1rem;
            transition: color 0.3s;
        }

        .navbar-nav .nav-link:after {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            bottom: -5px;
            height: 3px;
            background: white;
            transition: width 0.3s;
            width: 0;
        }

        .navbar-nav .nav-link:hover:after {
            width: 100%;
        }

        .navbar-nav .nav-link:hover {
            color: #f8f9fa;
        }

        .navbar .btn {
            border: 1px solid white;
            color: white;
            margin-left: 10px;
            transition: background-color 0.3s;
            font-size: 1rem;
        }

        .navbar .btn:hover {
            background-color: #ffffff44;
        }

        .hero-section {
            position: relative;
            text-align: center;
            color: white;
            background-color: #343a40;
            overflow: hidden;
        }

        .hero-section img {
            width: 100%;
            height: auto;
            opacity: 0.7;
            display: block;
        }

        .hero-section .overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            background: rgba(0, 0, 0, 0.5);
            padding: 20px;
        }

        .hero-section .overlay h1,
        .hero-section .overlay h2 {
            animation: fadeInUp 1s ease-out;
        }


        .hero-section .overlay h1 {
            font-size: 8rem;
            /* Adjust size */
            margin-bottom: 10px;
            font-weight: 700;
            /* Updated font */
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.5);
            color: #ffffff;
            border-right: 0.15em solid #ffffff;
            /* Blinking cursor */
            white-space: nowrap;
            overflow: hidden;
            width: 0;
            animation: typing 3s steps(30, end) forwards, blink-cursor 0.75s step-end infinite, hide-cursor 0s 3.1s forwards;

        }

        .buttons1 {
            text-align: right;
            /* Aligns the buttons to the right */
        }

        .buttons .btn {
            display: inline-block;
            width: 200px;
            /* Set the width as per your requirement */
            text-align: center;
            /* Center the text */
        }

        .buttons .btn-donate,
        .buttons .btn-view-details {
            padding: 10px 20px;
            /* Ensure consistent padding */
        }

        @keyframes typing {
            from {
                width: 0
            }

            to {
                width: 100%
            }
        }

        @keyframes blink-cursor {

            from,
            to {
                border-color: transparent
            }

            50% {
                border-color: #ffffff;
            }
        }

        @keyframes hide-cursor {
            to {
                border-right: none;
            }
        }

        @keyframes typing {
            from {
                width: 0;
            }

            to {
                width: 100%;
            }
        }

        @keyframes blink-caret {

            from,
            to {
                border-color: transparent;
            }

            50% {
                border-color: #ffffff;
            }
        }



        .hero-section .overlay h2 {
            font-size: 1.8rem;
            margin-top: 10px;
            font-weight: 600;
            color: #fec20c;
            cursor: pointer;
            text-decoration: none;
            padding: 15px 25px;
            border: 2px solid #ffffff;
            border-radius: 50px;
            transition: background-color 0.3s, color 0.3s;
            width: auto;
            height: auto;
            line-height: 1.5;
            text-align: center;
            display: inline-block;
            background-color: transparent;
        }

        .hero-section .overlay h2:hover {
            background-color: rgba(255, 255, 255, 0.3);
        }

        .hero-section .overlay h2:active {
            background-color: white;
            color: #2a912e;
        }







        .hero-section .overlay h2:hover {
            background-color: rgba(255, 255, 255, 0.3);
        }

        .hero-section .overlay h2:active {
            background-color: white;
            color: #2a912e;
        }

        @keyframes typing {
            from {
                width: 0;
            }

            to {
                width: 100%;
            }
        }

        @keyframes fadeInUp {
            0% {
                opacity: 0;
                transform: translateY(20px);
            }

            100% {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hello-heroes-section {
            text-align: center;
            padding: 40px 20px;
            background-color: #062315;
            color: white;
            font-weight: 700;
        }

        .hello-heroes-section h2 {
            font-size: 4rem;
            margin: 0;
            animation: bounceIn 1s ease-out;
            color: #fec20c;
        }

        @keyframes bounceIn {
            0% {
                transform: scale(0);
                opacity: 0;
            }

            50% {
                transform: scale(1.05);
                opacity: 1;
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }



        .hello-heroes-section h2 {
            font-size: 4rem;
            margin: 0;
            animation: bounceIn 1s ease-out;
        }

        @keyframes bounceIn {
            0% {
                transform: scale(0);
                opacity: 0;
            }

            50% {
                transform: scale(1.05);
                opacity: 1;
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }


        .mission-section {
            padding: 100px 40px;
            background-color: #0F5132;
            margin: 0;
        }

        .mission-section .container {
            position: relative;
            z-index: 1;
            background-color: #fff;
            padding: 60px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 1900px;
        }

        .mission-section .row {
            position: relative;
            z-index: 1;
            display: flex;
            flex-wrap: wrap;
        }

        .mission-section .col-lg-6 {
            flex: 1;
            min-width: 100px;
        }

        .mission-section img {
            height: auto;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .mission-section h2 {
            margin-bottom: 20px;
            font-size: 2.5rem;
            font-weight: 700;
        }

        .mission-section p {
            font-size: 1.3rem;
            line-height: 1.6;
            color: #555;
            text-align: justify;
            font-style: 'poppins';

        }

        .vision-section {
            padding: 100px 40px;
            background-color: #0F5123;
            margin: 0;
        }

        .vision-section .container {
            position: relative;
            z-index: 1;
            background-color: #fff;
            padding: 60px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 1900px;
        }

        .vision-section .row {
            position: relative;
            z-index: 1;
            display: flex;
            flex-wrap: wrap;
        }

        .vision-section .col-lg-6 {
            flex: 1;
            min-width: 100px;
        }

        .vision-section img {
            height: auto;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .vision-section h2 {
            margin-bottom: 20px;
            font-size: 2.5rem;
            font-weight: 700;
        }

        .vision-section p {
            font-size: 1.3rem;
            line-height: 1.6;
            color: #555;
            text-align: justify;
        }

        .hello-heroes-section h2 {
            font-size: 4rem;
            margin: 0;
            animation: bounceIn 1s ease-out;
        }

        @keyframes bounceIn {
            0% {
                transform: scale(0);
                opacity: 0;
            }

            50% {
                transform: scale(1.05);
                opacity: 1;
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }


        .footer {
            background-color: #2e2f34;
            color: white;
            padding: 20px 0;
        }

        .footer .logo-info {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .footer .logo-info img {
            height: 50px;
            margin-right: 10px;
        }

        .footer .logo-info h5 {
            margin: 0;
        }

        .footer .container {
            text-align: center;
        }

        .footer .container p {
            margin: 0;
            font-size: 0.9em;
            color: #bbb;
        }

        @media (max-width: 768px) {
            .navbar-collapse {
                text-align: center;
            }

            .navbar-nav .nav-item {
                margin: 10px 0;
            }

            .hero-section .overlay h1 {
                font-size: 2.5rem;
            }

            .hero-section .overlay h2 {
                font-size: 1.8rem;
            }

            .hello-heroes-section h2 {
                font-size: 2rem;
            }

            .mission-section h2 {
                font-size: 2rem;
            }

            .mission-section p {
                font-size: 1rem;
            }

            .mission-section .container {
                padding: 5px;
            }

            .hello-heroes-section h2 {
                font-size: 2rem;
            }

            .vision-section h2 {
                font-size: 2rem;
            }

            .vision-section p {
                font-size: 1rem;
            }

            .vision-section .container {
                padding: 5px;
            }

            .hello-heroes-section h2 {
                font-size: 2rem;
            }

            .hymn-section h2 {
                font-size: 2rem;
            }

            .hymn-section p {
                font-size: 1rem;
            }

            .hymn-section .container {
                padding: 5px;
            }

            .nav-item.active .nav-link {
                color: #fff;
                /* Set the text color for the active link */
                background-color: #007bff;
                /* Set the background color for the active link */
                border-radius: 5px;
                /* Add some rounding to the corners */
            }

            .nav-item.active .nav-link:hover,
            .nav-item.active .nav-link:focus {
                color: #fff;
                /* Ensure the color remains the same on hover/focus */
                background-color: #0056b3;
                /* Darker shade on hover/focus */
            }
        }

        .btsTWO {
            display: flex;
            gap: 10px;
        }

        .btnTWO {
            flex: 1;
            text-align: center;
            flex: 1;
            text-align: center;
            padding: 10px;
            border: none;
            font-size: 16px;
            min-width: 100px;

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
    <nav class="navbar navbar-expand-lg navbar-dark">
        <a class="navbar-brand" href="homepage.php">
            <img src="assets/cvsu.png" alt="Cavite State University - Imus Campus">
        </a>
        <a class="navbar-brand" href="homepage.php">CAVITE STATE UNIVERSITY - IMUS CAMPUS</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item ">
                    <a class="nav-link" href="homepage.php">Home</a>
                </li>
                <li class="nav-item active">
                    <a class="nav-link" href="event.php">Event</a>
                </li>
                <li class="nav-item ">
                    <a class="nav-link" href="about.php">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="contact.php">Contact</a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-outline-light" href="loginPage/login.php?tab=login"><b>Log In</b></a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-outline-light" href="loginPage/login.php?tab=signup"><b>Sign Up</b></a>
                </li>
            </ul>
        </div>

    </nav>

    <section class="hero-section">
        <img src="assets/Imus-Campus-scaled.jpg" alt="Graduation Image" style="height: 550px;">
        <div class="overlay">
            <h1><b>EVENT DETAILS</b></h1>

        </div>
    </section>

    <section class="hello-heroes-section" data-aos="fade-up">
        <h2><b>UNIVERSITY EVENTS</b></h2>
    </section>

    <?php
// Check if there are results to display
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Check if the event_id is odd or even

        $address = $row['address'];
        $displayAddress = str_replace(',', '', $address);

        if ($row && $row['event_id'] % 2 == 1) {
            // Odd event_id: display in mission-section
            ?>
                <section class="mission-section" id="about-section">
                    <div class="container" data-aos="fade-up">
                        <div class="row align-items-center">
                            <div class="col-lg-6 mb-4 mb-lg-0 d-flex justify-content-center" data-aos="fade-right">
                                <img src="data:image/jpeg;base64,<?php echo base64_encode($row['image']); ?>" alt="About Us Image" class="img-fluid" style="border-radius: 3%;">
                            </div>
                            <div class="col-lg-6" data-aos="fade-left">
                                <form method="POST">
                                    <input type="hidden" name="event_idd" value="<?php echo $row['event_id']; ?>">
                                    <input type="hidden" name="titlee" value="<?php echo $row['title']; ?>">
                                    <h2><?php echo $row['title']; ?></h2>
                                    <p><?php echo $row['description']; ?></p>
                                    <p class="card-text"><small class="text-muted"><b>DATE: </b><?php echo date('F j, Y', strtotime($row['date_created'])); ?></small></p>
                                    <p class="card-text"><small class="text-muted"><b>TIME: </b><?php echo date('g:i A', strtotime($row['start_time'])); ?> To <?php echo date('g:i A', strtotime($row['end_time'])) ?></small></p>
                                    <p class="card-text"><small class="text-muted"><b>VENUE: </b><?php echo $row['venue']; ?></small></p>
                                    <p class="card-text"><small class="text-muted"><b>ADDRESS: </b><?php echo $displayAddress; ?></small></p>
                                    <!-- Buttons for Donation and View Details -->
                                    <div class="buttons buttons1">
                                        <button type="button" class="btn btn-success btnTWO" data-toggle="modal" data-target="#donateModal">Donate</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </section>
            <?php
} else {
            // Even event_id: display in vision-section
            ?>
                <section class="vision-section" id="vision-section">
                    <div class="container" data-aos="fade-up">
                        <div class="row align-items-center justify-content-center">
                            <div class="col-lg-6" data-aos="fade-left">
                                <form method="POST" action="event.php">
                                    <input type="hidden" name="event_idd" value="<?php echo $row['event_id']; ?>">
                                    <input type="hidden" name="titlee" value="<?php echo $row['title']; ?>">
                                    <h2><?php echo $row['title']; ?></h2>
                                    <p><?php echo $row['description']; ?></p>
                                    <p class="card-text"><small class="text-muted"><b>DATE: </b><?php echo date('F j, Y', strtotime($row['date'])); ?></small></p>
                                    <p class="card-text"><small class="text-muted"><b>TIME: </b><?php echo date('g:i A', strtotime($row['start_time'])); ?> To <?php echo date('g:i A', strtotime($row['end_time'])); ?></small></p>
                                    <p class="card-text"><small class="text-muted"><b>VENUE: </b><?php echo $row['venue']; ?></small></p>
                                    <p class="card-text"><small class="text-muted"><b>ADDRESS: </b><?php echo $displayAddress; ?></small></p>
                                    <!-- Buttons for Donation and View Details -->
                                    <div class="buttons">
                                        <button type="button" class="btn btn-success btnTWO" data-toggle="modal" data-target="#donateModal">Donate</button>
                                    </div>
                                </form>
                            </div>
                            <div class="col-lg-6 mb-4 mb-lg-0 d-flex justify-content-center" data-aos="fade-right">
                                <img src="data:image/jpeg;base64,<?php echo base64_encode($row['image']); ?>" alt="About Us Image" class="img-fluid" style="border-radius: 3%;">
                            </div>
                        </div>
                    </div>
                </section>
    <?php
}
    }
} else {
    echo "<p>No events found.</p>";
}
?>

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
                                <label for="donorName">Name</label>
                                <input type="text" name="name" class="form-control" id="donorName" placeholder="Enter your name" required>
                            </div>
                            <div class="form-group">
                                <label for="donorEmail">Email</label>
                                <input type="email" name="email" class="form-control" id="donorEmail" placeholder="Enter your email" required>
                            </div>
                            <div class="form-group">
                                <label for="donorEmail">Contact Number</label>
                                <input type="number" name="contact_number" class="form-control" id="donorNumber" placeholder="Enter your contact number" required>
                            </div>
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

    <footer class="footer">
        <div class="container">
            <div class="logo-info">
                <img src="assets/cvsu.png" alt="Logo">
                <h5>CAVITE STATE UNIVERSITY - IMUS CAMPUS</h5>
            </div>
            <p>&copy; 2024 Cavite State University - Imus Campus. All Rights Reserved.</p>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
    <script>
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


        // NO TIMER
        function noTimer(title, icon, iconHtml) {
            Swal.fire({
                icon: icon,
                iconHtml: iconHtml, // Custom icon using Font Awesome
                title: title,
                customClass: {
                    popup: 'swal-custom'
                },
                showConfirmButton: true,
                confirmButtonColor: '#4CAF50',
                confirmButtonText: 'OK'
            });
        }

    // ERROR NEGATIVE
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
            confirmButtonText: 'OK'
        });
    }

    // ERROR NEGATIVE
    function thanks(title, text, icon, iconHtml) {
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
            confirmButtonText: 'OK'
        });
    }
    document.getElementById('donationForm').addEventListener('submit', function(event) {
        var donationAmount = document.getElementById('donationAmount').value;

        if (donationAmount < 100) {
            // Prevent form submission
            event.preventDefault();

            // Show SweetAlert warning
            Swal.fire({
                icon: 'warning',
                title: 'Sorry for',
                text: 'Please input an appropriate amount.'
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
    <script>
        AOS.init();
    </script>

</body>

</html>