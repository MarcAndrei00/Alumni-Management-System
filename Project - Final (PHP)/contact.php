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

// Read data from table alumni
$sql = "SELECT * FROM contact_page";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - Cavite State University - Imus Campus</title>
    <link rel="shortcut icon" href="assets/cvsu.png" type="image/svg+xml">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .navbar {
            background-color: #2a9134;
            color: white;
            position: -webkit-sticky;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-brand img {
            height: 70px;
            margin-top: -10px;
        }

        .navbar-brand,
        .navbar-nav .nav-link,
        .user-profile img {
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
            color: #fff
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

        .contact-info-section {
            padding: 50px 0;
            background-color: #f8f9fa;
            text-align: center;
        }

        .contact-info-section h2 {
            margin-bottom: 30px;
        }

        .contact-info-section .contact-details {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }

        .contact-info-section .contact-details div {
            flex: 1;
            min-width: 300px;
            margin: 20px;
        }

        .contact-info-section .contact-details i {
            font-size: 2rem;
            margin-bottom: 10px;
            color: #1B651B;
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

        @media(max-width: 768px) {
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

    <section class="contact-info-section">
        <div class="container">
            <h2><?php echo $row['page_title']; ?></h2>
            <div class="contact-details">
                <img src="data:image/jpeg;base64,<?php echo base64_encode($row['image']); ?>" alt="Cavite State University - Imus Campus" class="img-fluid" width="1980" height="1080">
                <div>
                    <i class="fas fa-map-marker-alt"></i>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-geo-alt-fill" viewBox="0 0 16 16">
                        <path d="M8 16s6-5.686 6-10A6 6 0 0 0 2 6c0 4.314 6 10 6 10m0-7a3 3 0 1 1 0-6 3 3 0 0 1 0 6" />
                    </svg>
                    <h5>Address:</h5>
                    <p><?php echo $row['address']; ?></p>
                    <p></p>
                </div>
                <div>
                    <i class="fas fa-phone"></i>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-telephone-fill" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M1.885.511a1.745 1.745 0 0 1 2.61.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877z" />
                    </svg>
                    <h5>Contact us:</h5>
                    <p>0<?php echo $row['contact']; ?></p>
                </div>
                <div>
                    <i class="fas fa-envelope"></i>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-envelope-fill" viewBox="0 0 16 16">
                        <path d="M.05 3.555A2 2 0 0 1 2 2h12a2 2 0 0 1 1.95 1.555L8 8.414zM0 4.697v7.104l5.803-3.558zM6.761 8.83l-6.57 4.027A2 2 0 0 0 2 14h12a2 2 0 0 0 1.808-1.144l-6.57-4.027L8 9.586zm3.436-.586L16 11.801V4.697z" />
                    </svg>
                    <h5>Email:</h5>
                    <p><?php echo $row['email']; ?></p>
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
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js"></script> <!-- FontAwesome for icons -->
    <!-- Script to display preview of selected image -->
    <script>
        function getImagePreview(event) {
            var image = URL.createObjectURL(event.target.files[0]);
            var preview = document.getElementById('preview');
            preview.src = image;
        }
    </script>
</body>

</html>