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
        $user = $user_result->fetch_assoc();
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
    session_destroy();
    header('Location: ../../homepage.php');
    exit();
}


// Pagination configuration
$records_per_page = 20;
$current_page = isset($_GET['page']) ? $_GET['page'] : 1;
$start_from = ($current_page - 1) * $records_per_page;

// Initialize the SQL query
$sql = "SELECT * FROM alumni WHERE 1=1";

// Get search input
$search_query = isset($_GET['query']) ? $_GET['query'] : '';
if (!empty($search_query)) {
    $search_query = $conn->real_escape_string($search_query);
    $sql .= " AND (alumni_id LIKE '%$search_query%' 
            OR fname LIKE '%$search_query%' 
            OR mname LIKE '%$search_query%' 
            OR lname LIKE '%$search_query%'
            OR email LIKE '%$search_query%' 
            OR course LIKE '%$search_query%'
            OR batch_endYear LIKE '%$search_query%'
            OR (status LIKE '%$search_query%' AND ((status = 'Verified' AND '$search_query' != 'Unverified') OR (status = 'Unverified' AND '$search_query' != 'Verified')))
            OR (gender LIKE '%$search_query%' AND ((gender = 'male' AND '$search_query' != 'female') OR (gender = 'female' AND '$search_query' != 'male'))))";
}

// Get course and batch inputs
$course = isset($_GET['course']) ? $_GET['course'] : '';
$batch = isset($_GET['batch']) ? $_GET['batch'] : '';

if (!empty($course)) {
    $course = $conn->real_escape_string($course);
    $sql .= " AND course = '$course'";
}

if (!empty($batch)) {
    $batch = $conn->real_escape_string($batch);
    $sql .= " AND batch_endYear = '$batch'";
}

$sql .= " ORDER BY lname ASC LIMIT $start_from, $records_per_page";
$result = $conn->query($sql);

// Count total number of records
$total_records_query = "SELECT COUNT(*) FROM alumni WHERE 1=1";
if (!empty($search_query)) {
    $total_records_query .= " AND (alumni_id LIKE '%$search_query%' 
            OR fname LIKE '%$search_query%' 
            OR mname LIKE '%$search_query%' 
            OR lname LIKE '%$search_query%'
            OR email LIKE '%$search_query%' 
            OR course LIKE '%$search_query%'
            OR batch_endYear LIKE '%$search_query%'
            OR (status LIKE '%$search_query%' AND ((status = 'Verified' AND '$search_query' != 'Unverified') OR (status = 'Unverified' AND '$search_query' != 'Verified')))
            OR (gender LIKE '%$search_query%' AND ((gender = 'male' AND '$search_query' != 'female') OR (gender = 'female' AND '$search_query' != 'male'))))";
}

if (!empty($course)) {
    $total_records_query .= " AND course = '$course'";
}

if (!empty($batch)) {
    $total_records_query .= " AND batch_endYear = '$batch'";
}

$total_records_result = $conn->query($total_records_query);
$total_records_row = $total_records_result->fetch_array();
$total_records = $total_records_row[0];

$total_pages = ceil($total_records / $records_per_page);
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
                        <a href="../dashboard_admin.php">
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
                        <a href="../coordinator/coordinator.php">
                            <span class="las la-user-cog" style="color:#fff"></span>
                            <small>COORDINATOR</small>
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
                        <a href="../report/reportgraph.php" class="active">
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
                    <button id="download-pdf" class="btn btn-primary">Download as PDF</button>
                    <button id="refresh-page" class="btn btn-secondary">Refresh</button>
                </div>
                <div>
                    <button id="another-page" class="btn btn-success" onclick="window.location.href='../report/report.php'">Graph</button>
                </div>
            </div>

            <div class="container mt-4 p-3 shadow bg-white rounded">
                <div class="container mt-5">
                    <div id="hidden-content" style="display:none;">
                        <img src="../../assets/head.jpg" id="header-image" style="width:100%; height:auto;">
                    </div>
                    <h2>Select Course and Batch</h2>
                    <div class="mb-3">
                        <label for="courseSelect" class="form-label">Select Course</label>
                        <select id="courseSelect" class="form-select">
                            <option value="">All Courses</option>
                            <option value="Bachelor of Arts in Journalism">Bachelor of Arts in Journalism</option>
                            <option value="Bachelor of Secondary Education">Bachelor of Secondary Education</option>
                            <option value="Bachelor of Elementary Education">Bachelor of Elementary Education</option>
                            <option value="Bachelor of Science in Business Management">Bachelor of Science in Business Management</option>
                            <option value="Bachelor of Science in Office Administration">Bachelor of Science in Office Administration</option>
                            <option value="Bachelor of Science in Entrepreneurship">Bachelor of Science in Entrepreneurship</option>
                            <option value="Bachelor of Science in Hospitality Management">Bachelor of Science in Hospitality Management</option>
                            <option value="Bachelor of Science in Information Technology">Bachelor of Science in Information Technology</option>
                            <option value="Bachelor of Science in Computer Science">Bachelor of Science in Computer Science</option>
                            <option value="Bachelor of Science in Psychology">Bachelor of Science in Psychology</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="batchSelect" class="form-label">Select Batch</label>
                        <select id="batchSelect" class="form-select">
                            <option value="">All Batches</option>
                            <?php
                            $current_year = date('Y');
                            for ($year = 2004; $year <= $current_year; $year++) {
                                echo "<option value=\"$year\">$year</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="table-responsive" id="content-container">
                        <div class="container-fluid" id="column-header">
                            <div class="row">
                                <!-- Left side: Search bar and dropdown (col-8) -->
                                <div class="col-7 d-flex align-items-center">
                                    <div class="d-flex align-items-center" style="flex: 1;">
                                        <!-- Search Form -->
                                        <form class="d-flex" role="search" style="flex: 1;">
                                            <input class="form-control me-2" type="search" name="query" placeholder="Search Records..." aria-label="Search" value="<?php echo isset($_GET['query']) ? $_GET['query'] : ''; ?>">
                                            <button class="btn btn-outline-success" type="submit" style="padding-left: 30px; padding-right: 30px;">Search</button>
                                        </form>
                                    </div>
                                </div>
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
                                                    if (!empty($row["mname"])) {
                                                        $fullname = $row["lname"] . ", " . $row["fname"] . ", " . $row["mname"] . ".";
                                                    } else {
                                                        $fullname = $row["lname"] . ", " . $row["fname"];
                                                    }
                                                    $batch = $row["batch_startYear"] . " - " . $row["batch_endYear"];
                                                    $address = $row['address'];
                                                    $displayAddress = str_replace(',', '', $address);
                                            ?>
                                                    <tr>
                                                        <td class="inline"><?php echo $row['student_id'] ?></td>
                                                        <td class="inline"><?php echo htmlspecialchars($fullname) ?></td>
                                                        <td class="inline"><?php echo $row['course'] ?></td>
                                                        <td class="inline"><?php echo htmlspecialchars($batch) ?></td>
                                                        <td class="inline"><?php echo $row['email'] ?></td>
                                                        <td class="inline"><?php echo $row['gender'] ?></td>
                                                        <td class="inline" style="color: <?php echo ($row['status'] == 'Verified') ? 'green' : 'red'; ?>"><?php echo $row['status']; ?></td>
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
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>
    <script>
        // PDF
        document.getElementById('download-pdf').addEventListener('click', () => {
            const selectedCourse = courseSelect.value;
            displayGraduates(selectedCourse);
            const graduatesTable = document.getElementById('graduatesTable');
            const hiddenContent = document.getElementById('hidden-content').innerHTML;
            const tempContainer = document.createElement('div');
            tempContainer.innerHTML = hiddenContent + graduatesTable.outerHTML;
            tempContainer.querySelector('#header-image').style.display = 'block';
            tempContainer.querySelector('#graduatesTable').style.display = 'block';

            tempContainer.style.display = 'block';
            tempContainer.style.width = '100%';
            tempContainer.style.fontSize = '11px';
            const currentDate = new Date();
            const formattedDate = currentDate.toISOString().slice(0, 10);

            // PDF options
            const opt = {
                margin: 0.50,
                filename: `graduates-report-${formattedDate}.pdf`,
                image: {
                    type: 'jpeg',
                    quality: 0.98
                },
                html2canvas: {
                    scale: 2
                },
                jsPDF: {
                    unit: 'in',
                    format: 'legal',
                    orientation: 'portrait'
                }
            };
            // Generate the PDF from the temporary container
            html2pdf().from(tempContainer).set(opt).save().then(() => {
                // Clean up: remove the temporary container
                tempContainer.remove();
            });
        });
        document.getElementById('refresh-page').addEventListener('click', () => {
            location.reload();
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

            function updateUrl() {
                const course = courseSelect.value;
                const batch = batchSelect.value;
                const query = new URLSearchParams(window.location.search);
                query.set('course', course);
                query.set('batch', batch);
                window.location.search = query.toString();
            }

            courseSelect.addEventListener('change', updateUrl);
            batchSelect.addEventListener('change', updateUrl);
        });
    </script>
</body>

</html>





<!-- VERSION 2 -->
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
        $user = $user_result->fetch_assoc();
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
    session_destroy();
    header('Location: ../../homepage.php');
    exit();
}


$records_per_page = 20;
$current_page = isset($_GET['page']) ? $_GET['page'] : 1;
$start_from = ($current_page - 1) * $records_per_page;

$search_query = isset($_GET['query']) ? $_GET['query'] : '';
$course_filter = isset($_GET['course']) ? $_GET['course'] : '';
$batch_filter = isset($_GET['batch']) ? $_GET['batch'] : '';

// SQL base query
$sql = "SELECT * FROM alumni WHERE 1=1";

// Apply course filter
if (!empty($course_filter) && $course_filter != 'all') {
    $sql .= " AND course = '$course_filter'";
}

// Apply batch filter
if (!empty($batch_filter) && $batch_filter != 'all') {
    $sql .= " AND batch_endYear = '$batch_filter'";
}

// Apply search query
if (!empty($search_query)) {
    $sql .= " AND (alumni_id LIKE '%$search_query%' 
            OR fname LIKE '%$search_query%' 
            OR mname LIKE '%$search_query%' 
            OR lname LIKE '%$search_query%'
            OR email LIKE '%$search_query%' 
            OR course LIKE '%$search_query%'
            OR batch_endYear LIKE '%$search_query%'
            OR (status LIKE '%$search_query%' AND ((status = 'Verified' AND '$search_query' != 'Unverified') OR (status = 'Unverified' AND '$search_query' != 'Verified')))
            OR (gender LIKE '%$search_query%' AND ((gender = 'male' AND '$search_query' != 'female') OR (gender = 'female' AND '$search_query' != 'male')))";
}

// Add ordering and pagination
$sql .= " ORDER BY lname ASC LIMIT $start_from, $records_per_page";
$result = $conn->query($sql);

// Count total number of records
$total_records_query = "SELECT COUNT(*) FROM alumni WHERE 1=1";

// Apply filters to the count query
if (!empty($course_filter) && $course_filter != 'all') {
    $total_records_query .= " AND course = '$course_filter'";
}

if (!empty($batch_filter) && $batch_filter != 'all') {
    $total_records_query .= " AND batch_endYear = '$batch_filter'";
}

if (!empty($search_query)) {
    $total_records_query .= " AND (alumni_id LIKE '%$search_query%' 
                              OR fname LIKE '%$search_query%' 
                              OR mname LIKE '%$search_query%' 
                              OR lname LIKE '%$search_query%'
                              OR email LIKE '%$search_query%' 
                              OR course LIKE '%$search_query%'
                              OR batch_endYear LIKE '%$search_query%'
                              OR (status LIKE '%$search_query%' AND ((status = 'Verified' AND '$search_query' != 'Unverified') OR (status = 'Unverified' AND '$search_query' != 'Verified')))
                              OR (gender LIKE '%$search_query%' AND ((gender = 'male' AND '$search_query' != 'female') OR (gender = 'female' AND '$search_query' != 'male')))";
}

$total_records_result = mysqli_query($conn, $total_records_query);
$total_records_row = mysqli_fetch_array($total_records_result);
$total_records = $total_records_row[0];

$total_pages = ceil($total_records / $records_per_page);

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
                        <a href="../dashboard_admin.php">
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
                        <a href="../coordinator/coordinator.php">
                            <span class="las la-user-cog" style="color:#fff"></span>
                            <small>COORDINATOR</small>
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
                        <a href="../report/reportgraph.php" class="active">
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
                    <button id="download-pdf" class="btn btn-primary">Download as PDF</button>
                    <button id="refresh-page" class="btn btn-secondary">Refresh</button>
                </div>
                <div>
                    <button id="another-page" class="btn btn-success" onclick="window.location.href='../report/report.php'">Graph</button>
                </div>
            </div>

            <div class="container mt-4 p-3 shadow bg-white rounded">
                <div class="container mt-5">
                    <div id="hidden-content" style="display:none;">
                        <img src="../../assets/head.jpg" id="header-image" style="width:100%; height:auto;">
                    </div>
                    <h2>Select Course and Batch</h2>
                    <form method="GET" action="">
                        <div class="mb-3">
                            <label for="courseSelect" class="form-label">Select Course</label>
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

                        <div class="mb-3">
                            <label for="batchSelect" class="form-label">Select Batch</label>
                            <select id="batchSelect" class="form-select" name="batch">
                                <option value="" selected>All Batches</option>
                                <?php
                                for ($year = 2004; $year <= date("Y"); $year++) {
                                    echo "<option value='$year' " . ($batch_filter == $year ? 'selected' : '') . ">$year</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="searchQuery" class="form-label">Search Alumni</label>
                            <input type="text" class="form-control" id="searchQuery" name="query" placeholder="Search..." value="<?php echo htmlspecialchars($search_query); ?>">
                        </div>

                        <button type="submit" class="btn btn-primary">Filter</button>
                    </form>
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
                                                    if (!empty($row["mname"])) {
                                                        $fullname = $row["lname"] . ", " . $row["fname"] . ", " . $row["mname"] . ".";
                                                    } else {
                                                        $fullname = $row["lname"] . ", " . $row["fname"];
                                                    }
                                                    $batch = $row["batch_startYear"] . " - " . $row["batch_endYear"];
                                                    $address = $row['address'];
                                                    $displayAddress = str_replace(',', '', $address);
                                            ?>
                                                    <tr>
                                                        <td class="inline"><?php echo $row['student_id'] ?></td>
                                                        <td class="inline"><?php echo htmlspecialchars($fullname) ?></td>
                                                        <td class="inline"><?php echo $row['course'] ?></td>
                                                        <td class="inline"><?php echo htmlspecialchars($batch) ?></td>
                                                        <td class="inline"><?php echo $row['email'] ?></td>
                                                        <td class="inline"><?php echo $row['gender'] ?></td>
                                                        <td class="inline" style="color: <?php echo ($row['status'] == 'Verified') ? 'green' : 'red'; ?>"><?php echo $row['status']; ?></td>
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
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>
    <script>
        // PDF
        document.getElementById('download-pdf').addEventListener('click', () => {
            const selectedCourse = courseSelect.value;
            displayGraduates(selectedCourse);
            const graduatesTable = document.getElementById('graduatesTable');
            const hiddenContent = document.getElementById('hidden-content').innerHTML;
            const tempContainer = document.createElement('div');
            tempContainer.innerHTML = hiddenContent + graduatesTable.outerHTML;
            tempContainer.querySelector('#header-image').style.display = 'block';
            tempContainer.querySelector('#graduatesTable').style.display = 'block';

            tempContainer.style.display = 'block';
            tempContainer.style.width = '100%';
            tempContainer.style.fontSize = '11px';
            const currentDate = new Date();
            const formattedDate = currentDate.toISOString().slice(0, 10);

            // PDF options
            const opt = {
                margin: 0.50,
                filename: `graduates-report-${formattedDate}.pdf`,
                image: {
                    type: 'jpeg',
                    quality: 0.98
                },
                html2canvas: {
                    scale: 2
                },
                jsPDF: {
                    unit: 'in',
                    format: 'legal',
                    orientation: 'portrait'
                }
            };
            // Generate the PDF from the temporary container
            html2pdf().from(tempContainer).set(opt).save().then(() => {
                // Clean up: remove the temporary container
                tempContainer.remove();
            });
        });
        document.getElementById('refresh-page').addEventListener('click', () => {
            location.reload();
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

            function updateUrl() {
                const course = courseSelect.value;
                const batch = batchSelect.value;
                const query = new URLSearchParams(window.location.search);
                query.set('course', course);
                query.set('batch', batch);
                window.location.search = query.toString();
            }

            courseSelect.addEventListener('change', updateUrl);
            batchSelect.addEventListener('change', updateUrl);
        });
    </script>
</body>

</html>




<!-- VERSION 3 -->


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
        $user = $user_result->fetch_assoc();
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
    session_destroy();
    header('Location: ../../homepage.php');
    exit();
}


$records_per_page = 20;
$current_page = isset($_GET['page']) ? $_GET['page'] : 1;
$start_from = ($current_page - 1) * $records_per_page;

$search_query = isset($_GET['query']) ? $_GET['query'] : '';
$course_filter = isset($_GET['course']) ? $_GET['course'] : '';
$batch_filter = isset($_GET['batch']) ? $_GET['batch'] : '';

// SQL base query
$sql = "SELECT * FROM alumni WHERE 1=1";

// Apply course filter
if (!empty($course_filter) && $course_filter != 'all') {
    $sql .= " AND course = '$course_filter'";
}

// Apply batch filter
if (!empty($batch_filter) && $batch_filter != 'all') {
    $sql .= " AND batch_endYear = '$batch_filter'";
}

// Apply search query
if (!empty($search_query)) {
    $sql .= " AND (alumni_id LIKE '%$search_query%' 
            OR fname LIKE '%$search_query%' 
            OR mname LIKE '%$search_query%' 
            OR lname LIKE '%$search_query%'
            OR email LIKE '%$search_query%' 
            OR course LIKE '%$search_query%'
            OR batch_endYear LIKE '%$search_query%'
            OR (status LIKE '%$search_query%' AND ((status = 'Verified' AND '$search_query' != 'Unverified') OR (status = 'Unverified' AND '$search_query' != 'Verified')))
            OR (gender LIKE '%$search_query%' AND ((gender = 'male' AND '$search_query' != 'female') OR (gender = 'female' AND '$search_query' != 'male')))";
}

// Add ordering and pagination only if $sql is not empty
$sql .= " ORDER BY lname ASC LIMIT $start_from, $records_per_page";

$result = $conn->query($sql);

// Count total number of records
$total_records_query = "SELECT COUNT(*) FROM alumni WHERE 1=1";

// Apply filters to the count query
if (!empty($course_filter) && $course_filter != 'all') {
    $total_records_query .= " AND course = '$course_filter'";
}

if (!empty($batch_filter) && $batch_filter != 'all') {
    $total_records_query .= " AND batch_endYear = '$batch_filter'";
}

if (!empty($search_query)) {
    $total_records_query .= " AND (alumni_id LIKE '%$search_query%' 
                              OR fname LIKE '%$search_query%' 
                              OR mname LIKE '%$search_query%' 
                              OR lname LIKE '%$search_query%'
                              OR email LIKE '%$search_query%' 
                              OR course LIKE '%$search_query%'
                              OR batch_endYear LIKE '%$search_query%'
                              OR (status LIKE '%$search_query%' AND ((status = 'Verified' AND '$search_query' != 'Unverified') OR (status = 'Unverified' AND '$search_query' != 'Verified')))
                              OR (gender LIKE '%$search_query%' AND ((gender = 'male' AND '$search_query' != 'female') OR (gender = 'female' AND '$search_query' != 'male')))";
}

$total_records_result = mysqli_query($conn, $total_records_query);
$total_records_row = mysqli_fetch_array($total_records_result);
$total_records = $total_records_row[0];

$total_pages = ceil($total_records / $records_per_page);

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
                        <a href="../dashboard_admin.php">
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
                        <a href="../coordinator/coordinator.php">
                            <span class="las la-user-cog" style="color:#fff"></span>
                            <small>COORDINATOR</small>
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
                        <a href="../report/reportgraph.php" class="active">
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
                    <button id="download-pdf" class="btn btn-primary">Download as PDF</button>
                    <button id="refresh-page" class="btn btn-secondary">Refresh</button>
                </div>
                <div>
                    <button id="another-page" class="btn btn-success" onclick="window.location.href='../report/report.php'">Graph</button>
                </div>
            </div>

            <div class="container mt-4 p-3 shadow bg-white rounded">
                <div class="container mt-5">
                    <div id="hidden-content" style="display:none;">
                        <img src="../../assets/head.jpg" id="header-image" style="width:100%; height:auto;">
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <h2 class="mb-0 flex-grow-1">Lists of Registered Alumni</h2>
                            <div class="mb-0 flex-grow-1 ms-3">
                                <label for="statusSelect" class="form-label d-none">Status</label>
                                <select id="statusSelect" class="form-select w-100" name="status">
                                    <option value="register">Registered</option>
                                    <option value="unregister">Unregistered</option>
                                    <option value="inactive">Inactive</option>
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
                                                    if (!empty($row["mname"])) {
                                                        $fullname = $row["lname"] . ", " . $row["fname"] . ", " . $row["mname"] . ".";
                                                    } else {
                                                        $fullname = $row["lname"] . ", " . $row["fname"];
                                                    }
                                                    $batch = $row["batch_startYear"] . " - " . $row["batch_endYear"];
                                                    $address = $row['address'];
                                                    $displayAddress = str_replace(',', '', $address);
                                            ?>
                                                    <tr>
                                                        <td class="inline"><?php echo $row['student_id'] ?></td>
                                                        <td class="inline"><?php echo htmlspecialchars($fullname) ?></td>
                                                        <td class="inline"><?php echo $row['course'] ?></td>
                                                        <td class="inline"><?php echo htmlspecialchars($batch) ?></td>
                                                        <td class="inline"><?php echo $row['email'] ?></td>
                                                        <td class="inline"><?php echo $row['gender'] ?></td>
                                                        <td class="inline" style="color: <?php echo ($row['status'] == 'Verified') ? 'green' : 'red'; ?>"><?php echo $row['status']; ?></td>
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
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>
    <script>
        // PDF
        document.getElementById('download-pdf').addEventListener('click', () => {
            const selectedCourse = courseSelect.value;
            displayGraduates(selectedCourse);
            const graduatesTable = document.getElementById('graduatesTable');
            const hiddenContent = document.getElementById('hidden-content').innerHTML;
            const tempContainer = document.createElement('div');
            tempContainer.innerHTML = hiddenContent + graduatesTable.outerHTML;
            tempContainer.querySelector('#header-image').style.display = 'block';
            tempContainer.querySelector('#graduatesTable').style.display = 'block';

            tempContainer.style.display = 'block';
            tempContainer.style.width = '100%';
            tempContainer.style.fontSize = '11px';
            const currentDate = new Date();
            const formattedDate = currentDate.toISOString().slice(0, 10);

            // PDF options
            const opt = {
                margin: 0.50,
                filename: `graduates-report-${formattedDate}.pdf`,
                image: {
                    type: 'jpeg',
                    quality: 0.98
                },
                html2canvas: {
                    scale: 2
                },
                jsPDF: {
                    unit: 'in',
                    format: 'legal',
                    orientation: 'portrait'
                }
            };
            // Generate the PDF from the temporary container
            html2pdf().from(tempContainer).set(opt).save().then(() => {
                // Clean up: remove the temporary container
                tempContainer.remove();
            });
        });
        // document.getElementById('refresh-page').addEventListener('click', () => {
        //     location.reload();
        // });
        document.getElementById('refresh-page').addEventListener('click', function() {
            // Reset course filter to default (assuming default is 'all')
            const courseFilter = document.getElementById('courseSelect');
            if (courseFilter) {
                courseFilter.value = 'all'; // Replace 'all' with the actual default value if different
            }

            // Reset batch filter to default (assuming default is 'all')
            const batchFilter = document.getElementById('batchSelect');
            if (batchFilter) {
                batchFilter.value = 'all'; // Replace 'all' with the actual default value if different
            }

            // Remove query parameters from URL
            const url = new URL(window.location.href);
            url.searchParams.delete('course');
            url.searchParams.delete('batch');
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

            function updateUrl() {
                const course = courseSelect.value;
                const batch = batchSelect.value;
                const query = new URLSearchParams(window.location.search);
                query.set('course', course);
                query.set('batch', batch);
                window.location.search = query.toString();
            }

            courseSelect.addEventListener('change', updateUrl);
            batchSelect.addEventListener('change', updateUrl);
        });
    </script>
</body>

</html>