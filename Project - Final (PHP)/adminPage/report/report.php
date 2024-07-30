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

    // Check if user is an alumni
    $stmt = $conn->prepare("SELECT * FROM alumni WHERE alumni_id = ? AND email = ?");
    $stmt->bind_param("ss", $account, $account_email);
    $stmt->execute();
    $user_result = $stmt->get_result();

    if ($user_result->num_rows > 0) {
        // User is an alumni
        header('Location: ../../alumniPage/dashboard_user.php');
        exit();
    }
    $stmt->close();
    
} else {
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
$query_event = "SELECT MONTH(schedule) as month, COUNT(*) as count FROM event GROUP BY MONTH(schedule)";
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

            <div class="container mt-4 p-3 shadow bg-white rounded">
                <button id="download-pdf" class="btn btn-primary">Download as PDF</button>
                <button id="refresh-page" class="btn btn-secondary">Refresh</button>
            </div>
            <div class="container mt-4 p-3 shadow bg-white rounded">
                <form id="report-form">
                    <div class="summary-boxes">
                        <div class="summary-box" id="alumni">
                            <h2>Total Alumni Registered</h2>
                            <p><?php echo $count_alumni; ?></p>
                        </div>
                        <div class="summary-box" id="coordinator">
                            <h2>Total Coordinators Registered</h2>
                            <p><?php echo $coordinator_count; ?></p>
                        </div>
                        <div class="summary-box" id="events">
                            <h2>Total Events Posted</h2>
                            <p><?php echo $event_count; ?></p>
                        </div>
                    </div>
                    <div class="charts">
                        <div class="chart-container">
                            <canvas id="alumniChart"></canvas>
                        </div>
                        <div class="chart-container">
                            <canvas id="eventsChart"></canvas>
                        </div>
                        <div class="chart-container">
                            <canvas id="alumnimonth"></canvas>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>
    <script>
        const alumniData = {
            labels: <?php echo json_encode($labels); ?>,
            datasets: [{
                label: 'Alumni Registered in Courses',
                data: <?php echo json_encode($data); ?>,
                backgroundColor: [
                    'rgba(108, 108, 108, 0.6)',
                    'rgba(255, 255, 0, 0.6)',
                    'rgba(255, 255, 0, 0.8)',
                    'rgba(103, 0, 0, 0.6)',
                    'rgba(53, 83, 10, 0.6)',
                    'rgba(255, 165, 0)',
                    'rgba(0, 0, 179, 0.6)',
                    'rgba(0, 0, 179, 0.4)',
                    'rgba(0, 0, 179, 0.2)',
                    'rgba(108, 108, 108, 0.8)',
                ],
                borderColor: [
                    'rgba(0, 0, 0, 0.3)',
                ],
                borderWidth: 1
            }]
        };

        const alumniConfig = {
            type: 'bar',
            data: alumniData,
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return Number.isInteger(value) ? value : null;
                            }
                        }
                    }
                }
            }
        };

        const alumniChart = new Chart(
            document.getElementById('alumniChart'),
            alumniConfig
        );

        const eventsData = {
            labels: <?php echo json_encode($labels_event); ?>,
            datasets: [{
                label: 'Total Events Posted per Year',
                data: <?php echo json_encode($data_event); ?>,
                backgroundColor: [
                    'rgba(0, 179, 71, 0.6)',
                    'rgba(255, 0, 0, 0.6)',
                    'rgba(255, 0, 0, 0.8)',
                    'rgba(0, 0, 179, 0.6)',
                    'rgba(255, 165, 0)',
                    'rgba(0, 0, 179, 0.4)',
                    'rgba(53, 83, 10, 0.6)',
                    'rgba(108, 108, 108, 0.6)',
                    'rgba(255, 255, 0, 0.6)',
                    'rgba(108, 108, 108, 0.8)',
                    'rgba(103, 0, 0, 0.6)',
                    'rgba(255, 255, 0, 0.8)',
                ],
                borderColor: [
                    'rgba(0, 0, 0, 0.3)',
                ],
                borderWidth: 1
            }]
        };

        const eventsConfig = {
            type: 'bar',
            data: eventsData,
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return Number.isInteger(value) ? value : null;
                            }
                        }
                    }
                }
            }
        };

        const eventsChart = new Chart(
            document.getElementById('eventsChart'),
            eventsConfig
        );

        const alumniMonthData = {
            labels: <?php echo json_encode($labels_alumniCount); ?>,
            datasets: [{
                label: 'Total Alumni Registered per Month',
                data: <?php echo json_encode($data_alumniCount); ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        };

        const alumniMonthConfig = {
            type: 'line',
            data: alumniMonthData,
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        };

        const alumniMonthChart = new Chart(
            document.getElementById('alumnimonth'),
            alumniMonthConfig
        );

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
                    format: 'letter',
                    orientation: 'portrait'
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