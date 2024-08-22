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

// Pagination configuration
$records_per_page = 6; // Number of records to display per page
$current_page = isset($_GET['page']) ? $_GET['page'] : 1; // Get current page number, default to 1

// Calculate the limit clause for SQL query
$start_from = ($current_page - 1) * $records_per_page;

// Initialize variables
$sql = "SELECT * FROM alumni ";

// Check if search query is provided
if (isset($_GET['query']) && !empty($_GET['query'])) {
    $search_query = $_GET['query'];
    // Modify SQL query to include search filter
    $sql .= "WHERE alumni_id LIKE '%$search_query%' 
            OR fname LIKE '%$search_query%' 
            OR mname LIKE '%$search_query%' 
            OR lname LIKE '%$search_query%'
            OR address LIKE '%$search_query%'
            OR email LIKE '%$search_query%' 
            OR course LIKE '%$search_query%'
            OR batch_startYear LIKE '%$search_query%'
            OR batch_endYear LIKE '%$search_query%'
            OR CONCAT(batch_startYear, '-', batch_endYear) LIKE '%$search_query%'
            OR (status LIKE '%$search_query%' AND ((status = 'Verified' AND '$search_query' != 'Unverified') OR (status = 'Unverified' AND '$search_query' != 'Verified')))
            OR (gender LIKE '%$search_query%' AND ((gender = 'male' AND '$search_query' != 'female') OR (gender = 'female' AND '$search_query' != 'male')))";
}

$sql .= "ORDER BY lname ASC ";
$sql .= "LIMIT $start_from, $records_per_page";

$result = $conn->query($sql);

// Count total number of records
$total_records_query = "SELECT COUNT(*) FROM alumni";
if (isset($_GET['query']) && !empty($_GET['query'])) {
    $total_records_query .= " WHERE alumni_id LIKE '%$search_query%' 
                              OR fname LIKE '%$search_query%' 
                              OR mname LIKE '%$search_query%' 
                              OR lname LIKE '%$search_query%' 
                              OR address LIKE '%$search_query%'
                              OR email LIKE '%$search_query%' 
                              OR course LIKE '%$search_query%'
                              OR batch_startYear LIKE '%$search_query%'
                              OR batch_endYear LIKE '%$search_query%'
                              OR CONCAT(batch_startYear, '-', batch_endYear) LIKE '%$search_query%'
                              OR (status LIKE '%$search_query%' AND ((status = 'Verified' AND '$search_query' != 'Unverified') OR (status = 'Unverified' AND '$search_query' != 'Verified')))
                              OR (gender LIKE '%$search_query%' AND ((gender = 'male' AND '$search_query' != 'female') OR (gender = 'female' AND '$search_query' != 'male')))";
}
$total_records_result = mysqli_query($conn, $total_records_query);
$total_records_row = mysqli_fetch_array($total_records_result);
$total_records = $total_records_row[0];

$total_pages = ceil($total_records / $records_per_page);



if (isset($_GET['ide'])) {
    $icon = 'success';
    $iconHtml = '<i class="fas fa-check-circle"></i>';
    $title = 'Account archived successfully';

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
    <title>Alumni List</title>
    <link rel="stylesheet" href="./css/alumni.css">
    <link rel="shortcut icon" href="../../assets/cvsu.png" type="image/svg+xml">
    <link rel="stylesheet" href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <script>
        "https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    </script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- FOR PAGINATION -->
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
                <!-- <h4>ADMIN</h4>
                <small style="color: white;">admin@email.com</small> -->
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
                        <a href="./alumni.php" class="active">
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
                        <a href="../report/report.php">
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
                <h1><strong>Alumni</strong></h1>
            </div>

            <div class="container-fluid" id="main-container">
                <div class="container-fluid" id="content-container">
                    <div class="container-title">
                        <span>Records</span>
                    </div>
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

                                    <!-- Dropdown Button
                                    <div class="dropdown ms-2">
                                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="eventForDropdown" onclick="toggleDropdown()" style="height: 100%; width: 430px;">Select Courses</button>
                                        <div id="eventForMenu" class="dropdown-menu" style="display:none; position: absolute; background-color: white; border: 1px solid #ccc; padding: 10px;">
                                            <label><input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)" checked> ALL</label><br>
                                            <label><input type="checkbox" class="eventForCheckbox" name="eventFor[]" value="BAJ" checked> Batchelor of Arts in Journalism</label><br>
                                            <label><input type="checkbox" class="eventForCheckbox" name="eventFor[]" value="BECEd" checked> Bachelor Of Secondary Education</label><br>
                                            <label><input type="checkbox" class="eventForCheckbox" name="eventFor[]" value="BEEd" checked> Bachelor Of Elementary Education</label><br>
                                            <label><input type="checkbox" class="eventForCheckbox" name="eventFor[]" value="BSBM" checked> Bachelor Of Science In Business Management</label><br>
                                            <label><input type="checkbox" class="eventForCheckbox" name="eventFor[]" value="BSOA" checked> Bachelor of Science in Office Administration</label><br>
                                            <label><input type="checkbox" class="eventForCheckbox" name="eventFor[]" value="BSEntrep" checked> Bachelor Of Science In Entrepreneurship</label><br>
                                            <label><input type="checkbox" class="eventForCheckbox" name="eventFor[]" value="BSHM" checked> Bachelor Of Science In Hospitality Management</label><br>
                                            <label><input type="checkbox" class="eventForCheckbox" name="eventFor[]" value="BSIT" checked> Bachelor of Science in Information Technology</label><br>
                                            <label><input type="checkbox" class="eventForCheckbox" name="eventFor[]" value="BSCS" checked> Bachelor of Science in Computer Science</label><br>
                                            <label><input type="checkbox" class="eventForCheckbox" name="eventFor[]" value="BSc(Psych)" checked> Bachelor Of Science In Psychology</label>
                                        </div>
                                    </div> -->
                                </div>
                            </div>

                            <!-- Right side: Unregister Account button (col-4) -->
                            <div class="col-5 d-flex align-items-center justify-content-end">
                                <div class="add-button">
                                    <a class='btn btn-secondary border border-dark' href='./list_of_graduate.php' style="margin-left: 1%; padding-left: 4.1px; padding-right: 5.4px; white-space: nowrap;">Unregister Account</a>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="table-content">
                        <table id="example" class="table-responsive table table-striped table-hover ">
                            <thead>

                                <tr>
                                    <th scope="col" class="inline">STUDENT ID</th>
                                    <th scope="col" class="inline">NAME</th>
                                    <th scope="col" class="inline">GENDER</th>
                                    <th scope="col" class="inline">COURSE</th>
                                    <th scope="col" class="inline">BATCH</th>
                                    <th scope="col" class="inline">CONTACT</th>
                                    <th scope="col" class="inline">ADDRESS</th>
                                    <th scope="col" class="inline">EMAIL</th>
                                    <th scope="col" class="inline">LAST LOGIN</th>
                                    <th scope="col" class="inline">STATUS</th>
                                    <th scope="col" class="inline">DATE CREATION</th>
                                    <th scope="col" class="inline">ACTION</th>
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
                                            <td class="inline"><?php echo $row['gender'] ?></td>
                                            <td class="inline"><?php echo $row['course'] ?></td>
                                            <td class="inline"><?php echo htmlspecialchars($batch) ?></td>
                                            <td class="inline"><?php echo $row['contact'] ?></td>
                                            <td class="inline"><?php echo $displayAddress ?></td>
                                            <td class="inline"><?php echo $row['email'] ?></td>
                                            <td class="inline"><?php echo ($row['last_login'] == '0000-00-00 00:00:00') ? '-- / -- / --' : $row['last_login']; ?></td>
                                            <td class="inline" style="color: <?php echo ($row['status'] == 'Verified') ? 'green' : 'red'; ?>"><?php echo $row['status']; ?></td>
                                            <td class="inline"><?php echo $row['date_created'] ?></td>
                                            <?php
                                            echo "
                                                <td class='inline act'>
                                                    <a class='btn btn-danger btn-sm archive' href='./del_alumni.php?id=$row[alumni_id]' style='font-size: 11.8px; padding: 5px 10px; width: 80px; text-align: center;'>Archive</a>
                                                    <a class='btn btn-info btn-sm' href='./alumni_info.php?id=$row[alumni_id]' style='font-size: 11.8px; padding: 5px 10px; width: 80px; text-align: center;'>More Info</a>
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
            <!-- <div class="container-fluid" id="main-container">
                <div class="container-fluid" id="content-container">
                    
                </div>
            </div> -->
        </main>
        <script>
            function toggleDropdown() {
                var dropdown = document.getElementById('eventForMenu');
                dropdown.style.display = (dropdown.style.display === 'none' || dropdown.style.display === '') ? 'block' : 'none';
            }
            // Toggle select all checkboxes
            function toggleSelectAll(selectAllCheckbox) {
                var checkboxes = document.querySelectorAll('.eventForCheckbox');
                checkboxes.forEach(function(checkbox) {
                    checkbox.checked = selectAllCheckbox.checked;
                });
            }

            // Uncheck 'ALL' when any individual checkbox is unchecked
            document.querySelectorAll('.eventForCheckbox').forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    if (!this.checked) {
                        document.getElementById('selectAll').checked = false;
                    }
                });
            });

            // Set initial state of the dropdown to have all checkboxes checked
            window.onload = function() {
                document.querySelectorAll('.eventForCheckbox').forEach(function(checkbox) {
                    checkbox.checked = true;
                });
                document.getElementById('selectAll').checked = true;
            };

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

                // Initial load
                loadPage(currentPage);
            });

            // forsweetalert confirm
            // Debugging: Ensure SweetAlert2 is loaded
            document.addEventListener('DOMContentLoaded', function() {
                const archiveButtons = document.querySelectorAll('.archive');

                archiveButtons.forEach(function(button) {
                    button.addEventListener('click', function(event) {
                        event.preventDefault(); // Prevent the default action (navigation)

                        const href = this.getAttribute('href'); // Get the href attribute

                        Swal.fire({
                            title: 'Are you sure you want to continue?',
                            icon: 'warning',
                            iconHtml: '<i class="fas fa-exclamation-triangle"></i>',
                            text: 'Once you proceed, this action cannot be undone.',
                            showCancelButton: true,
                            confirmButtonColor: '#e03444',
                            cancelButtonColor: '#ffc404',
                            confirmButtonText: 'Ok',
                            cancelButtonText: 'Cancel',
                            customClass: {
                                confirmButton: 'confirm-button-class',
                                cancelButton: 'cancel-button-class'
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = href; // Proceed with the navigation if confirmed
                            }
                        });
                    });
                });
            });

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