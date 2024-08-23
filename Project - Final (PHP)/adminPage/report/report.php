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
    while ($row_alumniCount = $res_alumniCount->fetch_assoc()) {
        $data_alumniCount[$row_alumniCount['month'] - 1] = $row_alumniCount['count'];
    }
}



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
            data: data,
            options: {
                plugins: {
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
                    }
                }
            },
            plugins: [ChartDataLabels]
        };


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
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        // Pie Chart: Registered vs Unregistered Alumni
        const registeredUnregisteredChart = createPieChart(
            document.getElementById('registeredUnregisteredChart'),
            ['Registered', 'Unregistered'],
            [300, 200],
            colors
        );

        // Pie Chart: Verified, Unverified, and Inactive Alumni
        const statusChart = createPieChart(
            document.getElementById('statusChart'),
            ['Verified', 'Unverified', 'Inactive'],
            [150, 250, 100],
            colors
        );

        // Bar Chart: Verified, Unverified, and Inactive Alumni Per Course
        const statusPerCourseChart = createBarChart(
            document.getElementById('statusPerCourseChart'),
            ['BSIT', 'BSCS', 'BSOA', 'BAJ', 'BECED', 'BEED', 'BSBM', 'BSENTREP', 'BSHM', 'BSPsych'],
            [{
                    label: 'Verified',
                    data: [50, 30, 70, 60, 40, 50, 70, 65, 75, 80],
                    backgroundColor: colors.background[0]
                },
                {
                    label: 'Unverified',
                    data: [20, 40, 60, 30, 20, 35, 50, 45, 55, 60],
                    backgroundColor: colors.background[1]
                },
                {
                    label: 'Inactive',
                    data: [10, 50, 40, 20, 30, 25, 35, 30, 40, 50],
                    backgroundColor: colors.background[2]
                }
            ]
        );

        // Bar Chart: Registered Alumni Per Course
        const registeredPerCourseChart = createBarChart(
            document.getElementById('registeredPerCourseChart'),
            ['BSIT', 'BSCS', 'BSOA', 'BAJ', 'BECED', 'BEED', 'BSBM', 'BSENTREP', 'BSHM', 'BSPsych'],
            [{
                label: 'Registered Alumni Per Course',
                data: [300, 400, 200, 350, 300, 400, 450, 425, 475, 500],
                backgroundColor: colors.background[3]
            }]
        );

        // Bar Chart: Events Created Per Month
        const eventsPerMonthChart = createBarChart(
            document.getElementById('eventsPerMonthChart'),
            ['January', 'February', 'March', 'April'],
            [{
                label: 'Events Created',
                data: [5, 10, 8, 6],
                backgroundColor: colors.background[4]
            }]
        );

        // Bar Chart: Alumni Registered Per Month
        const registeredPerMonthChart = createBarChart(
            document.getElementById('registeredPerMonthChart'),
            ['January', 'February', 'March', 'April'],
            [{
                label: 'Registered Alumni Every Month',
                data: [25, 35, 40, 30],
                backgroundColor: colors.background[5]
            }]
        );


        // render init block
        const myChart = new Chart(
            document.getElementById('myChart'),
            config
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