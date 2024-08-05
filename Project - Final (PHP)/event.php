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

    header('Location: ./event.php');
    exit();
}

// Read data from table alumni
$sql = "SELECT * FROM event";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Your head content -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event</title>
    <link rel="shortcut icon" href="assets/cvsu.png" type="image/svg+xml">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
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
            font-family: 'Poppins', sans-serif;
        }


        .navbar {
            background: linear-gradient(90deg, rgba(42, 145, 52, 1) 0%, rgba(42, 145, 52, 1) 100%);
            color: white;
            padding: 1.2rem 2rem;
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

        .button-container {
            display: flex;
            justify-content: center;
            /* Center horizontally */
            gap: 15px;
            /* Space between buttons */
            margin-top: 20px;
            /* Optional: add some space above the buttons */
            margin-bottom: 20px;
        }

        .button-container button {
            width: 130px;
            /* Fixed width for both buttons */
            height: 50px;
            border-radius: 5px;
            /* Fully rounded corners */
            padding: 5px 5px;
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

        .card-link {
            text-decoration: none;
            color: inherit;
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
    <!-- Your navbar and other HTML content -->
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
                <li class="nav-item ">
                    <a class="nav-link" href="homepage.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="event.php">Event</a>
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

    <section class="event-section">
        <div class="container">
            <div class="row justify-content-center">
                <?php
                // Check if there are results to display
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                ?>
                        <div class="col-md-6 col-lg-4 event-card">
                            <div class="card">
                                <img src="data:image/jpeg;base64,<?php echo base64_encode($row['image']); ?>" class="card-img-top" alt="Event Image">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo $row['title']; ?></h5>
                                    <p class="card-text"><?php echo $row['description']; ?></p>
                                    <p class="card-text"><small class="text-muted"><b>DATE: </b><?php echo $row['date']; ?></small></p>
                                    <p class="card-text"><small class="text-muted"><b>TIME: </b><?php echo $row['start_time']; ?> To <?php echo $row['end_time']; ?></small></p>
                                    <p class="card-text"><small class="text-muted"><b>VENUE: </b><?php echo $row['venue']; ?></small></p>
                                    <p class="card-text"><small class="text-muted"><b>ADRESS: </b><?php echo $row['address']; ?></small></p>
                                </div>
                                <div class="button-container">
                                    <button type="button" class="btn btn-success">
                                        <i class="fas fa-donate"></i> Donate
                                    </button>
                                    <button type="button" class="btn btn-primary">
                                        <i class="fas fa-info-circle"></i> View Details
                                    </button>
                                </div>
                            </div>
                        </div>
                <?php
                    }
                } else {
                    echo "<p>No events found.</p>";
                }
                ?>
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
            <p>&copy; C2024 Cavite State University - Imus Campus. All Rights Reserved.</p>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- Script to display preview of selected image -->
    <script>
        function getImagePreview(event) {
            var image = URL.createObjectURL(event.target.files[0]);
            var preview = document.getElementById('preview');
            preview.src = image;
            preview.style.width = '200px';
            preview.style.height = '200px';
        }
    </script>
</body>

</html>

<?php
// Close connection
$conn->close();
?>