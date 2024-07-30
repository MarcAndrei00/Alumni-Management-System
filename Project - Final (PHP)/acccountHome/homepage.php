<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cavite State University - Imus Campus</title>
<link rel="shortcut icon" href="assets/cvsu.png" type="image/svg+xml">
<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<style>
    html, body {
        overflow-x: hidden;
        height: 100%;
        margin: 0;
        padding: 0;
    }
    body {
        background-color: #f8f9fa;
    }
    .navbar {
        background-color: #2a9134; 
        color: white;
        position: sticky;
        top: 0;
        z-index: 1000; 
    }
    .navbar-brand img {
        height: 70px;
        margin-top: -10px; 
    }
    .navbar-brand, .navbar-nav .nav-link, .user-profile img {
        color: white;
    }
    .navbar-nav .nav-link {
        margin: 0 5px;
        border: none; 
        border-radius: 5px;
    }
    .navbar-nav .nav-link:hover {
        background-color: #6c757d;
        color: #fff;
    }
    .navbar .btn {
        border: 1px solid white;
        border-radius: 5px;
        color: white;
        margin-left: 10px;
    }
    .navbar .btn:hover {
        background-color: #6c757d;
        color: #fff;
    }
    .user-profile {
        height: 40px;
        width: 40px;
        background-color: white;
        border-radius: 50%;
        overflow: hidden;
    }
    .user-profile img {
        width: 100%;
        height: auto;
    }
    .navbar-nav {
        margin-top: 10px;
    }
    .navbar-text {
        margin-bottom: 10px;
    }
    .hero-section {
        position: relative;
        text-align: center;
        color: white;
    }
    .hero-section img {
        width: 100%;
        height: auto;
    }
    .hero-section .overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
    }
    .hero-section .overlay h1 {
        font-size: 2.5rem;
        margin-bottom: 10px;
    }
    .hero-section .overlay p {
        font-size: 1.2rem;
    }
    .about-section {
        padding: 50px 0;
        background-color: #f8f9fa;
    }
    .about-section h2 {
        text-align: center;
        margin-bottom: 30px;
    }
    .about-section p {
        text-align: center;
        font-size: 18px;
        margin-bottom: 40px;
    }
    .footer {
            background-color: #2e2f34;
            color: white;
            padding: 20px 0;
        }
        .footer .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            text-align: center;
        }
        .footer .logo-info {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-basis: 100%;
            margin: 10px 0;
        }
        .footer .logo-info img {
            height: 50px;
            margin-right: 10px;
        }
        .footer .logo-info h5 {
            margin: 0;
        }
        .card-link {
            text-decoration: none;
            color: inherit;
        }
        .footer .copyright {
            text-align: center;
            margin-top: 10px;
            font-size: 0.9em;
            color: #bbb;
            width: 100%;
        }

        @media(max-width: 768px){
            .navbar-collapse {
                text-align: center;
            }
            .navbar-nav .nav-item {
                margin: 10px 0;
            }
            .footer .container {
                flex-direction: column;
            }
            .footer .logo-info,
            .footer .quick-links {
                text-align: center;
            }
        }

        @media(max-width: 576px) {
            .navbar-brand img {
                height: 50px;
            }
            .navbar .btn {
                display: block;
                margin: 5px 0;
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
                    <a class="nav-link" href="contact.php">Contact</a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-outline-light" href="loginPage/login.php?tab=login">Log In</a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-outline-light" href="loginPage/login.php?tab=signup">Sign Up</a>
                </li>
            </ul>
        </div>
    </nav>

<section class="hero-section">
    <img src="assets/Imus-Campus-scaled.jpg" alt="Graduation Image">
    <div class="overlay">
        <h1>WELCOME TO CAVITE STATE UNIVERSITY <br> IMUS CAMPUS</h1>
    </div>
</section>

<section class="about-section">
    <div class="container">
        <h2>ABOUT US</h2>
        <p>"Welcome to our Alumni Management System, your dedicated platform for staying connected, advancing your career, and giving back to the community. Our system empowers alumni students to effortlessly manage their academic and professional records, aiding in the creation of polished resumes that highlight their achievements. Through curated job opportunities, career resources, and networking events, we strive to support alumni in securing fulfilling employment opportunities. Moreover, we foster a spirit of community engagement by facilitating mentorship programs, volunteer opportunities, and initiatives that contribute to the growth and prosperity of our collective alumni network. Join us in harnessing the power of connectivity and collaboration as we embark on this journey together."</p>
    </div>
</section>

<footer class="footer">
    <div class="container">
        <div class="logo-info">
            <img src="assets/cvsu.png" alt="Logo">
            <h5>CAVITE STATE UNIVERSITY - IMUS CAMPUS</h5>
        </div>
    </div>
    <div class="text-center mt-3">
        <p>&copy; Copyright All Reserve</p>
    </div>
</footer>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
