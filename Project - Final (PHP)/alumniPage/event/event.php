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


//read data from table alumni
$sql = "SELECT * FROM alumni WHERE alumni_id=$account";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$file = $row['picture'];


// Pagination configuration
$records_per_page = 6; // Number of records to display per page
$current_page = isset($_GET['page']) ? $_GET['page'] : 1; // Get current page number, default to 1

// Calculate the limit clause for SQL query
$start_from = ($current_page - 1) * $records_per_page;

$course = $row['course'];

// Set $event_result based on the course
switch ($course) {
    case 'Bachelor of Arts in Journalism':
        $event_result = 'BAJ';
        break;
    case 'Bachelor of Secondary Education':
        $event_result = 'BECEd';
        break;
    case 'Bachelor of Elementary Education':
        $event_result = 'BEEd';
        break;
    case 'Bachelor of Science in Business Management':
        $event_result = 'BSBM';
        break;
    case 'Bachelor of Science in Office Administration':
        $event_result = 'BSOA';
        break;
    case 'Bachelor of Science in Entrepreneurship':
        $event_result = 'BSEntrep';
        break;
    case 'Bachelor of Science in Hospitality Management':
        $event_result = 'BSHM';
        break;
    case 'Bachelor of Science in Information Technology':
        $event_result = 'BSIT';
        break;
    case 'Bachelor of Science in Computer Science':
        $event_result = 'BSCS';
        break;
    case 'Bachelor of Science in Psychology':
        $event_result = 'BSc(Psych)';
        break;
    default:
        $event_result = 'ALL';
}
// Initialize variables

$sql = "SELECT * FROM event WHERE event_for LIKE '%$event_result%' OR event_for = 'ALL'";

// Check if search query is provided
if (isset($_GET['query']) && !empty($_GET['query'])) {
    $search_query = $_GET['query'];
    // Modify SQL query to include search filter
    $sql .= "WHERE event_id LIKE '%$search_query%' 
            OR title LIKE '%$search_query%' 
            OR sched_date LIKE '%$search_query%' 
            OR sched_time LIKE '%$search_query%'
            OR description LIKE '%$search_query%'";
}

$sql .= "LIMIT $start_from, $records_per_page";

$result = $conn->query($sql);

// Count total number of records
$total_records_query = "SELECT COUNT(*) FROM event";
if (isset($_GET['query']) && !empty($_GET['query'])) {
    $total_records_query .= " WHERE event_id LIKE '%$search_query%' 
                              OR title LIKE '%$search_query%' 
                              OR sched_date LIKE '%$search_query%' 
                              OR sched_time LIKE '%$search_query%'
                              OR description LIKE '%$search_query%'";
}
$total_records_result = mysqli_query($conn, $total_records_query);
$total_records_row = mysqli_fetch_array($total_records_result);
$total_records = $total_records_row[0];

$total_pages = ceil($total_records / $records_per_page);

if (isset($_GET['ide'])) {
    $icon = 'success';
    $iconHtml = '<i class="fas fa-check-circle"></i>';
    $title = 'Event archived successfully';

    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            noTextMessage('$title', '$icon', '$iconHtml');
        });
    </script>";
    sleep(2);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
    <title>View Event Details</title>
    <link rel="shortcut icon" href="../../assets/cvsu.png" type="image/svg+xml">
    <link rel="stylesheet" href="./css/event.css">
    <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- FOR PAGINATION -->
    <style>
        /*  DESIGN FOR SEARCH BAR AND PAGINATION */
        table {
            width: 100%;
            border-collapse: collapse;
        }

        td {
            text-align: left;
        }

        .inline {
            border: 1px solid #dddddd;
            padding: 8px;
            font-size: 12px;
            white-space: nowrap;
            /* Prevent text from wrapping */
            overflow: hidden;
            /* Hide overflowing content */
            text-overflow: ellipsis;
            /* Display ellipsis for truncated text */
            max-width: 130px;
            /* Set a max-width to control truncation */

        }

        .act {
            max-width: 235px;
            text-align: center;
            /* Set a max-width to control truncation */
        }

        th {
            background-color: #368DB8;
            font-weight: bold;
            text-align: center;
        }

        .pagination {
            margin-top: 20px;
            text-align: center;

        }

        .pagination a {
            display: inline-block;
            padding: 8px 16px;
            text-decoration: none;
            background-color: #f1f1f1;
            color: black;
            border: 1px solid #ccc;
            margin-right: 5px;
            /* Added margin to separate buttons */
        }

        .pagination a.active {
            background-color: #4CAF50;
            color: white;
            border: 1px solid #4CAF50;
        }

        .pagination a:hover:not(.active) {
            background-color: #ddd;
        }

        .pagination .prev :hover {
            float: left;

            /* Float left for "Previous" link */
        }


        .pagination .next {
            float: right;
            /* Float right for "Next" link */
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
                    <img id="preview" src="data:image/jpeg;base64,<?php echo base64_encode($row['picture']); ?>" style="width:83px;height:83px; border-radius: 100%;border: 2px solid white;">
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
        <div class="container-fluid" id="main-container">
            <div class="container-fluid" id="content-container">
                <div class="container-title">
                    <h4 style="overflow-y: hidden;">Event List</h4>
                </div>
                <div class="congainer-fluid" id="column-header">
                    <div class="row">
                        <div class="col">
                            <div class="search">

                                <form class="d-flex" role="search">
                                    <div class="container-fluid" id="search">
                                        <input class="form-control me-2" type="search" name="query" placeholder="Search Records..." aria-label="Search" value="<?php echo isset($_GET['query']) ? $_GET['query'] : ''; ?>">
                                        <button class="btn btn-outline-success" type="submit" style="padding-left: 30px; padding-right: 39px;">Search</button>
                                    </div>
                                </form>

                            </div>
                        </div>
                        <div class="col" id="add-btn">
                            <div class="add-button">
                                <div class="span">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-content">
                    <table id="example" class="table-responsive table table-striped table-hover ">
                        <thead>

                            <tr>
                                <th scope="col" class="inline">TITLE</th>
                                <th scope="col" class="inline">DATE</th>
                                <th scope="col" class="inline">TIME</th>
                                <th scope="col" class="inline">VENUE</th>
                                <th scope="col" class="inline">ADDRESS</th>
                                <th scope="col" class="inline">DESCRIPTION</th>
                                <th scope="col" class="inline">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result->num_rows > 0) {
                                while ($row = $result->fetch_assoc()) {
                                    $time = date('g:i A', strtotime($row['start_time'])) . " - " . date('g:i A', strtotime($row['end_time']));
                                    $address = $row['address'];
                                    $displayAddress = str_replace(',', '', $address);
                                    $date = date('F j, Y', strtotime($row['date']));


                            ?>
                                    <tr>
                                        <td class="inline"><?php echo $row['title'] ?></td>
                                        <td class="inline"><?php echo $date ?></td>
                                        <td class="inline"><?php echo htmlspecialchars($time) ?></td>
                                        <td class="inline"><?php echo $row['venue'] ?></td>
                                        <td class="inline"><?php echo $displayAddress ?></td>
                                        <td class="inline"><?php echo $row['description'] ?></td>
                                        <?php
                                        echo "
                                                <td class='inline act'>
                                                    <div class='button'>
                                                        <a class='btn btn-info btn-sm' href='./view_event.php?id=$row[event_id]' style='font-size: 11.8px;'>View Details</a>
                                                    </div>
                                                </td>
                                            "; ?>
                                    </tr>
                            <?php
                                }
                            } else {
                                $current_page = 0;
                                echo '<tr><td colspan="12" style="text-align: center;">No records found</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>

                </div>

                <div>
                    <!-- Pagination links -->
                    <div class="pagination" id="content" style="float:right; margin-right:1.5%">
                        <!-- next and previous -->
                        <?php
                        if ($current_page > 1) : ?>
                            <a href="?page=<?= ($current_page - 1); ?>&query=<?php echo isset($_GET['query']) ? $_GET['query'] : ''; ?>" class="prev" style="border-radius:4px;background-color:#368DB8;color:white;margin-bottom:13px;">&laquo; Previous</a>
                        <?php endif; ?>

                        <?php if ($current_page < $total_pages) : ?>
                            <a href="?page=<?= ($current_page + 1); ?>&query=<?php echo isset($_GET['query']) ? $_GET['query'] : ''; ?>" class="next" style="border-radius:4px;background-color:#368DB8;color:white;margin-bottom:13px;">Next &raquo;</a>
                        <?php endif; ?>
                    </div>
                    <p style="margin-left:2%;margin-top:2.3%;">Page <?= $current_page ?> out of <?= $total_pages ?></p>
                </div>
            </div>
        </div>
        <!-- <div class="container-fluid" id="main-container">
                <div class="container-fluid" id="content-container">
                    
                </div>
            </div> -->
        <!-- Script to display preview of selected image -->
        <script>
            function getImagePreview(event) {
                var image = URL.createObjectURL(event.target.files[0]);
                var preview = document.getElementById('preview');
                preview.src = image;
                preview.style.width = '83px';
                preview.style.height = '83px';
            }

            // FOR MESSAGEBOX WITHOUT TEXT AND REDIRECT
            function noTextMessage(title, icon, iconHtml) {
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
                });
            }
        </script>
</body>

</html>