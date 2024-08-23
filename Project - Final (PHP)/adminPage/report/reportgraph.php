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


// COUNTS
    //query for alumni count
    $sql_alumni = "SELECT COUNT(student_id) AS alumni_count FROM alumni";
    $result_alumni = $conn->query($sql_alumni);
    $row_alumni = $result_alumni->fetch_assoc();
    $count_alumni = $row_alumni['alumni_count'];

    //query for alumni count
    $sql_coordinator = "SELECT COUNT(coor_id) AS coordinators_count FROM coordinator";
    $result_coordinator = $conn->query($sql_coordinator);
    $row_coordinator = $result_coordinator->fetch_assoc();
    $coordinator_count = $row_coordinator['coordinators_count'];

    //query for events count
    $sql_event = "SELECT COUNT(event_id) AS events_count FROM event";
    $result_event = $conn->query($sql_event);
    $row_event = $result_event->fetch_assoc();
    $event_count = $row_event['events_count'];


$sql = "SELECT course, COUNT(*) as count FROM alumni GROUP BY course";
$result = $conn->query($sql);

// DTA FOR CHART
$labels = ['BAJ', 'BECEd', 'BEEd', 'BSBM', 'BSOA', 'BSEntrep', 'BSHM', 'BSIT', 'BSCS', 'BSc(Psych)'];
$data = array_fill(0, count($labels), 0);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $index = array_search($row['course'], $labels);
        if ($index !== false) {
            $data[$index] = $row['count'];
        }
    }
}

// EVENT COUNT EVERY MONTH
$query_event = "SELECT MONTH(date) as month, COUNT(*) as count FROM event GROUP BY MONTH(date)";
$res_event = $conn->query($query_event);

$labels_event = ['Jan.', 'Feb.', 'Mar.', 'Apr.', 'May', 'June', 'July', 'Aug.', 'Sept.', 'Oct.', 'Nov.', 'Dec.'];
$data_event = array_fill(0, 12, 0);

if ($res_event->num_rows > 0) {
    while ($row_event = $res_event->fetch_assoc()) {
        $data_event[$row_event['month'] - 1] = $row_event['count'];
    }
}


// Query to get the count of alumni registered in each month
$qeury_alumniCount = "SELECT MONTH(date_created) as month, COUNT(*) as count FROM alumni GROUP BY MONTH(date_created)
";

// Execute the query
$res_alumniCount = $conn->query($qeury_alumniCount);

// Prepare data for the chart
$labels_alumniCount = ['Jan.', 'Feb.', 'Mar.', 'Apr.', 'May', 'June', 'July', 'Aug.', 'Sept.', 'Oct.', 'Nov.', 'Dec.'];
$data_alumniCount = array_fill(0, 12, 0);

if ($res_alumniCount->num_rows > 0) {
    while($row_alumniCount = $res_alumniCount->fetch_assoc()) {
        $data_alumniCount[$row_alumniCount['month'] - 1] = $row_alumniCount['count'];
    }
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
            <h2>Select Course and Batch</h2>

            <!-- Course Selection Dropdown -->
            <div class="mb-3">
            <label for="courseSelect" class="form-label">Select Course</label>
            <select id="courseSelect" class="form-select">
                <option value="" disabled selected>Select a course</option>
                <option value="bsit">Bachelor of Science in Information Technology</option>
                <option value="bscs">Bachelor of Science in Computer Science</option>
                <option value="bsoa">Bachelor of Science in Office Administration</option>
                <option value="baj">Bachelor Of Arts In Journalism</option>
                <option value="beced">Bachelor Of Elementary Education</option>
                <option value="beed">Bachelor Of Secondary Education</option>          
                <option value="bsbm">Bachelor Of Science In Business Management</option>
                <option value="bsentrep">Bachelor Of Science In Entrepreneurship</option>
                <option value="bshm">Bachelor Of Science In Hospitality Management</option>
                <option value="bspsych">Bachelor Of Science In Psychology</option>
            </select>
            </div>

            <!-- Batch Selection Dropdown -->
            <div class="mb-3">
            <label for="batchSelect" class="form-label">Select Batch</label>
            <select id="batchSelect" class="form-select" disabled>
                <option value="" disabled selected>Select a batch</option>
            </select>
            </div>

            <!-- Graduates Table -->
            <div id="graduatesTable" class="table-responsive" style="display: none;">
            <h4>Graduates List</h4>
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>Stud ID</th>
                    <th>Name</th>
                    <th>Course</th>
                    <th>Batch</th>
                    <th>Email</th>
                    <th>Gender</th>
                </tr>
                </thead>
                <tbody id="graduatesBody">
                <!-- Graduates will be populated here -->
                </tbody>
            </table>
            </div>
            </div>
        </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script>
            const courseSelect = document.getElementById('courseSelect');
        const batchSelect = document.getElementById('batchSelect');
        const graduatesTable = document.getElementById('graduatesTable');
        const graduatesBody = document.getElementById('graduatesBody');

        // Data for batches based on the selected course (now showing ranges)
        const batches = {
        bsit: ['2020-2024', '2016-2020', '2012-2016'],
        bscs: ['2018-2022', '2014-2018', '2010-2014'],
        bsoa: ['2019-2023', '2015-2019', '2011-2015'],
        baj: ['2017-2021', '2013-2017', '2009-2013'],
        beced: ['2016-2020', '2015-2019', '2013-2017'],
        beed: ['2016-2020', '2015-2019', '2013-2017'],
        bsbm: ['2016-2020', '2015-2019', '2013-2017'],
        bsentrep: ['2016-2020', '2015-2019', '2013-2017'],
        bshm: ['2016-2020', '2015-2019', '2013-2017'],
        bspsych: ['2016-2020', '2015-2019', '2013-2017']
        };

        // Data for graduates based on course and batch
        const graduates = {
        bsit: {
            '2020-2024': [
            { studId: 'S001', lastName: 'Smith', firstName: 'Alice', middleName: 'Marie', course: 'BSIT', batch: '2020-2024', email: 'alice@example.com', gender: 'Female' },
            { studId: 'S002', lastName: 'Johnson', firstName: 'Bob', middleName: 'Edward', course: 'BSIT', batch: '2020-2024', email: 'bob@example.com', gender: 'Male' }
            ],
            // Add more graduates for different batches if needed
        },
        bscs: {
            '2018-2022': [
            { studId: 'S009', lastName: 'Newton', firstName: 'Isaac', middleName: 'William', course: 'BSCS', batch: '2018-2022', email: 'isaac@example.com', gender: 'Male' },
            { studId: 'S010', lastName: 'Austen', firstName: 'Jane', middleName: 'Elizabeth', course: 'BSCS', batch: '2018-2022', email: 'jane@example.com', gender: 'Female' }
            ],
            // Add more graduates for different batches if needed
        },
        bsoa: {
            '2018-2022': [
            { studId: 'S020', lastName: 'Newton', firstName: 'Albert', middleName: 'Eugene', course: 'BSCS', batch: '2018-2022', email: 'albert.newton@example.com', gender: 'Male' },
            { studId: 'S030', lastName: 'Austen', firstName: 'Jane', middleName: 'Marie', course: 'BSCS', batch: '2018-2022', email: 'jane.m.austen@example.com', gender: 'Female' }
            ],
        },
        baj: {
            '2016-2020': [
            { studId: 'S020', lastName: 'Newton', firstName: 'Albert', middleName: 'Eugene', course: 'BSCS', batch: '2018-2022', email: 'albert.newton@example.com', gender: 'Male' },
            { studId: 'S030', lastName: 'Austen', firstName: 'Jane', middleName: 'Marie', course: 'BSCS', batch: '2018-2022', email: 'jane.m.austen@example.com', gender: 'Female' }
            ]
        },
        beced: {
            '2016-2020': [
            { studId: 'S020', lastName: 'Newton', firstName: 'Albert', middleName: 'Eugene', course: 'BSCS', batch: '2018-2022', email: 'albert.newton@example.com', gender: 'Male' },
            { studId: 'S030', lastName: 'Austen', firstName: 'Jane', middleName: 'Marie', course: 'BSCS', batch: '2018-2022', email: 'jane.m.austen@example.com', gender: 'Female' }
            ]
        },
        beed: {
            '2016-2020': [
            { studId: 'S020', lastName: 'Newton', firstName: 'Albert', middleName: 'Eugene', course: 'BSCS', batch: '2018-2022', email: 'albert.newton@example.com', gender: 'Male' },
            { studId: 'S030', lastName: 'Austen', firstName: 'Jane', middleName: 'Marie', course: 'BSCS', batch: '2018-2022', email: 'jane.m.austen@example.com', gender: 'Female' }
            ]
        },
        bsbm: {
            '2016-2020': [
            { studId: 'S020', lastName: 'Newton', firstName: 'Albert', middleName: 'Eugene', course: 'BSCS', batch: '2018-2022', email: 'albert.newton@example.com', gender: 'Male' },
            { studId: 'S030', lastName: 'Austen', firstName: 'Jane', middleName: 'Marie', course: 'BSCS', batch: '2018-2022', email: 'jane.m.austen@example.com', gender: 'Female' }
            ]
        },
        bsentrep: {
            '2016-2020': [
            { studId: 'S020', lastName: 'Newton', firstName: 'Albert', middleName: 'Eugene', course: 'BSCS', batch: '2018-2022', email: 'albert.newton@example.com', gender: 'Male' },
            { studId: 'S030', lastName: 'Austen', firstName: 'Jane', middleName: 'Marie', course: 'BSCS', batch: '2018-2022', email: 'jane.m.austen@example.com', gender: 'Female' }
            ]
        },
        bshm: {
            '2016-2020': [
            { studId: 'S020', lastName: 'Newton', firstName: 'Albert', middleName: 'Eugene', course: 'BSCS', batch: '2018-2022', email: 'albert.newton@example.com', gender: 'Male' },
            { studId: 'S030', lastName: 'Austen', firstName: 'Jane', middleName: 'Marie', course: 'BSCS', batch: '2018-2022', email: 'jane.m.austen@example.com', gender: 'Female' }
            ]
        },
        bspsych: {
            '2016-2020': [
            { studId: 'S020', lastName: 'Newton', firstName: 'Albert', middleName: 'Eugene', course: 'BSCS', batch: '2018-2022', email: 'albert.newton@example.com', gender: 'Male' },
            { studId: 'S030', lastName: 'Austen', firstName: 'Jane', middleName: 'Marie', course: 'BSCS', batch: '2018-2022', email: 'jane.m.austen@example.com', gender: 'Female' }
            ]
        },
        // Similarly, data for BSBA and BSME courses can be added
        };

        // Event listener for course selection
        courseSelect.addEventListener('change', function() {
        const selectedCourse = this.value;
        batchSelect.innerHTML = '<option value="" disabled selected>Select a batch</option>'; // Reset batches
        if (selectedCourse) {
            batchSelect.disabled = false;
            batches[selectedCourse].forEach(batch => {
            const option = document.createElement('option');
            option.value = batch;
            option.textContent = batch;
            batchSelect.appendChild(option);
            });
            displayGraduates(selectedCourse);
        } else {
            batchSelect.disabled = true;
            graduatesTable.style.display = 'none';
        }
        });

        // Event listener for batch selection
        batchSelect.addEventListener('change', function() {
        const selectedCourse = courseSelect.value;
        const selectedBatch = this.value;
        displayGraduates(selectedCourse, selectedBatch);
        });

        // Function to display graduates based on course and batch
        function displayGraduates(course, batch) {
        graduatesBody.innerHTML = ''; // Clear previous data
        if (course && graduates[course]) {
            let graduatesList = graduates[course];
            if (batch) {
            graduatesList = graduates[course][batch];
            } else {
            graduatesList = Object.values(graduatesList).flat(); // Merge all batches
            }

            graduatesList.forEach(graduate => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${graduate.studId}</td>
                <td>${graduate.lastName}, ${graduate.firstName} ${graduate.middleName}</td>
                <td>${graduate.course}</td>
                <td>${graduate.batch}</td>
                <td>${graduate.email}</td>
                <td>${graduate.gender}</td>
            `;
            graduatesBody.appendChild(row);
            });

            graduatesTable.style.display = 'block';
        } else {
            graduatesTable.style.display = 'none';
        }
        }
        document.getElementById('download-pdf').addEventListener('click', () => {
            const element = document.querySelector('form');

            // Get the current date
            const currentDate = new Date();
            const formattedDate = currentDate.toISOString().slice(0, 10);

            const opt = {
                margin: 1,
                filename: `alumni-report-${formattedDate}.pdf`,
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
                    orientation: 'landscape'
                }
            };

            html2pdf().from(element).set(opt).save();
        });

        document.getElementById('refresh-page').addEventListener('click', () => {
            location.reload();
        });
    </script>
</body>

</html>