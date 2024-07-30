<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event</title>
    <link rel="shortcut icon" href="assets/cvsu.png" type="image/svg+xml">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            margin: 0;
        }
        .navbar {
        background-color: #2a9134; /* Hex color */
        color: white;
        position: -webkit-sticky; /* For Safari */
        position: sticky;
        top: 0;
        z-index: 1000; /* Ensure it stays above other content */
    }
    .navbar-brand img {
        height: 70px; /* Increased logo height */
        margin-top: -10px; /* Overlapping the header */
    }
    .navbar-brand, .navbar-nav .nav-link, .user-profile img {
        color: white;
    }
    .navbar-nav .nav-link {
        margin: 0 5px;
        border: none; /* Removed the border */
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
        .event-section {
            padding: 50px 0;
            background-color: #f8f9fa;
        }
        .event-card {
            margin-bottom: 20px;
        }
        .event-card .card {
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        .event-card img {
            width: 100%; 
            height: 200px;
            object-fit: cover;
        }
        .event-card .card-body {
            flex: 1;
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
        <a class="navbar-brand" href="Homepage.php">
            <img src="assets/cvsu.png" alt="Alumni Management System Logo" height="50">
        </a>
        <a class="navbar-brand" href="Homepage.php">CAVITE STATE UNIVERSITY - IMUS CAMPUS</a>
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
    
    <section class="event-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-4 event-card">
                    <div class="card">
                        <img src="assets/Scholarship.png" class="card-img-top" alt="Event Image">
                        <div class="card-body">
                            <h5 class="card-title">SCHOLARSHIP PROGRAM</h5>
                            <p class="card-text">The Cavite State University - Imus Campus is proud to announce the upcoming Scholarship Awards Ceremony, a prestigious event dedicated to recognizing and celebrating the academic excellence and achievements of our outstanding students.</p>
                            <p class="card-text"><small class="text-muted">2024-04-18 10:00</small></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 event-card">
                    <div class="card">
                        <img src="assets/community.png" class="card-img-top" alt="Event Image">
                        <div class="card-body">
                            <h5 class="card-title">ALUMNI COMMUNITY</h5>
                            <p class="card-text">We are thrilled to invite all CvSU alumni to our upcoming Alumni Community Event. This gathering is a wonderful opportunity for past students to reconnect, share their experiences, and strengthen their bonds with fellow alumni. The event is designed to foster a sense of community and networking among our graduates, providing a platform for both personal and professional growth.</p>
                            <p class="card-text"><small class="text-muted">2024-05-10 15:00</small></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 event-card">
                    <div class="card">
                        <img src="assets/basketball.jpg" class="card-img-top" alt="Event Image">
                        <div class="card-body">
                            <h5 class="card-title">CVSU ALUMNI BASKETBALL</h5>
                            <p class="card-text">Get ready to lace up your sneakers and join us for an exciting day of sportsmanship and camaraderie at the CVSU Alumni Basketball Event. This event is a fantastic opportunity for former CvSU students to reconnect on the basketball court, showcase their athletic skills, and enjoy a fun-filled day with fellow alumni.</p>
                            <p class="card-text"><small class="text-muted">2024-06-20 09:00</small></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 event-card">
                    <div class="card">
                        <img src="assets/gathering.jpg" class="card-img-top" alt="Event Image">
                        <div class="card-body">
                            <h5 class="card-title">ALUMNI GATHERING</h5>
                            <p class="card-text">Rekindle old friendships and make new memories at the CVSU Alumni Gathering, a special event dedicated to bringing together past graduates of Cavite State University - Imus Campus. This event promises an evening of nostalgia, celebration, and future aspirations, allowing alumni to reconnect with their alma mater and each other.</p>
                            <p class="card-text"><small class="text-muted">2024-07-15 18:00</small></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-4 event-card">
                    <div class="card">
                        <img src="assets/Alumni Ball.png" class="card-img-top" alt="Event Image">
                        <div class="card-body">
                            <h5 class="card-title">ALUMNI BALL</h5>
                            <p class="card-text">Step into a night of elegance and sophistication at the CVSU Alumni Ball, an annual event that brings together past graduates of Cavite State University - Imus Campus for an evening of glamour, dancing, and fond memories. This prestigious event is the perfect occasion to celebrate the achievements of our alumni, renew old friendships, and create new connections in a splendid setting.</p>
                            <p class="card-text"><small class="text-muted">2024-08-25 19:00</small></p>
                        </div>
                    </div>
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
        </div>
        <div class="text-center mt-3">
            <p>&copy; Copyright All Reserve</p>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
