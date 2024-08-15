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
            $redirectUrl = './loginPage/verification_code.php';

            echo "<script>
                    document.addEventListener('DOMContentLoaded', function() {
                        alertMessage('$redirectUrl', '$title', '$text', '$icon', '$iconHtml');
                    });
                </script>";
        }
    } else {
        // Redirect to login if no matching user found
        session_destroy();
        header('Location: ./contact.php');
        exit();
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

        @media (max-width: 768px) {
            .navbar-collapse {
                text-align: center;
            }

            .navbar-nav .nav-item {
                margin: 10px 0;
            }
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

        .animated-containers {
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            top: 15%;
        }

        .animated-containers .container {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            max-width: 1200px;
            margin-right: 450px;

        }

        .animated-box h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: white;
            /* Ensure the color contrasts with the background */
        }

        .container h3 i {
            color: white;
            /* Or any other color you prefer */
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

        strong {
            font-weight: bold;
        }

        em {
            font-style: italic;
        }

        table {
            background: #f5f5f5;
            border-collapse: separate;
            box-shadow: inset 0 1px 0 #fff;
            font-size: 16px;
            line-height: 24px;
            margin: 30px auto;
            text-align: center;
            width: 1200px;
        }

        th {
            background: url(https://jackrugile.com/images/misc/noise-diagonal.png), linear-gradient(#7dcea0, #444);
            border-left: 1px solid #555;
            border-right: 1px solid #777;
            border-top: 1px solid #555;
            border-bottom: 1px solid #333;
            box-shadow: inset 0 1px 0 #999;
            color: #fff;
            font-weight: bold;
            padding: 10px 15px;
            position: relative;
            text-shadow: 0 1px 0 #000;
        }

        th:after {
            background: linear-gradient(rgba(255, 255, 255, 0), rgba(255, 255, 255, .08));
            content: '';
            display: block;
            height: 25%;
            left: 0;
            margin: 1px 0 0 0;
            position: absolute;
            top: 25%;
            width: 100%;
        }

        th:first-child {
            border-left: 1px solid #777;
            box-shadow: inset 1px 1px 0 #999;
        }

        th:last-child {
            box-shadow: inset -1px 1px 0 #999;
        }

        td {
            border-right: 1px solid #fff;
            border-left: 1px solid #e8e8e8;
            border-top: 1px solid #fff;
            border-bottom: 1px solid #e8e8e8;
            padding: 10px 15px;
            position: relative;
            transition: all 300ms;
        }

        td:first-child {
            box-shadow: inset 1px 0 0 #fff;
        }

        td:last-child {
            border-right: 1px solid #e8e8e8;
            box-shadow: inset -1px 0 0 #fff;
        }

        tr {
            background: url(https://jackrugile.com/images/misc/noise-diagonal.png);
        }

        tr:nth-child(odd) td {
            background: #f1f1f1 url(https://jackrugile.com/images/misc/noise-diagonal.png);
        }

        tr:last-of-type td {
            box-shadow: inset 0 -1px 0 #fff;
        }

        tr:last-of-type td:first-child {
            box-shadow: inset 1px -1px 0 #fff;
        }

        tr:last-of-type td:last-child {
            box-shadow: inset -1px -1px 0 #fff;
        }

        tbody:hover td {
            color: transparent;
            text-shadow: 0 0 3px #aaa;
        }

        tbody:hover tr:hover td {
            color: #444;
            text-shadow: 0 1px 0 #fff;
        }


        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
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
                <li class="nav-item">
                    <a class="nav-link" href="homepage.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="event.php">Event</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="about.php">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="contact.php">Contact</a>
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
            <h1><b>CONTACT DETAILS</b></h1>
            <div class="animated-containers">
                <div class="container">
                    <div class="animated-box" data-aos="fade-up">
                        <h3><i class="fas fa-map-marker-alt"></i> LOCATION: </h3>
                        <p>Cavite Civic Center,
                            Palico IV, Imus City, Cavite</p>
                    </div>
                    <div class="animated-box" data-aos="fade-up" data-aos-delay="100">
                        <h3><i class="fas fa-envelope"></i> EMAIL: </h3>
                        <p>alumni.management07@gmail.com</>
                        </p>
                    </div>
                    <div class="animated-box" data-aos="fade-up" data-aos-delay="200">
                        <h3><i class="fas fa-phone-alt"></i> CONTACT NO: </h3>
                        <p>09123456789</p>
                    </div>
                </div>

            </div>
        </div>
    </section>
    <section class="mission-section" id="about-section">
        <div class="container" data-aos="fade-up">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0" data-aos="fade-right">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d61826.49974971021!2d120.93253400352037!3d14.418155634799145!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397d25e0e88d16b%3A0xc2f8607cd4512597!2sCavite%20State%20University%20-%20Imus%20Campus!5e0!3m2!1sfil!2sph!4v1722941794812!5m2!1sfil!2sph" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>
                <div class="col-lg-6" data-aos="fade-left">
                    <h2>Need Some Help?</h2><br>
                    <br>
                    <p>If you have any questions or need assistance, we're here to help! Please reach out to us through any
                        of the following methods, and we'll get back to you as soon as possible:
                        <br>
                        <br>
                        <br>
                        <b>Office Hours:</b> Monday to Thursday, 9 AM - 5 PM<br>
                    </p>
                </div>
            </div>
        </div>
    </section>
    <section class="hello-heroes-section" data-aos="fade-up">
        <h2><b>WHO TO CONTACT:</b></h2>
    </section>
    <section class="mission-section" id="about-section">
        <div class="container" data-aos="fade-up">
            <table>
                <thead>
                    <tr>
                        <th>Administrator</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Registrar's Office</strong></td>
                        <td>imus.registrar@cvsu.edu.ph</td>
                    </tr>
                    <tr>
                        <td><strong>Office of the Campus Administrator</strong></td>
                        <td>cvsuimus@cvsu.edu.ph</td>
                    </tr>
                    <tr>
                        <td><strong>Human Resource Development Office</strong></td>
                        <td>hrdoimus@cvsu.edu.ph</td>
                    </tr>
                    <tr>
                        <td><strong>Office of Student Affairs and Services</strong></td>
                        <td>cvsuimus.osas@cvsu.edu.ph</td>
                    </tr>
                    <tr>
                        <td><strong>Admission Office</strong></td>
                        <td>cvsuimus.admissions@cvsu.edu.ph</td>
                    </tr>
                    <tr>
                        <td><strong>Department of Management - Business Management Cluster</strong></td>
                        <td>imus.mngt@cvsu.edu.ph</td>
                    </tr>
                    <tr>
                        <td><strong>Department of Management - Office Administration Cluster/strong></td>
                        <td>imus.ofad@cvsu.edu.ph</td>
                    </tr>
                    <tr>
                        <td><strong>Department Of Computer Studies</strong></td>
                        <td>imus.dcs@cvsu.edu.ph</td>
                    </tr>
                    <tr>
                        <td><strong>Department Of Hospitality Management</strong></td>
                        <td>imus.dhm@cvsu.edu.ph</td>
                    </tr>
                    <tr>
                        <td><strong>Teacher Education Department </strong></td>
                        <td>imus.ted@cvsu.edu.ph</td>
                    </tr>
                    <tr>
                        <td><strong>Department Of Entrepreneurship </strong></td>
                        <td>imus.dent@cvsu.edu.ph</td>
                    </tr>
                    <tr>
                        <td><strong>Department Of Languages And Mass Communication</strong></td>
                        <td>imus.dlmc@cvsu.edu.ph</td>
                    </tr>
                    <tr>
                        <td><strong>Department of Physical Education</strong></td>
                        <td>imus.dpe@cvsu.edu.ph</td>
                    </tr>
                    <tr>
                        <td><strong>Department Of Biological And Physical Sciences</strong></td>
                        <td>imus.dbps@cvsu.edu.ph</td>
                    </tr>
                    <tr>
                        <td><strong>Management Information System Office</strong></td>
                        <td>cvsuimus.miso@cvsu.edu.ph</td>
                    </tr>
                    <tr>
                        <td><strong>Campus Library</strong></td>
                        <td>cvsuimuslibrary@cvsu.edu.ph</td>
                    </tr>
                    <tr>
                        <td><strong>Research Office</strong></td>
                        <td>cvsuimus.research@cvsu.edu.ph</td>
                    </tr>
                    <tr>
                        <td><strong>Extension Office</strong></td>
                        <td>cvsuimus.extension@cvsu.edu.ph</td>
                    </tr>
                    <tr>
                        <td><strong>Guidance Office</strong></td>
                        <td>cvsuimus.guidance@cvsu.edu.ph</td>
                    </tr>
                    <tr>
                        <td><strong>Cashier and Accounting Office </strong></td>
                        <td>cvsuimus.accounting@cvsu.edu.ph</td>
                    </tr>
                    <tr>
                        <td><strong>Health Services Unit / Clinic</strong></td>
                        <td>cvsuimus.clinic@cvsu.edu.ph</td>
                    </tr>
                    <tr>
                        <td><strong>Property and Supplies Office </strong></td>
                        <td>cvsuimus.supplies@cvsu.edu.ph</td>
                    </tr>
                    <tr>
                        <td><strong>Civil Security Office</strong></td>
                        <td>imus.civilsecurity@cvsu.edu.ph</td>
                    </tr>
                    <tr>
                        <td><strong>Scholarship Coordinator</strong></td>
                        <td>imus.scholarship@cvsu.edu.ph</td>
                    </tr>
                    <tr>
                        <td><strong>Gender and Development Office</strong></td>
                        <td>imus.gad@cvsu.edu.ph</td>
                    </tr>
                    <tr>
                        <td><strong>Institutional Development Office</strong></td>
                        <td>cvsuimus.ido@cvsu.edu.ph</td>
                    </tr>
                    <tr>
                        <td><strong>Job Placement Coordinator</strong></td>
                        <td>imus.jobplacement@cvsu.edu.ph</td>
                    </tr>
                    <tr>
                        <td><strong>OJT Coordinator</strong></td>
                        <td>imus.ojt@cvsu.edu.ph</td>
                    </tr>





                    <tr>
                        <td><strong>Campus Budget Officer</strong></td>
                        <td>imus.budget@cvsu.edu.ph</td>
                    </tr>
                    <tr>
                        <td><strong>NSTP Coordinator</strong></td>
                        <td>imus.nstp@cvsu.edu.ph</td>
                    </tr>
                    <tr>
                        <td><strong>Student Development Services Coordinator</strong></td>
                        <td>imus.sds@cvsu.edu.phh</td>
                    </tr>
                    <tr>
                        <td><strong>Records Office</strong></td>
                        <td>imus.records@cvsu.edu.ph</td>
                    </tr>
                    <tr>
                        <td><strong>Instructional Materials Development Coordinator</strong></td>
                        <td>imus.imdu@cvsu.edu.ph</td>
                    </tr>
                    <tr>
                        <td><strong>Knowledge Management Center Coordinator</strong></td>
                        <td>imus.kmc@cvsu.edu.ph</td>
                    </tr>
                    <tr>
                        <td><strong>Alumni Coordinator</strong></td>
                        <td>imus.alumni@cvsu.edu.ph</td>
                    </tr>
                    <tr>
                        <td><strong>Campus Liaison</strong></td>
                        <td>cvsuimus.ido@cvsu.edu.ph</td>
                    </tr>
                    <tr>
                        <td><strong>Job Placement Coordinator</strong></td>
                        <td>imus.liaison@cvsu.edu.ph</td>
                    </tr>
                    <tr>
                        <td><strong>Campus Inspector</strong></td>
                        <td>imus.inspector@cvsu.edu.ph</td>
                    </tr>
                    <tr>
                        <td><strong>Sports Coordinator</strong></td>
                        <td>imus.sports@cvsu.edu.ph</td>
                    </tr>
                    <tr>
                        <td><strong>Discipline Officerr</strong></td>
                        <td>imus.discipline@cvsu.edu.ph</td>
                    </tr>
                    <tr>
                        <td><strong>Campus Disaster Risk Reduction and Managementr</strong></td>
                        <td>imus.drrm@cvsu.edu.ph</td>
                    </tr>
                </tbody>
            </table>

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