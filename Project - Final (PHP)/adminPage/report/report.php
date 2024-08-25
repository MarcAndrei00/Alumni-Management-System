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

//query for events count
$sql_event = "SELECT COUNT(event_id) AS events_count FROM event";
$result_event = $conn->query($sql_event);
$row_event = $result_event->fetch_assoc();
$event_count = $row_event['events_count'];

// EVENT COUNT EVERY MONTH
$query_event = "SELECT MONTH(date) as month, COUNT(*) as count FROM event GROUP BY MONTH(date)";
$res_event = $conn->query($query_event);

$labels_event = ['Jan.', 'Feb.', 'Mar.', 'Apr.', 'May', 'June', 'July', 'Aug.', 'Sept.', 'Oct.', 'Nov.', 'Dec.'];
$data_event = array_fill(0, 12, 0); // Initialize an array with 12 zeros for each month

if ($res_event->num_rows > 0) {
    while ($row_event = $res_event->fetch_assoc()) {
        $data_event[$row_event['month'] - 1] = $row_event['count']; // Store the count for the respective month
    }
}

// DONATION COUNT PER MONTH
$query = "SELECT MONTH(donate_date) AS donate_month, SUM(donation_amount) AS total_donations
          FROM donation_table
          GROUP BY donate_month
          ORDER BY donate_month";
$result = $conn->query($query);
$monthly_donation = array_fill(0, 12, 0);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $monthly_donation[$row['donate_month'] - 1] = $row['total_donations'];
    }
}

// Query to get the donation data
$query = "SELECT MONTH(donate_date) AS donate_month, COUNT(*) AS donation_count
          FROM donation_table
          GROUP BY donate_month
          ORDER BY donate_month";
$result = $conn->query($query);

// Initialize months and counts
$month_names = ['Jan.', 'Feb.', 'Mar.', 'Apr.', 'May', 'June', 'July', 'Aug.', 'Sept.', 'Oct.', 'Nov.', 'Dec.'];
$months = $month_names;
$donation_count = array_fill(0, 12, 0); // Initialize all months with 0

// Calculate total donations
$total_donations = 0;

// Process the query result
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $donate_month = $row['donate_month'] - 1;
        $donation_count[$donate_month] = $row['donation_count']; // Update count for the month
        $total_donations += $row['donation_count']; // Calculate total donations
    }
}

// Calculate percentages
$percentages = array_map(function ($count) use ($total_donations) {
    return $total_donations > 0 ? number_format(($count / $total_donations * 100), 1) : 0;
}, $donation_count);



// ALUMNI COUNT
$query_event = "SELECT MONTH(date_created) as month, COUNT(*) as count FROM alumni GROUP BY MONTH(date_created)";
$res_event = $conn->query($query_event);

$labels_event = ['Jan.', 'Feb.', 'Mar.', 'Apr.', 'May', 'June', 'July', 'Aug.', 'Sept.', 'Oct.', 'Nov.', 'Dec.'];
$registered_alumni = array_fill(0, 12, 0); // Initialize an array with 12 zeros for each month

if ($res_event->num_rows > 0) {
    while ($row_event = $res_event->fetch_assoc()) {
        $registered_alumni[$row_event['month'] - 1] = $row_event['count']; // Store the count for the respective month
    }
}

// For registered alumni
$sql = "SELECT COUNT(*) as count FROM alumni";
$result = $conn->query($sql);
$registeredCount = $result->fetch_assoc()['count'];

// For unregistered alumni (from list_of_graduate)
$sql2 = "SELECT COUNT(*) as count FROM list_of_graduate";
$result2 = $conn->query($sql2);
$unregisteredCount = $result2->fetch_assoc()['count'];

// ALUMNI FOR EVERY COURSES
$alumni_list = "SELECT * FROM alumni";
$result = $conn->query($alumni_list);

// verified
$verified = "SELECT COUNT(*) as count FROM alumni WHERE status='Verified'";
$result_verified = $conn->query($verified);
$verified = $result_verified->fetch_assoc()['count'];

// unverified
$unverified = "SELECT COUNT(*) as count FROM alumni WHERE status='Unverified'";
$result_unverified = $conn->query($unverified);
$unverified = $result_unverified->fetch_assoc()['count'];

// inactive
$inactive = "SELECT COUNT(*) as count FROM alumni_archive";
$result_inactive = $conn->query($inactive);
$inactive = $result_inactive->fetch_assoc()['count'];


// ALUMNI FOR EVERY COURSES
$alumni_list = "SELECT * FROM alumni";
$result = $conn->query($alumni_list);

// Initialize course counts
$course_counts = [
    'BAJ' => 0,
    'BECEd' => 0,
    'BEEd' => 0,
    'BSBM' => 0,
    'BSOA' => 0,
    'BSEntrep' => 0,
    'BSHM' => 0,
    'BSIT' => 0,
    'BSCS' => 0,
    'BSc(Psych)' => 0
];

while ($row = $result->fetch_assoc()) {
    $courseCode = '';

    switch ($row['course']) {
        case 'Bachelor of Arts in Journalism':
            $courseCode = 'BAJ';
            break;
        case 'Bachelor of Secondary Education':
            $courseCode = 'BECEd';
            break;
        case 'Bachelor of Elementary Education':
            $courseCode = 'BEEd';
            break;
        case 'Bachelor of Science in Business Management':
            $courseCode = 'BSBM';
            break;
        case 'Bachelor of Science in Office Administration':
            $courseCode = 'BSOA';
            break;
        case 'Bachelor of Science in Entrepreneurship':
            $courseCode = 'BSEntrep';
            break;
        case 'Bachelor of Science in Hospitality Management':
            $courseCode = 'BSHM';
            break;
        case 'Bachelor of Science in Information Technology':
            $courseCode = 'BSIT';
            break;
        case 'Bachelor of Science in Computer Science':
            $courseCode = 'BSCS';
            break;
        case 'Bachelor of Science in Psychology':
            $courseCode = 'BSc(Psych)';
            break;
    }

    // Increment the course count
    if (!empty($courseCode)) {
        $course_counts[$courseCode]++;
    }
}

// Initialize course counts for verified, unverified, and inactive alumni
$verifCourse = $unverifCourse = $inactiveCourse = [
    'BAJ' => 0,
    'BECEd' => 0,
    'BEEd' => 0,
    'BSBM' => 0,
    'BSOA' => 0,
    'BSEntrep' => 0,
    'BSHM' => 0,
    'BSIT' => 0,
    'BSCS' => 0,
    'BSc(Psych)' => 0
];

// Fetch verified alumni
$alumni_verified = "SELECT * FROM alumni WHERE status='Verified'";
$resVerified = $conn->query($alumni_verified);

while ($row = $resVerified->fetch_assoc()) {
    switch ($row['course']) {
        case '  ':
            $verifCourse['BAJ']++;
            break;
        case 'Bachelor of Secondary Education':
            $verifCourse['BECEd']++;
            break;
        case 'Bachelor of Elementary Education':
            $verifCourse['BEEd']++;
            break;
        case 'Bachelor of Science in Business Management':
            $verifCourse['BSBM']++;
            break;
        case 'Bachelor of Science in Office Administration':
            $verifCourse['BSOA']++;
            break;
        case 'Bachelor of Science in Entrepreneurship':
            $verifCourse['BSEntrep']++;
            break;
        case 'Bachelor of Science in Hospitality Management':
            $verifCourse['BSHM']++;
            break;
        case 'Bachelor of Science in Information Technology':
            $verifCourse['BSIT']++;
            break;
        case 'Bachelor of Science in Computer Science':
            $verifCourse['BSCS']++;
            break;
        case 'Bachelor of Science in Psychology':
            $verifCourse['BSc(Psych)']++;
            break;
    }
}

// Fetch unverified alumni
$alumni_unverified = "SELECT * FROM alumni WHERE status='Unverified'";
$resUnverified = $conn->query($alumni_unverified);

while ($row = $resUnverified->fetch_assoc()) {
    switch ($row['course']) {
        case 'Bachelor of Arts in Journalism':
            $unverifCourse['BAJ']++;
            break;
        case 'Bachelor of Secondary Education':
            $unverifCourse['BECEd']++;
            break;
        case 'Bachelor of Elementary Education':
            $unverifCourse['BEEd']++;
            break;
        case 'Bachelor of Science in Business Management':
            $unverifCourse['BSBM']++;
            break;
        case 'Bachelor of Science in Office Administration':
            $unverifCourse['BSOA']++;
            break;
        case 'Bachelor of Science in Entrepreneurship':
            $unverifCourse['BSEntrep']++;
            break;
        case 'Bachelor of Science in Hospitality Management':
            $unverifCourse['BSHM']++;
            break;
        case 'Bachelor of Science in Information Technology':
            $unverifCourse['BSIT']++;
            break;
        case 'Bachelor of Science in Computer Science':
            $unverifCourse['BSCS']++;
            break;
        case 'Bachelor of Science in Psychology':
            $unverifCourse['BSc(Psych)']++;
            break;
    }
}

// Fetch inactive alumni
$alumni_inactive = "SELECT * FROM alumni_archive";
$resInactive = $conn->query($alumni_inactive);

while ($row = $resInactive->fetch_assoc()) {
    switch ($row['course']) {
        case 'Bachelor of Arts in Journalism':
            $inactiveCourse['BAJ']++;
            break;
        case 'Bachelor of Secondary Education':
            $inactiveCourse['BECEd']++;
            break;
        case 'Bachelor of Elementary Education':
            $inactiveCourse['BEEd']++;
            break;
        case 'Bachelor of Science in Business Management':
            $inactiveCourse['BSBM']++;
            break;
        case 'Bachelor of Science in Office Administration':
            $inactiveCourse['BSOA']++;
            break;
        case 'Bachelor of Science in Entrepreneurship':
            $inactiveCourse['BSEntrep']++;
            break;
        case 'Bachelor of Science in Hospitality Management':
            $inactiveCourse['BSHM']++;
            break;
        case 'Bachelor of Science in Information Technology':
            $inactiveCourse['BSIT']++;
            break;
        case 'Bachelor of Science in Computer Science':
            $inactiveCourse['BSCS']++;
            break;
        case 'Bachelor of Science in Psychology':
            $inactiveCourse['BSc(Psych)']++;
            break;
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
    <link rel="stylesheet" href="./css/reportgraph.css">
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
                        <a href="../report/report.php" class="active">
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
                    <button id="another-page" class="btn btn-success" onclick="window.location.href='../report/reportgraph.php'">List of Graduates</button>
                </div>
            </div>

            <div class="container mt-4 p-3 shadow bg-white rounded">
                <form id="report-form">
                    <div class="summary-boxes">
                        <div id="hidden-content" style="display:none;">
                            <img src="../../assets/head.jpg" id="header-image" style="width:100%; height:auto;">
                        </div>
                        <div class="summary-box" id="alumni">
                            <h2>Total Alumni Registered</h2>
                            <p><?php echo $count_alumni; ?></p>
                        </div>
                        <div class="summary-box" id="events">
                            <h2>Total Events Posted</h2>
                            <p><?php echo $event_count; ?></p>
                        </div>
                    </div>
                </form>
            </div>
            <div class="container mt-4 p-3 shadow bg-white rounded d-flex justify-content-between">
                <div class="chartCard">
                    <div class="chartBox">
                        <canvas id="myChart"></canvas>
                    </div>
                </div>
                <!-- Pie Charts -->
                <div class="chartCard">
                    <div class="chartBox">
                        <canvas id="registeredUnregisteredChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="container mt-4 p-3 shadow bg-white rounded d-flex justify-content-between">
                <div class="chartCard">
                    <div class="chartBox">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
                <div class="chartCard">
                    <div class="chartBoxs">
                        <canvas id="statusPerCourseChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="container mt-4 p-3 shadow bg-white rounded d-flex justify-content-between">
                <!-- Bar Charts -->
                <div class="chartCard">
                    <div class="chartBoxs">
                        <canvas id="registeredPerCourseChart"></canvas>
                    </div>
                </div>
                <div class="chartCard">
                    <div class="chartBoxs">
                        <canvas id="eventsPerMonthChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="container mt-4 p-3 shadow bg-white rounded d-flex justify-content-between">
                <!-- Doughnut Chart: Donations -->
                <div class="chartCard">
                    <div class="chartBoxs">
                        <canvas id="donationsChart"></canvas> <!-- Updated ID -->
                    </div>
                </div>
                <!-- Table: Donations Data -->
                <div class="chartCard">
                    <div class="chartBoxs">
                        <canvas id="donationPerMonthChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="container mt-4 p-3 shadow bg-white rounded d-flex justify-content-between">
                <div class="chartCard">
                    <div class="chartBoxs">
                        <canvas id="registeredPerMonthChart"></canvas>
                    </div>
                </div>
            </div>

    </div>
    </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/chart.js/dist/chart.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/chartjs-plugin-datalabels/2.2.0/chartjs-plugin-datalabels.min.js" integrity="sha512-JPcRR8yFa8mmCsfrw4TNte1ZvF1e3+1SdGMslZvmrzDYxS69J7J49vkFL8u6u8PlPJK+H3voElBtUCzaXj+6ig==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        const data = {
            labels: ['BSIT', 'BSCS', 'BSOA', 'BAJ', 'BECEd', 'BEEd', 'BSBM', 'BSEntrep', 'BSHM', 'BSPsych'],
            datasets: [{
                label: 'Alumni Registered',
                data: [
                    <?= $course_counts['BSIT']; ?>,
                    <?= $course_counts['BSCS']; ?>,
                    <?= $course_counts['BSOA']; ?>,
                    <?= $course_counts['BAJ']; ?>,
                    <?= $course_counts['BECEd']; ?>,
                    <?= $course_counts['BEEd']; ?>,
                    <?= $course_counts['BSBM']; ?>,
                    <?= $course_counts['BSEntrep']; ?>,
                    <?= $course_counts['BSHM']; ?>,
                    <?= $course_counts['BSc(Psych)']; ?>
                ],
                backgroundColor: [
                    'rgba(255, 26, 104, 0.5)',
                    'rgba(54, 162, 235, 0.5)',
                    'rgba(255, 206, 86, 0.5)',
                    'rgba(75, 192, 192, 0.5)',
                    'rgba(153, 102, 255, 0.5)',
                    'rgba(255, 159, 64, 0.5)',
                    'rgba(0, 0, 0, 0.5)',
                    'rgba(123, 50, 123, 0.5)',
                    'rgba(189, 183, 107, 0.5)',
                    'rgba(72, 61, 139, 0.5)'
                ],
                borderColor: [
                    'rgba(255, 26, 104, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)',
                    'rgba(0, 0, 0, 1)',
                    'rgba(123, 50, 123, 1)',
                    'rgba(189, 183, 107, 1)',
                    'rgba(72, 61, 139, 1)'
                ],
                borderWidth: 1
            }]
        };

        // config 
        const config = {
            type: 'pie',
            data,
            options: {
                plugins: {
                    legend: {
                        display: true,
                        position: 'right', // Place the legend (labels) to the right of the chart
                        labels: {
                            boxWidth: 20, // Size of the colored box beside the label
                            padding: 20, // Space between the label and the box
                        }
                    },
                    tooltip: {
                        enabled: false
                    },
                    datalabels: {
                        formatter: (value, context) => {
                            const datapoints = context.chart.data.datasets[0].data;
                            const totalSum = datapoints.reduce((total, datapoint) => total + datapoint, 0);
                            const percentageValue = (value / totalSum * 100).toFixed(1);
                            return `${percentageValue}%`; // Only display the percentage
                        }
                    },

                }
            },
            plugins: [ChartDataLabels],
        };


        // render init block
        const myChart = new Chart(
            document.getElementById('myChart'),
            config
        );
        // Common Colors for Consistency

        const colors = {
            background: [
                'rgba(255, 26, 104, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(255, 159, 64, 0.2)',
                'rgba(0, 0, 0, 0.2)',
                'rgba(123, 50, 123, 0.2)',
                'rgba(189, 183, 107, 0.2)',
                'rgba(72, 61, 139, 0.2)'
            ],
            border: [
                'rgba(255, 26, 104, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)',
                'rgba(0, 0, 0, 1)',
                'rgba(123, 50, 123, 1)',
                'rgba(189, 183, 107, 1)',
                'rgba(72, 61, 139, 1)'
            ]

        };

        // Config for Pie Charts
        function createPieChart(ctx, labels, data, colors) {
            return new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: colors.background,
                        borderColor: colors.border,
                        borderWidth: 1
                    }]
                },
                options: {
                    plugins: {
                        datalabels: {
                            formatter: (value, context) => {
                                const datapoints = context.chart.data.datasets[0].data;
                                const totalSum = datapoints.reduce((total, datapoint) => total + datapoint, 0);
                                const percentageValue = (value / totalSum * 100).toFixed(1);
                                return `${percentageValue}%`;
                            }
                        }
                    }
                },
                plugins: [ChartDataLabels],
            });
        }

        // Config for Bar Charts
        function createBarChart(ctx, labels, datasets) {
            return new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: datasets
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true, // Start the y-axis at 0
                            ticks: {
                                stepSize: 1, // Use whole numbers for y-axis increments
                                callback: function(value) {
                                    return Number.isInteger(value) ? value : ''; // Only display whole numbers
                                }
                            }
                        }
                    }
                }
            });
        }


        // Pie Chart: Registered vs Unregistered Alumni
        const registeredUnregisteredChart = createPieChart(
            document.getElementById('registeredUnregisteredChart'),
            ['Registered', 'Unregistered'],
            [<?php echo $registeredCount; ?>, <?php echo $unregisteredCount; ?>], // Use PHP variables here
            {
                background: [
                    'rgba(255, 26, 104, 0.5)',
                    'rgba(54, 162, 235, 0.5)'
                ],
                border: [
                    'rgba(255, 26, 104, 1)',
                    'rgba(54, 162, 235, 1)'
                ]
            }
        );

        // Pie Chart: Verified, Unverified, and Inactive Alumni
        const statusChart = createPieChart(
            document.getElementById('statusChart'),
            ['Verified', 'Unverified', 'Inactive'],
            [<?php echo $verified; ?>, <?php echo $unverified; ?>, <?php echo $inactive; ?>], {
                background: [
                    'rgba(255, 26, 104, 0.5)',
                    'rgba(54, 162, 235, 0.5)',
                    'rgba(255, 206, 86, 0.5)'
                ],
                border: [
                    'rgba(255, 26, 104, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)'
                ]
            }
        );

        // Bar Chart: Verified, Unverified, and Inactive Alumni Per Course
        const statusPerCourseChart = createBarChart(
            document.getElementById('statusPerCourseChart'),
            ['BSIT', 'BSCS', 'BSOA', 'BAJ', 'BECED', 'BEED', 'BSBM', 'BSEntrep', 'BSHM', 'BSPsych'],
            [{
                    label: 'Verified',
                    data: [
                        <?= $verifCourse['BSIT']; ?>,
                        <?= $verifCourse['BSCS']; ?>,
                        <?= $verifCourse['BSOA']; ?>,
                        <?= $verifCourse['BAJ']; ?>,
                        <?= $verifCourse['BECEd']; ?>,
                        <?= $verifCourse['BEEd']; ?>,
                        <?= $verifCourse['BSBM']; ?>,
                        <?= $verifCourse['BSEntrep']; ?>,
                        <?= $verifCourse['BSHM']; ?>,
                        <?= $verifCourse['BSc(Psych)']; ?>
                    ],
                    backgroundColor: colors.background[0],
                    borderColor: colors.border[0], // Solid border color
                    borderWidth: 1
                },
                {
                    label: 'Unverified',
                    data: [
                        <?= $unverifCourse['BSIT']; ?>,
                        <?= $unverifCourse['BSCS']; ?>,
                        <?= $unverifCourse['BSOA']; ?>,
                        <?= $unverifCourse['BAJ']; ?>,
                        <?= $unverifCourse['BECEd']; ?>,
                        <?= $unverifCourse['BEEd']; ?>,
                        <?= $unverifCourse['BSBM']; ?>,
                        <?= $unverifCourse['BSEntrep']; ?>,
                        <?= $unverifCourse['BSHM']; ?>,
                        <?= $unverifCourse['BSc(Psych)']; ?>
                    ],
                    backgroundColor: colors.background[1],
                    borderColor: colors.border[1], // Solid border color
                    borderWidth: 1
                },
                {
                    label: 'Inactive',
                    data: [
                        <?= $inactiveCourse['BSIT']; ?>,
                        <?= $inactiveCourse['BSCS']; ?>,
                        <?= $inactiveCourse['BSOA']; ?>,
                        <?= $inactiveCourse['BAJ']; ?>,
                        <?= $inactiveCourse['BECEd']; ?>,
                        <?= $inactiveCourse['BEEd']; ?>,
                        <?= $inactiveCourse['BSBM']; ?>,
                        <?= $inactiveCourse['BSEntrep']; ?>,
                        <?= $inactiveCourse['BSHM']; ?>,
                        <?= $inactiveCourse['BSc(Psych)']; ?>
                    ],
                    backgroundColor: colors.background[2],
                    borderColor: colors.border[2], // Solid border color
                    borderWidth: 1
                }
            ]
        );


        // Bar Chart: Registered Alumni Per Course
        const registeredPerCourseChart = createBarChart(
            document.getElementById('registeredPerCourseChart'),
            ['BSIT', 'BSCS', 'BSOA', 'BAJ', 'BECED', 'BEED', 'BSBM', 'BSENTREP', 'BSHM', 'BSPsych'],
            [{
                label: 'Registered Alumni Per Course',
                data: [
                    <?= $course_counts['BSIT']; ?>,
                    <?= $course_counts['BSCS']; ?>,
                    <?= $course_counts['BSOA']; ?>,
                    <?= $course_counts['BAJ']; ?>,
                    <?= $course_counts['BECEd']; ?>,
                    <?= $course_counts['BEEd']; ?>,
                    <?= $course_counts['BSBM']; ?>,
                    <?= $course_counts['BSEntrep']; ?>,
                    <?= $course_counts['BSHM']; ?>,
                    <?= $course_counts['BSc(Psych)']; ?>
                ],
                backgroundColor: colors.background[3],
                borderColor: colors.border[3], // Solid border color
                borderWidth: 1
            }]
        );

        // Bar Chart: Events Created Per Month
        const labels_event = <?php echo json_encode($labels_event); ?>;
        const data_event = <?php echo json_encode($data_event); ?>;

        const eventsPerMonthChart = createBarChart(
            document.getElementById('eventsPerMonthChart'),
            labels_event, // Use the labels for all months
            [{
                label: 'Events Created',
                data: data_event, // Use the dynamic event data from PHP
                backgroundColor: colors.background[4],
                borderColor: colors.border[4],
                borderWidth: 1
            }]
        );


        // Bar Chart: Alumni Registered Per Month
        const registered_alumni = <?php echo json_encode($registered_alumni); ?>;

        // Create the bar chart with the data using your custom configuration
        const registeredPerMonthChart = createBarChart(
            document.getElementById('registeredPerMonthChart'),
            ['Jan.', 'Feb.', 'Mar.', 'Apr.', 'May', 'June', 'July', 'Aug.', 'Sept.', 'Oct.', 'Nov.', 'Dec.'],
            [{
                label: 'Registered Alumni Every Month',
                data: registered_alumni, // Use the PHP variable here
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        );

        // Pie Chart: Donations
        const months = <?php echo json_encode($months); ?>;
        const percentages = <?php echo json_encode($percentages); ?>;

        // Create a mapping of months and percentages, keeping all months but setting zero percentages to null
        const dataWithLabels = months.map((month, index) => ({
            month: month,
            percentage: percentages[index]
        }));

        // Filter out zero percentage values for chart display
        const filteredData = dataWithLabels.filter(data => data.percentage > 0);

        const filteredMonths = filteredData.map(data => data.month);
        const filteredPercentages = filteredData.map(data => data.percentage);

        // Configuration for the pie chart
        const configDonate = {
            type: 'pie',
            data: {
                labels: months, // Show all months
                datasets: [{
                    label: 'Total Donation Per Month',
                    data: percentages, // Show all data
                    backgroundColor: [
                        '#FF8C8C', '#FFB84D', '#FFFF66', '#66FF66', '#66B3FF', '#FF66B2', '#D9B3FF', '#99FFFF', '#FFFF99', '#FFB84D', '#FF6666', '#B3A1FF'
                    ],
                    borderColor: [
                        '#FF8C8C', '#FFB84D', '#FFFF66', '#66FF66', '#66B3FF', '#FF66B2', '#D9B3FF', '#99FFFF', '#FFFF99', '#FFB84D', '#FF6666', '#B3A1FF'
                    ],
                    borderWidth: 1,

                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: {
                    padding: {
                        right: 80 // Decrease space between the pie chart and the legend
                    }
                },
                plugins: {
                    tooltip: {
                        enabled: false // Disable tooltips
                    },
                    legend: {
                        display: true,
                        position: 'right', // Place the legend (labels) to the right of the chart
                        labels: {
                            boxWidth: 30, // Size of the colored box beside the label
                            padding: 10, // Space between the label and the box

                        }
                    },
                    datalabels: {
                        formatter: (value, context) => {
                            return value > 0 ? `${value}%` : null; // Show percentage only if value > 0
                        },
                        color: '#333', // Darker color for the text
                        font: {
                            weight: 'bold',
                            size: 14
                        }
                    }
                }
            },
            plugins: [ChartDataLabels]
        };

        // Initialize the chart
        const donationsChart = new Chart(document.getElementById('donationsChart'), configDonate);

        // Bar Chart for Monthly donation
        const monthly_donation = <?php echo json_encode($monthly_donation); ?>;
        const donationPerMonthChart = createBarChart(
            document.getElementById('donationPerMonthChart'),
            ['Jan.', 'Feb.', 'Mar.', 'Apr.', 'May', 'June', 'July', 'Aug.', 'Sept.', 'Oct.', 'Nov.', 'Dec.'],
            [{
                label: 'Total Monthly Donation',
                data: monthly_donation, // This variable is populated from PHP
                backgroundColor: 'rgba(255, 206, 86, 0.5)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        );

        document.getElementById('download-pdf').addEventListener('click', () => {
            const hiddenContent = document.getElementById('hidden-content').innerHTML;
            const formContent = document.querySelector('form').outerHTML;

            // Combine hidden content and form content
            const combinedContent = hiddenContent + formContent;

            // Create a temporary container to hold all content
            const tempContainer = document.createElement('div');
            tempContainer.innerHTML = combinedContent;

            // Append the header image at the top of the content
            const headerImage = document.createElement('img');
            headerImage.src = '../../assets/head.jpg'; // Adjust the path if necessary
            headerImage.style.width = '100%';
            headerImage.style.height = 'auto';
            tempContainer.prepend(headerImage);

            // Create a container for Total Donation per Month and the table to be side by side
            const donationAndTableContainer = document.createElement('div');
            donationAndTableContainer.style.display = 'flex';
            donationAndTableContainer.style.justifyContent = 'space-between';
            donationAndTableContainer.style.marginBottom = '20px'; // Space between this section and graphs

            // Append the donation content (Total Donation per Month) to the container
            const donationContent = document.getElementById('donation-per-month-section'); // Assuming the donation chart has this id
            if (donationContent) {
                const clonedDonationContent = donationContent.cloneNode(true);
                donationAndTableContainer.appendChild(clonedDonationContent);
            }

            // // Create the table element
            // const tableContainer = document.createElement('div');
            // const table = document.createElement('table');
            // table.style.borderCollapse = 'collapse';
            // table.style.width = '173%';

            // // Add some sample data to the table
            // const tableHeader = `
            //     <thead>
            //         <tr>
            //             <th style="border: 1px solid black; padding: 8px;">Event Title</th>
            //             <th style="border: 1px solid black; padding: 8px;">Total Donation</th>
            //             <th style="border: 1px solid black; padding: 8px;">Total Donors</th>
            //         </tr>
            //     </thead>`;
            // const tableBody = `
            //     <tbody>
            //         <tr>
            //             <td style="border: 1px solid black; padding: 8px;">Alumni Night</td>
            //             <td style="border: 1px solid black; padding: 8px;">$500</td>
            //             <td style="border: 1px solid black; padding: 8px;">5</td>
            //         </tr>
            //         <tr>
            //             <td style="border: 1px solid black; padding: 8px;">Fundrasing</td>
            //             <td style="border: 1px solid black; padding: 8px;">$300</td>
            //             <td style="border: 1px solid black; padding: 8px;">10</td>
            //         </tr>
            //         <tr>
            //             <td style="border: 1px solid black; padding: 8px;">Alumni Sportsfest</td>
            //             <td style="border: 1px solid black; padding: 8px;">$200</td>
            //             <td style="border: 1px solid black; padding: 8px;">15</td>
            //         </tr>
            //         <tr>
            //             <td style="border: 1px solid black; padding: 8px;">Grand Ball</td>
            //             <td style="border: 1px solid black; padding: 8px;">$100</td>
            //             <td style="border: 1px solid black; padding: 8px;">20</td>
            //         </tr>
            //     </tbody>`;

            // table.innerHTML = tableHeader + tableBody;
            // tableContainer.appendChild(table);

            // // Append the table beside the donation content
            // donationAndTableContainer.appendChild(tableContainer);

            // // Add the combined container to the temporary content container
            // tempContainer.appendChild(donationAndTableContainer);

            // Append the Event Created section below the Total Donation per Month and Table
            const eventCreatedContent = document.getElementById('event-created-section'); // Assuming the event chart has this id
            if (eventCreatedContent) {
                const clonedEventCreatedContent = eventCreatedContent.cloneNode(true);
                tempContainer.appendChild(clonedEventCreatedContent);
            }

            // Create a container for the graphs to ensure two per row
            const graphContainer = document.createElement('div');
            graphContainer.style.display = 'flex';
            graphContainer.style.flexWrap = 'wrap';
            graphContainer.style.gap = '10px'; // Add some spacing between graphs
            graphContainer.style.justifyContent = 'space-between';

            // Clone and append the charts into the graph container
            const charts = document.querySelectorAll('canvas');
            charts.forEach(chart => {
                const clonedCanvas = document.createElement('canvas');
                clonedCanvas.width = chart.width / 2; // Scale down width
                clonedCanvas.height = chart.height / 2; // Scale down height
                const ctx = clonedCanvas.getContext('2d');
                ctx.drawImage(chart, 0, 0, clonedCanvas.width, clonedCanvas.height); // Adjust size
                graphContainer.appendChild(clonedCanvas);

                // If two graphs are in a row, start a new row
                if (graphContainer.children.length % 2 === 0) {
                    tempContainer.appendChild(graphContainer);
                    graphContainer.style.display = 'flex'; // Reset for the next row
                }
            });

            // Append the last row of graphs if not already appended
            if (graphContainer.children.length % 2 !== 0) {
                tempContainer.appendChild(graphContainer);
            }

            // Get the current date
            const currentDate = new Date();
            const formattedDate = currentDate.toISOString().slice(0, 10);

            const opt = {
                margin: 0.50,
                filename: `alumni-report-${formattedDate}.pdf`,
                image: {
                    type: 'jpeg',
                    quality: 0.98
                },
                html2canvas: {
                    scale: 2
                }, // Increased scale for better quality
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
    </script>
</body>

</html>