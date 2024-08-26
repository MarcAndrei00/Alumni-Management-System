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
        $user = $user_result->fetch_assoc();
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
            header('Location: ../../alumniPage/dashboard_user.php');
            exit();
        } else {

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




$records_per_page = 20;
$current_page = isset($_GET['page']) ? $_GET['page'] : 1;
$start_from = ($current_page - 1) * $records_per_page;

// Retrieve filter values from GET request and update session variables
if (isset($_GET['course'])) {
    $_SESSION['course_filter'] = $_GET['course'];
}
if (isset($_GET['batch'])) {
    $_SESSION['batch_filter'] = $_GET['batch'];
}
if (isset($_GET['status'])) {
    $_SESSION['status_filter'] = $_GET['status'];
}
if (isset($_GET['alumni_stat'])) {
    $_SESSION['alumni_stat'] = $_GET['alumni_stat'];
}

// Retrieve filter values from session variables
$course_filter = isset($_SESSION['course_filter']) ? $_SESSION['course_filter'] : '';
$batch_filter = isset($_SESSION['batch_filter']) ? $_SESSION['batch_filter'] : '';
$status_filter = isset($_SESSION['status_filter']) ? $_SESSION['status_filter'] : 'register';
$alumni_stat = isset($_SESSION['alumni_stat']) ? $_SESSION['alumni_stat'] : '';

// Base SQL query depending on the status
if ($status_filter === 'unregister') {
    $sql = "SELECT * FROM list_of_graduate WHERE 1=1";
    if (!empty($course_filter) && $course_filter != 'all') {
        $sql .= " AND course = '$course_filter'";
    }
    if (!empty($batch_filter)) {
        $batcher = '-' . $batch_filter;
        $sql .= " AND batch LIKE '%$batcher%'";
    }
} elseif ($status_filter === 'inactive') {
    $sql = "SELECT * FROM alumni_archive WHERE 1=1";
    if (!empty($course_filter) && $course_filter != 'all') {
        $sql .= " AND course = '$course_filter'";
    }
    if (!empty($batch_filter) && $batch_filter != 'all') {
        $sql .= " AND batch_endYear = '$batch_filter'";
    }
} else {
    $sql = "SELECT * FROM alumni WHERE 1=1";
    if (!empty($course_filter) && $course_filter != 'all') {
        $sql .= " AND course = '$course_filter'";
    }
    if (!empty($batch_filter) && $batch_filter != 'all') {
        $sql .= " AND batch_endYear = '$batch_filter'";
    }
    if (!empty($alumni_stat) && $alumni_stat != 'all') {
        $sql .= " AND status = '$alumni_stat'";
    }
}

// Add ordering and pagination
$sql .= " ORDER BY lname ASC LIMIT $start_from, $records_per_page";
$result = $conn->query($sql);

// Count total number of records
if ($status_filter === 'unregister') {
    $total_records_query = "SELECT COUNT(*) FROM list_of_graduate WHERE 1=1";
    if (!empty($course_filter) && $course_filter != 'all') {
        $total_records_query .= " AND course = '$course_filter'";
    }
    if (!empty($batch_filter)) {
        $total_records_query .= " AND batch LIKE '%$batch_filter%'";
    }
} elseif ($status_filter === 'inactive') {
    $total_records_query = "SELECT COUNT(*) FROM alumni_archive WHERE 1=1";
    if (!empty($course_filter) && $course_filter != 'all') {
        $total_records_query .= " AND course = '$course_filter'";
    }
    if (!empty($batch_filter) && $batch_filter != 'all') {
        $total_records_query .= " AND batch_endYear = '$batch_filter'";
    }
} else {
    $total_records_query = "SELECT COUNT(*) FROM alumni WHERE 1=1";
    if (!empty($course_filter) && $course_filter != 'all') {
        $total_records_query .= " AND course = '$course_filter'";
    }
    if (!empty($batch_filter) && $batch_filter != 'all') {
        $total_records_query .= " AND batch_endYear = '$batch_filter'";
    }
    if (!empty($alumni_stat) && $alumni_stat != 'all') {
        $total_records_query .= " AND status = '$alumni_stat'";
    }
}

$total_records_result = $conn->query($total_records_query);
$total_records_row = $total_records_result->fetch_array();
$total_records = $total_records_row[0];

$total_pages = ceil($total_records / $records_per_page);

// Determine the title based on the selected status
switch ($status_filter) {
    case 'unregister':
        $title = 'Lists of Unregistered Alumni';
        break;
    case 'inactive':
        $title = 'Lists of Inactive Alumni';
        break;
    case 'register':
        $title = 'Lists of Registered Alumni';
        break;
}

// PDF
$records_per_page_pdf = 500000;
$current_page_pdf = isset($_GET['page']) ? $_GET['page'] : 1;
$start_from_pdf = ($current_page_pdf - 1) * $records_per_page_pdf;

$course_filter_pdf = $course_filter;
$batch_filter_pdf = $batch_filter;
$status_filter_pdf = $status_filter; // Default to 'Registered'
$alumni_stat_pdf = $alumni_stat;

// Base SQL query depending on the status
if ($status_filter_pdf === 'unregister') {
    $sql_pdf = "SELECT * FROM list_of_graduate WHERE 1=1";
    if (!empty($course_filter_pdf) && $course_filter_pdf != 'all') {
        $sql_pdf .= " AND course = '$course_filter_pdf'";
    }
    if (!empty($batch_filter_pdf)) {
        // Use LIKE to match the batch format, assuming batch is stored in '2020-2021' format
        $batcher_pdf = '-' . $batch_filter_pdf;
        $sql_pdf .= " AND batch LIKE '%$batcher_pdf%'";
    }
} elseif ($status_filter_pdf === 'inactive') {
    $sql_pdf = "SELECT * FROM alumni_archive WHERE 1=1";
    if (!empty($course_filter_pdf) && $course_filter_pdf != 'all') {
        $sql_pdf .= " AND course = '$course_filter_pdf'";
    }
    if (!empty($batch_filter_pdf) && $batch_filter_pdf != 'all') {
        // Use batch_endYear for inactive
        $sql_pdf .= " AND batch_endYear = '$batch_filter_pdf'";
    }
} else {
    // Default to 'register' which means Registered Alumni
    $sql_pdf = "SELECT * FROM alumni WHERE 1=1";
    if (!empty($course_filter_pdf) && $course_filter_pdf != 'all') {
        $sql_pdf .= " AND course = '$course_filter_pdf'";
    }
    if (!empty($batch_filter_pdf) && $batch_filter_pdf != 'all') {
        // Use batch_endYear for registered
        $sql_pdf .= " AND batch_endYear = '$batch_filter_pdf'";
    }
    if (!empty($alumni_stat_pdf) && $alumni_stat_pdf != 'all') {
        // Use batch_endYear for registered
        $sql_pdf .= " AND status = '$alumni_stat_pdf'";
    }
}

// Add ordering and pagination
$sql_pdf .= " ORDER BY lname ASC";
$result_pdf = $conn->query($sql_pdf);

// Count total number of records
if ($status_filter_pdf === 'unregister') {
    $total_records_query_pdf = "SELECT COUNT(*) FROM list_of_graduate WHERE 1=1";
    if (!empty($course_filter_pdf) && $course_filter_pdf != 'all') {
        $total_records_query_pdf .= " AND course = '$course_filter_pdf'";
    }
    if (!empty($batch_filter_pdf)) {
        // Count with LIKE to match the batch format
        $total_records_query_pdf .= " AND batch LIKE '%$batch_filter_pdf%'";
    }
} elseif ($status_filter_pdf === 'inactive') {
    $total_records_query_pdf = "SELECT COUNT(*) FROM alumni_archive WHERE 1=1";
    if (!empty($course_filter_pdf) && $course_filter_pdf != 'all') {
        $total_records_query_pdf .= " AND course = '$course_filter_pdf'";
    }
    if (!empty($batch_filter_pdf) && $batch_filter_pdf != 'all') {
        // Count with batch_endYear for inactive
        $total_records_query_pdf .= " AND batch_endYear = '$batch_filter_pdf'";
    }
} else {
    // Default to 'register'
    $total_records_query_pdf = "SELECT COUNT(*) FROM alumni WHERE 1=1";
    if (!empty($course_filter_pdf) && $course_filter_pdf != 'all') {
        $total_records_query_pdf .= " AND course = '$course_filter_pdf'";
    }
    if (!empty($batch_filter_pdf) && $batch_filter_pdf != 'all') {
        // Count with batch_endYear for registered
        $total_records_query_pdf .= " AND batch_endYear = '$batch_filter_pdf'";
    }
    if (!empty($alumni_stat_pdf) && $alumni_stat_pdf != 'all') {
        // Count with batch_endYear for registered
        $total_records_query_pdf .= " AND status = '$alumni_stat_pdf'";
    }
}

$total_records_result_pdf = $conn->query($total_records_query_pdf);
$total_records_row_pdf = $total_records_result_pdf->fetch_array();
$total_records_pdf = $total_records_row_pdf[0];

$total_pages_pdf = ceil($total_records_pdf / $records_per_page_pdf);

$status_filter_pdf = isset($_GET['status']) ? $_GET['status'] : 'register'; // Default to 'register'
// Determine the title based on the selected status
switch ($status_filter_pdf) {
    case 'unregister':
        $title_pdf = 'Lists of Unregistered Alumni';
        break;
    case 'inactive':
        $title_pdf = 'Lists of Inactive Alumni';
        break;
    case 'register':
    default:
        $title_pdf = 'Lists of Registered Alumni';
        break;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
    <title>Report</title>
    <link rel="shortcut icon" href="../../assets/cvsu.png" type="image/svg+xml">
    <link rel="stylesheet" href="./css/report.css">
    <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        /* FOR SWEETALERT */
        .swal2-popup {
            padding-bottom: 30px;
            /* Adjust the padding as needed */
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
        }

        .confirm-button-class {
            background-color: #e03444 !important;
            color: white;
        }

        .cancel-button-class {
            background-color: #ffc404 !important;
            color: white;
        }

        /* FOR SWEETALERT  END LINE*/


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
            max-width: 125px;
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

        .dropdown-menu {
            max-height: absolute;
            /* Limit the height of the dropdown menu */
            overflow-y: auto;
            /* Add scroll if content exceeds height */
            padding: 0;
            /* Remove extra padding if needed */
        }

        .dropdown-menu label {
            display: block;
            /* Ensure each label takes up a full line */
            margin-bottom: 5px;
            /* Space between items */
            white-space: nowrap;
            /* Prevent text from wrapping */
        }

        .dropdown-menu input[type="checkbox"] {
            margin-right: 10px;
            /* Space between checkbox and label text */
        }

        #statusSelect {
            width: 410px;
            /* Adjust the width as needed */
            min-width: 150px;
            /* Ensure the width is not too small */
        }

        #alumniSelect {
            width: 195px;
            /* Adjust the width as needed */
            min-width: 150px;
            /* Ensure the width is not too small */
        }

        .dropdown-container {
            display: flex;
            justify-content: flex-end;
            /* Aligns child elements to the right */
            align-items: center;
            /* Ensures the container takes up the full width */
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
                <i class="bi bi-person-circle"></i>
                <h4><?php echo $user['fname']; ?></h4>
                <small style="color: white;"><?php echo $user['email']; ?></small>
            </div>

            <div class="side-menu">
                <ul>
                    <li>
                        <a href="../dashboard_coor.php">
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
                        <a href="../alumni/alumni.php">
                            <span class="las la-th-list" style="color:#fff"></span>
                            <small>ALUMNI</small>
                        </a>
                    </li>
                    <li>
                        <a href="../event/event.php">
                            <span class="las la-calendar" style="color:#fff"></span>
                            <small>EVENT</small>
                        </a>
                    </li>
                    <li>
                        <a href="../settings/about.php">
                            <span class="las la-cog" style="color:#fff"></span>
                            <small>SETTINGS</small>
                        </a>
                    </li>
                    <li>
                        <a href="./report.php" class="active">
                            <span class="las la-clipboard-check" style="color:#fff"></span>
                            <small>REPORT</small>
                        </a>
                    </li>
                    <li>
                        <a href="../archive/alumni_archive.php">
                            <span class="las la-archive" style="color:#fff"></span>
                            <small>ARCHIVE</small>
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
                    <span class="las la-bars bars" style="color: white;"></span>
                </label>

                <div class="header-menu">
                    <label for="">
                    </label>

                    <div class="user">
                        <a href="../logout.php">
                            <span class="las la-power-off" style="font-size: 30px; border-left: 1px solid #fff; padding-left:10px; color:#fff"></span>
                        </a>
                    </div>
                </div>
            </div>
        </header>
        <main>
            <div class="page-header">
                <h1><strong>Report</strong></h1>
            </div>

            <div class="container mt-4 p-3 shadow bg-white rounded d-flex justify-content-between">
                <div>
                    <button id="download-pdf" class="btn btn-primary" style="display:none;">Download as PDF</button>
                    <button id="refresh-page" class="btn btn-secondary">Refresh</button>
                </div>
                <div>
                    <button id="another-page" class="btn btn-success" onclick="window.location.href='../report/report.php'">Graphs</button>
                </div>
            </div>

            <!-- CONTAINER FOR LIST -->
            <div class="container mt-4 p-3 shadow bg-white rounded">
                <div class="container mt-5">
                    <div id="hidden-content" style="display:none;">
                        <img src="../../assets/head.jpg" id="header-image" style="width:100%; height:auto;">
                        <br>
                        <h2><strong><?php echo $title; ?></strong></h2>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <h2 class="mb-0 flex-grow-1"><strong><?php echo $title; ?></strong></h2>
                        <?php if ($status_filter === 'register'): ?>
                            <div class="dropdown-container ms-3">
                                <label for="alumniSelect" class="form-label d-none">Status</label>
                                <select id="alumniSelect" class="form-select" name="alumni_stat">
                                    <option value="">All Status</option>
                                    <option value="Verified" <?php echo ($alumni_stat == 'Verified') ? 'selected' : ''; ?>>Verified</option>
                                    <option value="Unverified" <?php echo ($alumni_stat == 'Unverified') ? 'selected' : ''; ?>>Unverified</option>
                                </select>
                            </div>
                        <?php endif; ?>

                        <div class="dropdown-container ms-3">
                            <label for="statusSelect" class="form-label d-none">Status</label>
                            <select id="statusSelect" class="form-select" name="status">
                                <option value="register" <?php echo ($status_filter == 'register') ? 'selected' : ''; ?>>Registered</option>
                                <option value="unregister" <?php echo ($status_filter == 'unregister') ? 'selected' : ''; ?>>Unregistered</option>
                                <option value="inactive" <?php echo ($status_filter == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <form method="GET" action="">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="courseSelect" class="form-label">Course</label>
                                <select id="courseSelect" class="form-select" name="course">
                                    <option value="" selected>All Courses</option>
                                    <option value="Bachelor of Arts in Journalism" <?php echo ($course_filter == 'Bachelor of Arts in Journalism') ? 'selected' : ''; ?>>Bachelor of Arts in Journalism</option>
                                    <option value="Bachelor of Secondary Education" <?php echo ($course_filter == 'Bachelor of Secondary Education') ? 'selected' : ''; ?>>Bachelor of Secondary Education</option>
                                    <option value="Bachelor of Elementary Education" <?php echo ($course_filter == 'Bachelor of Elementary Education') ? 'selected' : ''; ?>>Bachelor of Elementary Education</option>
                                    <option value="Bachelor of Science in Business Management" <?php echo ($course_filter == 'Bachelor of Science in Business Management') ? 'selected' : ''; ?>>Bachelor of Science in Business Management</option>
                                    <option value="Bachelor of Science in Office Administration" <?php echo ($course_filter == 'Bachelor of Science in Office Administration') ? 'selected' : ''; ?>>Bachelor of Science in Office Administration</option>
                                    <option value="Bachelor of Science in Entrepreneurship" <?php echo ($course_filter == 'Bachelor of Science in Entrepreneurship') ? 'selected' : ''; ?>>Bachelor of Science in Entrepreneurship</option>
                                    <option value="Bachelor of Science in Hospitality Management" <?php echo ($course_filter == 'Bachelor of Science in Hospitality Management') ? 'selected' : ''; ?>>Bachelor of Science in Hospitality Management</option>
                                    <option value="Bachelor of Science in Information Technology" <?php echo ($course_filter == 'Bachelor of Science in Information Technology') ? 'selected' : ''; ?>>Bachelor of Science in Information Technology</option>
                                    <option value="Bachelor of Science in Computer Science" <?php echo ($course_filter == 'Bachelor of Science in Computer Science') ? 'selected' : ''; ?>>Bachelor of Science in Computer Science</option>
                                    <option value="Bachelor of Science in Psychology" <?php echo ($course_filter == 'Bachelor of Science in Psychology') ? 'selected' : ''; ?>>Bachelor of Science in Psychology</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="batchSelect" class="form-label">Year Graduated</label>
                                <select id="batchSelect" class="form-select" name="batch">
                                    <option value="" selected>All Batches</option>
                                    <?php
                                    for ($year = 2004; $year <= date("Y"); $year++) {
                                        echo "<option value='$year' " . ($batch_filter == $year ? 'selected' : '') . ">$year</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </form>
                    <br>
                    <div class="table-responsive" id="content-container">
                        <div class="container-fluid" id="column-header">
                            <div class="row">
                                <!-- Left side: Search bar and dropdown (col-8) -->
                                <br><br><br>
                                <div class="table-content">
                                    <table id="example" class="table-responsive table table-striped table-hover ">
                                        <thead>
                                            <tr>
                                                <th scope="col" class="inline">STUDENT ID</th>
                                                <th scope="col" class="inline">NAME</th>
                                                <th scope="col" class="inline">COURSE</th>
                                                <th scope="col" class="inline">BATCH</th>
                                                <th scope="col" class="inline">EMAIL</th>
                                                <th scope="col" class="inline">GENDER</th>
                                                <th scope="col" class="inline">STATUS</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            if ($result->num_rows > 0) {
                                                while ($row = $result->fetch_assoc()) {
                                                    // Determine the batch value based on the SQL query
                                                    if ($status_filter == 'unregister') {
                                                        // If the status filter is 'unregister', use 'batch' column from list_of_graduate
                                                        $batch = $row["batch"];
                                                        $status = 'Unregistered';
                                                    } else if ($status_filter == 'inactive') {
                                                        // If the status filter is 'unregister', use 'batch' column from list_of_graduate
                                                        $batch = $row["batch_startYear"] . " - " . $row["batch_endYear"];
                                                        $status = 'Inactive';
                                                    } else {
                                                        // For other statuses, use 'batch_startYear' and 'batch_endYear' from alumni
                                                        $batch = $row["batch_startYear"] . " - " . $row["batch_endYear"];
                                                    }

                                                    // Handle the full name format
                                                    if (!empty($row["mname"])) {
                                                        $fullname = $row["lname"] . ", " . $row["fname"] . ", " . $row["mname"] . ".";
                                                    } else {
                                                        $fullname = $row["lname"] . ", " . $row["fname"];
                                                    }

                                                    // Determine the status display and color
                                                    if ($status_filter == 'unregister') {
                                                        $statusDisplay = 'Unregistered';
                                                        $color = '#e6b800';
                                                    } else if ($status_filter == 'inactive') {
                                                        $statusDisplay = 'Inactive';
                                                        $color = '#e6b800';
                                                    } else {
                                                        $statusDisplay = ($row['status'] == 'Verified' || $row['status'] == 'Unverified') ? $row['status'] : $status;
                                                        $color = ($row['status'] == 'Verified') ? 'green' : 'red';
                                                    }
                                            ?>
                                                    <tr>
                                                        <td class="inline"><?php echo $row['student_id']; ?></td>
                                                        <td class="inline"><?php echo htmlspecialchars($fullname); ?></td>
                                                        <td class="inline"><?php echo $row['course']; ?></td>
                                                        <td class="inline"><?php echo htmlspecialchars($batch); ?></td>
                                                        <td class="inline"><?php echo $row['email']; ?></td>
                                                        <td class="inline"><?php echo $row['gender']; ?></td>
                                                        <td class="inline" style="color: <?php echo $color; ?>">
                                                            <?php echo $statusDisplay; ?>
                                                        </td>
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
                                <div style="display: flex; justify-content: space-between; align-items: center; padding-right: 1.5%; padding-left: 1.5%;">
                                    <p style="margin: 0;">Page <?= $current_page ?> out of <?= $total_pages ?></p>
                                    <div class="pagination" id="content">
                                        <?php if ($current_page > 1) : ?>
                                            <a href="?page=<?= ($current_page - 1); ?>&query=<?php echo isset($_GET['query']) ? $_GET['query'] : ''; ?>" class="prev" style="border-radius:4px;background-color:#368DB8;color:white;margin-bottom:13px;">&laquo; Previous</a>
                                        <?php endif; ?>

                                        <?php if ($current_page < $total_pages) : ?>
                                            <a href="?page=<?= ($current_page + 1); ?>&query=<?php echo isset($_GET['query']) ? $_GET['query'] : ''; ?>" class="next" style="border-radius:4px;background-color:#368DB8;color:white;margin-bottom:13px;">Next &raquo;</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div><br><br>
                        </div>
                    </div>
                </div>
            </div>
            <!-- CONTAINER END -->

            <!-- PDF -->
            <div class="container mt-4 p-3 shadow bg-white rounded" style="display:none;">
                <div class="container mt-5">
                    <div id="hidden-content" style="display:none;">
                        <img src="../../assets/head.jpg" id="header-image" style="width:100%; height:auto;">
                        <br>
                        <h2><strong><?php echo $title_pdf; ?></strong></h2>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <h2 class="mb-0 flex-grow-1"><strong><?php echo $title_pdf; ?></strong></h2>
                        <div class="dropdown-container ms-3">
                            <label for="alumniSelect" class="form-label d-none">Status</label>
                            <select id="alumniSelect" class="form-select" name="alumni_stat_pdf">
                                <option value="">All Status</option>
                                <option value="Verified" <?php echo ($alumni_stat_pdf == 'Verified') ? 'selected' : ''; ?>>Verified</option>
                                <option value="Unverified" <?php echo ($alumni_stat_pdf == 'Unverified') ? 'selected' : ''; ?>>Unverified</option>
                            </select>
                        </div>

                        <div class="dropdown-container ms-3">
                            <label for="statusSelect" class="form-label d-none">Status</label>
                            <select id="statusSelect" class="form-select" name="status_pdf">
                                <option value="register" <?php echo ($status_filter_pdf == 'register') ? 'selected' : ''; ?>>Registered</option>
                                <option value="unregister" <?php echo ($status_filter_pdf == 'unregister') ? 'selected' : ''; ?>>Unregistered</option>
                                <option value="inactive" <?php echo ($status_filter_pdf == 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <form method="GET" action="">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="courseSelect" class="form-label">Course</label>
                                <select id="courseSelect" class="form-select" name="course_pdf">
                                    <option value="" selected>All Courses</option>
                                    <option value="Bachelor of Arts in Journalism" <?php echo ($course_filter_pdf == 'Bachelor of Arts in Journalism') ? 'selected' : ''; ?>>Bachelor of Arts in Journalism</option>
                                    <option value="Bachelor of Secondary Education" <?php echo ($course_filter_pdf == 'Bachelor of Secondary Education') ? 'selected' : ''; ?>>Bachelor of Secondary Education</option>
                                    <option value="Bachelor of Elementary Education" <?php echo ($course_filter_pdf == 'Bachelor of Elementary Education') ? 'selected' : ''; ?>>Bachelor of Elementary Education</option>
                                    <option value="Bachelor of Science in Business Management" <?php echo ($course_filter_pdf == 'Bachelor of Science in Business Management') ? 'selected' : ''; ?>>Bachelor of Science in Business Management</option>
                                    <option value="Bachelor of Science in Office Administration" <?php echo ($course_filter_pdf == 'Bachelor of Science in Office Administration') ? 'selected' : ''; ?>>Bachelor of Science in Office Administration</option>
                                    <option value="Bachelor of Science in Entrepreneurship" <?php echo ($course_filter_pdf == 'Bachelor of Science in Entrepreneurship') ? 'selected' : ''; ?>>Bachelor of Science in Entrepreneurship</option>
                                    <option value="Bachelor of Science in Hospitality Management" <?php echo ($course_filter_pdf == 'Bachelor of Science in Hospitality Management') ? 'selected' : ''; ?>>Bachelor of Science in Hospitality Management</option>
                                    <option value="Bachelor of Science in Information Technology" <?php echo ($course_filter_pdf == 'Bachelor of Science in Information Technology') ? 'selected' : ''; ?>>Bachelor of Science in Information Technology</option>
                                    <option value="Bachelor of Science in Computer Science" <?php echo ($course_filter_pdf == 'Bachelor of Science in Computer Science') ? 'selected' : ''; ?>>Bachelor of Science in Computer Science</option>
                                    <option value="Bachelor of Science in Psychology" <?php echo ($course_filter_pdf == 'Bachelor of Science in Psychology') ? 'selected' : ''; ?>>Bachelor of Science in Psychology</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="batchSelect" class="form-label">Year Graduated</label>
                                <select id="batchSelect" class="form-select" name="batch_pdf">
                                    <option value="" selected>All Batches</option>
                                    <?php
                                    for ($year_pdf = 2004; $year_pdf <= date("Y"); $year_pdf++) {
                                        echo "<option value='$year_pdf' " . ($batch_filter_pdf == $year_pdf ? 'selected' : '') . ">$year_pdf</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </form>
                    <br>
                    <div class="table-responsive" id="content-container">
                        <div class="container-fluid" id="column-header">
                            <div class="row">
                                <!-- Left side: Search bar and dropdown (col-8) -->
                                <br><br><br>
                                <div class="table-content_pdf">
                                    <table id="example" class="table-responsive table table-striped table-hover ">
                                        <thead>
                                            <tr>
                                                <th scope="col" class="inline">STUDENT ID</th>
                                                <th scope="col" class="inline">NAME</th>
                                                <th scope="col" class="inline">COURSE</th>
                                                <th scope="col" class="inline">BATCH</th>
                                                <th scope="col" class="inline">EMAIL</th>
                                                <th scope="col" class="inline">GENDER</th>
                                                <th scope="col" class="inline">STATUS</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            if ($result_pdf->num_rows > 0) {
                                                while ($row_pdf = $result_pdf->fetch_assoc()) {
                                                    // Determine the batch value based on the SQL query
                                                    if ($status_filter_pdf == 'unregister') {
                                                        $batch_pdf = $row_pdf["batch"];
                                                        $status_pdf = 'Unregistered';
                                                    } else if ($status_filter_pdf == 'inactive') {
                                                        $batch_pdf = $row_pdf["batch_startYear"] . " - " . $row_pdf["batch_endYear"];
                                                        $status_pdf = 'Inactive';
                                                    } else {
                                                        $batch_pdf = $row_pdf["batch_startYear"] . " - " . $row_pdf["batch_endYear"];
                                                        $status_pdf = 'Inactive';
                                                    }

                                                    if (!empty($row_pdf["mname"])) {
                                                        $fullname_pdf = $row_pdf["lname"] . ", " . $row_pdf["fname"] . ", " . $row_pdf["mname"] . ".";
                                                    } else {
                                                        $fullname_pdf = $row_pdf["lname"] . ", " . $row_pdf["fname"];
                                                    }

                                                    if ($status_filter_pdf == 'unregister') {
                                                        $statusDisplay_pdf = 'Unregistered';
                                                        $color_pdf = '#e6b800';
                                                    } else if ($status_filter_pdf == 'inactive') {
                                                        $statusDisplay_pdf = 'Inactive';
                                                        $color_pdf = '#e6b800';
                                                    } else {
                                                        $statusDisplay_pdf = ($row_pdf['status'] == 'Verified' || $row_pdf['status'] == 'Unverified') ? $row_pdf['status'] : $status_pdf;
                                                        $color_pdf = ($row_pdf['status'] == 'Verified') ? 'green' : 'red';
                                                    }
                                            ?>
                                                    <tr>
                                                        <td class="inline"><?php echo $row_pdf['student_id']; ?></td>
                                                        <td class="inline"><?php echo htmlspecialchars($fullname_pdf); ?></td>
                                                        <td class="inline"><?php echo $row_pdf['course']; ?></td>
                                                        <td class="inline"><?php echo htmlspecialchars($batch_pdf); ?></td>
                                                        <td class="inline"><?php echo $row_pdf['email']; ?></td>
                                                        <td class="inline"><?php echo $row_pdf['gender']; ?></td>
                                                        <td class="inline" style="color: <?php echo $color_pdf; ?>">
                                                            <?php echo $statusDisplay_pdf; ?>
                                                        </td>
                                                    </tr>
                                            <?php
                                                }
                                            } else {
                                                $current_page_pdf = 0;
                                                echo '<tr><td colspan="12" style="text-align: center;">No records found</td></tr>';
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div style="display: flex; justify-content: space-between; align-items: center; padding-right: 1.5%; padding-left: 1.5%;">
                                    <p style="margin: 0;">Page <?= $current_page ?> out of <?= $total_pages ?></p>
                                    <div class="pagination" id="content">
                                        <?php if ($current_page > 1) : ?>
                                            <a href="?page=<?= ($current_page - 1); ?>&query=<?php echo isset($_GET['query']) ? $_GET['query'] : ''; ?>" class="prev" style="border-radius:4px;background-color:#368DB8;color:white;margin-bottom:13px;">&laquo; Previous</a>
                                        <?php endif; ?>

                                        <?php if ($current_page < $total_pages) : ?>
                                            <a href="?page=<?= ($current_page + 1); ?>&query=<?php echo isset($_GET['query']) ? $_GET['query'] : ''; ?>" class="next" style="border-radius:4px;background-color:#368DB8;color:white;margin-bottom:13px;">Next &raquo;</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- END -->
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>
    <script>
        // PDF
        document.getElementById('download-pdf').addEventListener('click', () => {
            const hiddenContent = document.getElementById('hidden-content').innerHTML;
            const graduatesTable = document.querySelector('.table-content_pdf').innerHTML;

            // Combine header and graduates list content
            const tempContainer = document.createElement('div');
            tempContainer.innerHTML = hiddenContent + graduatesTable;

            // Ensure header image is displayed
            const headerImage = tempContainer.querySelector('#header-image');
            if (headerImage) {
                headerImage.style.display = 'block';
            }

            // PDF options
            const opt = {
                margin: 0.30,
                filename: `graduates-report-${new Date().toISOString().slice(0, 10)}.pdf`,
                image: {
                    type: 'jpeg',
                    quality: 1
                },
                html2canvas: {
                    scale: 3
                },
                jsPDF: {
                    unit: 'in',
                    format: 'legal',
                    orientation: 'portrait'
                }
            };

            // Generate PDF
            html2pdf().from(tempContainer).set(opt).save();
        });

        // REFRESH
        document.getElementById('refresh-page').addEventListener('click', function() {
            // Send an AJAX request to clear session variables
            fetch('clear_sessions.php', {
                method: 'GET',
                credentials: 'same-origin' // Ensures cookies/session data are sent with the request
            }).then(() => {
                // On successful session clearance, reset the filters and reload the page
                const courseFilter = document.getElementById('courseSelect');
                if (courseFilter) {
                    courseFilter.value = 'all'; // Replace 'all' with the actual default value if different
                }

                const batchFilter = document.getElementById('batchSelect');
                if (batchFilter) {
                    batchFilter.value = 'all'; // Replace 'all' with the actual default value if different
                }

                const alumniStatFilter = document.getElementById('alumniSelect');
                if (alumniStatFilter) {
                    alumniStatFilter.value = ''; // Replace '' with the actual default value if different
                }

                // Remove query parameters from URL
                const url = new URL(window.location.href);
                url.searchParams.delete('course');
                url.searchParams.delete('batch');
                url.searchParams.delete('alumni_stat');
                window.history.replaceState({}, document.title, url.pathname + url.search);

                // Reload the page
                location.reload();
            }).catch(error => {
                console.error('Error:', error);
            });
        });

        // STATUS SELECTOR
        document.getElementById('statusSelect').addEventListener('change', function() {
            const status = this.value;
            const course = document.getElementById('courseSelect').value;
            const batch = document.getElementById('batchSelect').value;
            const alumniStat = document.getElementById('alumniSelect').value; // Get the value of alumniSelect

            const query = new URLSearchParams(window.location.search);
            query.set('status', status);
            query.set('course', course);
            query.set('batch', batch);
            query.set('alumni_stat', alumniStat); // Add alumni_stat to query parameters

            window.location.search = query.toString();
        });

        document.getElementById('refresh-page').addEventListener('click', function() {
            // Reset course filter to default (assuming default is '')
            const courseFilter = document.getElementById('courseSelect');
            if (courseFilter) {
                courseFilter.value = ''; // Set to default value
            }

            // Reset batch filter to default (assuming default is '')
            const batchFilter = document.getElementById('batchSelect');
            if (batchFilter) {
                batchFilter.value = ''; // Set to default value
            }

            // Reset status filter to default (assuming default is 'register')
            const statusFilter = document.getElementById('statusSelect');
            if (statusFilter) {
                statusFilter.value = 'register'; // Set to default value
            }

            // Reset alumni_stat filter to default (assuming default is '')
            const alumniStatFilter = document.getElementById('alumniSelect');
            if (alumniStatFilter) {
                alumniStatFilter.value = ''; // Set to default value
            }

            // Remove query parameters from URL
            const url = new URL(window.location.href);
            url.searchParams.delete('course');
            url.searchParams.delete('batch');
            url.searchParams.delete('status');
            url.searchParams.delete('alumni_stat'); // Remove alumni_stat from URL
            window.history.replaceState({}, document.title, url.pathname);

            // Reload the page
            location.reload(); // Reloads the entire page
        });



        // PAGINATION
        document.addEventListener('DOMContentLoaded', (event) => {
            let currentPage = 1;

            function loadPage(page) {
                // Simulate an AJAX request to get page content
                const contentDiv = document.getElementById('content');
                contentDiv.innerHTML = `Content for page ${page}`; // Replace with actual AJAX call
                currentPage = page;
            }
            document.getElementById('prevPage').addEventListener('click', (event) => {
                event.preventDefault();
                if (currentPage > 1) {
                    loadPage(currentPage - 1);
                }
            });
            document.getElementById('nextPage').addEventListener('click', (event) => {
                event.preventDefault();
                loadPage(currentPage + 1);
            });
            loadPage(currentPage);
        });


        document.addEventListener('DOMContentLoaded', function() {
            const courseSelect = document.getElementById('courseSelect');
            const batchSelect = document.getElementById('batchSelect');
            const alumniStatSelect = document.getElementById('alumniSelect'); // Get the alumniStat dropdown

            function updateUrl() {
                const course = courseSelect.value;
                const batch = batchSelect.value;
                const alumniStat = alumniStatSelect.value; // Get the value of alumniStat
                const query = new URLSearchParams(window.location.search);
                query.set('course', course);
                query.set('batch', batch);
                query.set('alumni_stat', alumniStat); // Add alumni_stat to query parameters
                window.location.search = query.toString();
            }

            // Add event listeners to all dropdowns
            courseSelect.addEventListener('change', updateUrl);
            batchSelect.addEventListener('change', updateUrl);
            alumniStatSelect.addEventListener('change', updateUrl); // Add event listener for alumniStat
        });
    </script>
</body>

</html>