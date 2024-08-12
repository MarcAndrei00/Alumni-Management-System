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
        // User is an alumni
        header('Location: ./alumniPage/dashboard_user.php');
        exit();
    }
    $stmt->close();

    header('Location: ./contact.php');
    exit();
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
            color: #ffffff;
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

        .misiion-section img {
            max-width: 100%;
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
            max-width: 100%;
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

        .hymn-section {
            padding: 100px 40px;
            background-color: #0F5132;
            margin: 0;
        }

        .hymn-section .container {
            position: relative;
            z-index: 1;
            background-color: #fff;
            padding: 60px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 1900px;
        }

        .hymn-section .row {
            position: relative;
            z-index: 1;
            display: flex;
            flex-wrap: wrap;
        }

        .hymn-section .col-lg-6 {
            flex: 1;
            min-width: 100px;
        }

        .hymn-section img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .hymn-section h2 {
            margin-bottom: 20px;
            font-size: 2.5rem;
            font-weight: 700;
        }

        .hymn-section p {
            font-size: 1.3rem;
            line-height: 1.6;
            color: #555;
            text-align: justify;
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
                <li class="nav-item">
                    <a class="nav-link" href="event.php">Event</a>
                </li>
                <li class="nav-item active">
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
            <h1><b>WELCOME ALUMNI</b></h1>
            
        </div>
    </section>

    <section class="hello-heroes-section" data-aos="fade-up">
        <h2><b>UNIVERSITY MISSION</b></h2>
    </section>

    <section class="mission-section" id="about-section">
        <div class="container" data-aos="fade-up">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-right">
                    <img src="./assets/try.jpg" alt="About Us Image" class="img-fluid" style="border-radius: 3%;">
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <h2>MISSION</h2>
                    <p>"Cavite Ctate University shall provide excellent, equitable and relevant educational opportunities in the arts, sciences, and technology through quality instruction and responsive research and development activities. it shall produce professional skilled and morally upright individuals for global competitiveness"</p>
                </div>
            </div>
        </div>
    </section>

    <section class="hello-heroes-section" data-aos="fade-up">
        <h2><b>VISION</b></h2>
    </section>

    <section class="vision-section" id="vision-section">
        <div class="container" data-aos="fade-up">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-right">
                    <img src="./assets/vision.jpg" alt="About Us Image" class="img-fluid" style="border-radius: 3%;">
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <h2>VISION</h2>
                    <p>"The premier university in historic Cavite globally recognized for excellence in character development, academics, research, innovation, and sustainable community engagement."</p>
                </div>
            </div>
        </div>
    </section>

    <section class="hello-heroes-section" data-aos="fade-up">
        <h2><b>UNIVERSITY HYMN</b></h2>
    </section>

    <section class="hymn-section" id="hymn-section">
        <div class="container" data-aos="fade-up">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0 d-flex justify-content-center" data-aos="fade-right">
                    <img src="./assets/hymn.jpg" alt="About Us Image" class="img-fluid" style="height: 600px;">
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <h2>HYMN</h2>
                    <p>
                        "Hail alma mater dear, CVSU all the way through,<br>
                        Seat of hope that we dream of, under the sky so blue.<br>
                        Verdant fields, God's gift to you, open our lives anew,<br>
                        Oh, our hearts, our hands, and minds, too, in your bosom thrive and grow.<br>
                        <br>
                        Seeds of hope are now in bloom, vigilant sons to you have sworn,<br>
                        To CVSU our faith goes on, cradle of hope and bright vision.<br>
                        <br>
                        These sturdy arms that care are the nation builders,<br>
                        Blessed with strength and power to our Almighty we offer.<br>
                        Seeds of hope are now in bloom, vigilant sons to you have sworn,<br>
                        To CVSU our faith goes on, cradle of hope and bright vision.<br>
                        <br>
                        We pray for CVSU, God's blessings be with you,<br>
                        You're the master, we're the builders, CVSU leads forever."
                    </p>
                </div>
            </div>

        </div>
    </section>

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
        AOS.init();
    </script>





</body>

</html>