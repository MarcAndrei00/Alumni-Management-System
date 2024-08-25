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
            $_SESSION['alert'] = 'Unverified';
            sleep(2);
            header('Location: ./loginPage/verification_code.php');
            exit();
        }
    } else {
        // Redirect to login if no matching user found
        session_destroy();
        header('Location: ./homepage.php');
        exit();
    }
}

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
            // Deleting the event from the database
            $event_id = $row['event_id'];

            $sql_archive = "INSERT INTO event_archive (event_id, title, date, start_time, end_time, venue, address, event_for, description, image, going, interested, not_interested, date_created)" .
                " SELECT event_id, title, date, start_time, end_time, venue, address, event_for, description, image, going, interested, not_interested, date_created FROM event WHERE event_id=$event_id";
            $conn->query($sql_archive);

            $sql_delete = "DELETE FROM event WHERE event_id = $event_id";
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
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">



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
            background-color: #e9f5e9;
            margin: 0;
            padding: 0;
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
            color: #fec20c;
            border-right: 0.15em solid #ffffff;
            /* Blinking cursor */
            white-space: nowrap;
            overflow: hidden;
            width: 0;
            animation: typing 3s steps(30, end) forwards, blink-cursor 0.75s step-end infinite, hide-cursor 0s 3.1s forwards;
            font-family: 'Poppins', sans-serif;
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


        .hero-section .overlay h2 {
            font-size: 3rem;
            /* Adjust size */
            margin-top: 10px;
            /* Updated font */
            font-weight: 600;
            color: #ffffff;
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
            /* Updated font */
            font-weight: 700;
            font-family: 'Poppins', sans-serif;
        }

        .hello-heroes-section h2 {
            font-size: 4rem;
            /* Adjust size */
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






        .about-section {
            padding: 100px 40px;
            background-color: #062315;
            margin: 0;
            text-align: center;
            /* Center text for the entire section */
        }

        .about-section .container {
            position: relative;
            z-index: 1;
            background-color: #fff;
            padding: 60px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 2000px;
            margin: 0 auto;
            /* Center container horizontally */
        }

        .about-section .row {
            position: relative;
            z-index: 1;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            /* Center content within the row */
        }

        .about-section .col-lg-12 {
            flex: 1;
            min-width: 300px;
        }

        .about-section h2 {
            margin-bottom: 20px;
            font-size: 2.5rem;
            font-weight: 700;
            text-align: center;
            /* Center text for heading */
            font-family: 'Poppins', sans-serif;
        }

        .about-section p {
            font-size: 1.3rem;
            line-height: 1.6;
            font-family: 'Poppins', sans-serif;
            color: #555;
            text-align: center;
            /* Center text for paragraph */
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
                font-family: 'Poppins', sans-serif;
            }

            .hello-heroes-section h2 {
                font-size: 2rem;
                font-family: 'Poppins', sans-serif;
            }

            .about-section h2 {
                font-size: 2rem;
                font-family: 'Poppins', sans-serif;
            }

            .about-section p {
                font-size: 1r'Poppins', sans-serif;
            }

            .about-section .container {
                padding: 20px;
            }

            .about-section h2 i {
                margin-right: 10px;
                color: #007bff;
                /* Adjust color as needed */
                font-size: 2rem;
                /* Adjust size as needed */
            }


            .alumni-section h2 {
                font-size: 2rem;
                font-family: 'Poppins', sans-serif;
            }

            .alumni-section p {
                font-size: 1r'Poppins', sans-serif;
            }

            .alumni-section .container {
                padding: 20px;
                color: #fec20c;
            }

            .alumni-section img {
                width: 100%;
                height: auto;
                opacity: 0.7;
                display: block;
            }



            .announcement-container {
                display: flex;
                gap: 15px;
                /* Space between the cards */
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
                gap: 15px;
                padding: 15px;
                margin-top: auto;
            }


            .announcement-card {
                background-color: #ffffff;
                border: 1px solid #dee2e6;
                border-radius: 8px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                transition: transform 0.3s, box-shadow 0.3s;
                overflow: hidden;
                position: relative;
                text-align: center;
                padding: 15px;
                padding-top: 10px;
                margin-top: 500px;
            }

            .announcement-card img {
                width: 100%;
                height: auto;
                max-height: 200px;
                /* Limit the height */
                object-fit: cover;
                /* Ensure the image covers the area without distortion */
                padding-top: 100px;
                margin-top: 500px;
            }

            .card-body {
                padding: 15px;
                color: #333;
                margin-top: 100px;
            }

            .card-title {
                font-weight: 700;
                font-size: 18px;
                margin-bottom: 10px;
            }

            .card-date {
                font-size: 14px;
                color: #777;
            }

            .card-footer {
                background-color: #f1f1f1;
                border-radius: 0 0 8px 8px;
                padding: 10px;
                text-align: center;
            }

            .alumni-section {
                padding: 100px 40px;
                background-color: #062315;
                margin: 0;
                text-align: center;
                /* Center text for the entire section */

            }

            .alumni-section .container {
                position: relative;
                z-index: 1;
                background-color: #ffffff;
                padding: 60px;
                border-radius: 10px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                max-width: 2000px;
                margin: 0 auto;
                /* Center container horizontally */

            }

            .alumni-section .row {
                position: relative;
                z-index: 1;
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                /* Center content within the row */
            }

            .alumni-section .col-lg-6 {
                flex: 1;
                min-width: 300px;
            }

            .alumni-section h2 {
                margin-bottom: 20px;
                font-size: 2.5rem;
                font-weight: 700;
                text-align: center;
                /* Center text for heading */
                font-family: 'Poppins', sans-serif;
            }

            .alumni-section p {
                font-size: 1.3rem;
                line-height: 1.6;
                font-family: 'Poppins', sans-serif;
                color: #555;
                text-align: center;
                /* Center text for paragraph */
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
    </style>
</head>

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
            <li class="nav-item active">
                <a class="nav-link" href="homepage.php">Home</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="event.php">Event</a>
            </li>
            <li class="nav-item">
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
    <img src="assets/Imus-Campus-scaled.jpg" alt="Graduation Image">
    <div class="overlay">
        <h1>WELCOME ALUMNI</h1>
        <h2>TO CAVITE STATE UNIVERSITY - IMUS CAMPUS</h2>
    </div>
</section>


<section class="hello-heroes-section" data-aos="fade-up">
    <h2><b>HELLO HEROES!</b></h2>
</section>


<img src="assets/ss.jpg" alt="About Us Image" class="img-fluid animated-image">



<section class="hello-heroes-section" data-aos="fade-up">
    <h2><b>OVERVIEW</b></h2>
</section>

<section class="about-section">
    <div class="container" data-aos="fade-up">
        <div class="row align-items-center">
            <div class="col-lg-12" data-aos="fade-left">
                <p><b>"Welcome to our Alumni Management System, your dedicated platform for staying connected, advancing your career, and giving back to the community. Our system empowers alumni students to effortlessly manage their academic and professional records, aiding in the creation of polished resumes that highlight their achievements. Through curated job opportunities, career resources, and networking events, we strive to support alumni in securing fulfilling employment opportunities. Moreover, we foster a spirit of community engagement by facilitating mentorship programs, volunteer opportunities, and initiatives that contribute to the growth and prosperity of our collective alumni network. Join us in harnessing the power of connectivity and collaboration as we embark on this journey together."</b></p>
            </div>
        </div>
    </div>
</section>




<section class="hello-heroes-section" data-aos="fade-up">
    <h2><i class="fas fa-shield-alt"></i><b>QUALITY POLICY</b></h2>
</section>

<section class="about-section">
    <div class="container" data-aos="fade-up">
        <div class="row align-items-center">
            <div class="col-lg-12" data-aos="fade-left">
                <p><b>"We Commit to the highest standards of education, value our stakeholders, Strive for continual improvement of our products and services, and Uphold the University’s tenets of Truth, Excellence, and Service to produce globally competitive and morally upright individuals."</b></p>
            </div>
        </div>
    </div>
</section>

<style>
    .slider {
        position: relative;
        width: 100%;
        overflow: hidden;
    }

    .slide {
        display: none;
        width: 100%;
        transition: opacity 1s ease-in-out;
    }

    .slide:target {
        display: block;
    }

    .slider-nav {
        text-align: center;
        margin-top: 20px;
    }

    .dot {
        display: inline-block;
        width: 12px;
        height: 12px;
        margin: 0 5px;
        background-color: #008000;
        border-radius: 50%;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .dot:hover {
        background-color: #005500;
    }

    .text-section {
        padding: 20px;
        font-family: 'Poppins', sans-serif;
        color: white;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(255, 255, 255, 0.3);
        transition: background-color 0.3s ease;
    }

    .text-section h2 {
        font-weight: bold;
        margin-top: 0;
    }

    .text-section h3 {
        font-weight: bold;
        margin-bottom: 20px;
    }

    .text-section p {
        font-size: 16px;
        line-height: 1.6;
        text-align: justify;
    }

    .animated-img {
        width: 100%;
        height: auto;
        transition: transform 0.3s ease-in-out;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
      
    }

    .animated-img:hover {
        transform: scale(1.05);
    }
</style>



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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        AOS.init();
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
    </script>
    <script src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>


</body>

</html>