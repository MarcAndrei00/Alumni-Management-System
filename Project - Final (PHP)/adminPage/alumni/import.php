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


require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

function convertToCsv($inputFile, $outputFile)
{
    // Determine the file type
    $inputFileType = IOFactory::identify($inputFile);

    // Create a reader for the file type
    $reader = IOFactory::createReader($inputFileType);

    // Load the spreadsheet
    $spreadsheet = $reader->load($inputFile);

    // Create a CSV writer
    $writer = IOFactory::createWriter($spreadsheet, 'Csv');

    // Save the file as CSV
    $writer->save($outputFile);
}

if (isset($_POST["Import"])) {

    $filename = $_FILES["file"]["tmp_name"];
    $fileSize = $_FILES["file"]["size"];

    if ($fileSize > 0) {

        $extension = pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);

        if ($extension == 'csv') {
            // Handle CSV files directly
            $csvFile = $filename;
        } elseif ($extension == 'xlsx' || $extension == 'xls') {
            // Convert Excel files to CSV
            $csvFile = $filename . '.csv';
            convertToCsv($filename, $csvFile);
        } else {
            // Invalid File: Please Upload CSV or Excel File.
            $_SESSION['import'] = 'invalid';
            header("Location: ./list_of_graduate.php");
            exit;
        }

        if (($file = fopen($csvFile, "r")) !== FALSE) {

            $insertCount = 0; // Counter for inserted records
            $startImporting = false; // Flag to start importing data

            while (($emapData = fgetcsv($file, 10000, ",")) !== FALSE) {

                // Check if the first column has the value "1" to start importing
                if (trim($emapData[0]) == "1") {
                    $startImporting = true;
                }

                // Skip rows until we find the row with "No. 1"
                if (!$startImporting) {
                    continue;
                }

                $student_id = $emapData[1];
                $email = $emapData[10];

                // Check if student_id or email already exists in any of the tables
                $emailCheck = mysqli_query($conn, "SELECT * FROM alumni WHERE email='$email'");
                $emailCheck_archive = mysqli_query($conn, "SELECT * FROM alumni_archive WHERE email='$email'");
                $idCheck = mysqli_query($conn, "SELECT * FROM alumni WHERE student_id='$student_id'");
                $idCheck_archive = mysqli_query($conn, "SELECT * FROM alumni_archive WHERE student_id='$student_id'");
                $gradCheck = mysqli_query($conn, "SELECT * FROM list_of_graduate WHERE student_id='$student_id' OR email='$email'");

                if (mysqli_num_rows($emailCheck) > 0 || mysqli_num_rows($emailCheck_archive) > 0 || mysqli_num_rows($idCheck) > 0 || mysqli_num_rows($idCheck_archive) > 0 || mysqli_num_rows($gradCheck) > 0) {
                    // Skip inserting if student_id or email exists in any of the tables
                    continue;
                }

                // Insert data into list_of_graduate table
                $sql = "INSERT INTO list_of_graduate (`student_id`, `lname`, `fname`, `mname`, `gender`, `course`, `batch`, `contact`, `address`, `email`) 
                                    VALUES ('$emapData[1]', '$emapData[2]', '$emapData[3]', '$emapData[4]', '$emapData[5]', '$emapData[6]', '$emapData[7]', '$emapData[8]', '$emapData[9]', '$emapData[10]')";
                $result = mysqli_query($conn, $sql);
                if (!$result) {
                    // Error Importing Data:
                    $_SESSION['import'] = 'errorImporting';
                    header("Location: ./list_of_graduate.php");
                    exit;
                }

                $insertCount++; // Increment the counter if insertion is successful
            }
            fclose($file);

            if ($insertCount > 0) {
                // File has been successfully Imported.
                $_SESSION['import'] = 'import';
                header("Location: ./list_of_graduate.php");
                exit();
            } else {
                // Records are up to date.
                $_SESSION['import'] = 'uptodate';
                header("Location: ./list_of_graduate.php");
                exit();
            }
        } else {
            // Error Opening File
            $_SESSION['import'] = 'errorOpening';
            header("Location: ./list_of_graduate.php");
        }

        mysqli_close($conn);
    }
}
