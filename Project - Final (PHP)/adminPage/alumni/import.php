<?php
$conn = new mysqli("localhost", "root", "", "alumni_management_system");
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
            echo "<script type=\"text/javascript\">
                    alert(\"Invalid File: Please Upload CSV or Excel File.\");
                    window.location = \"index.php\"
                </script>";
            exit();
        }

        if (($file = fopen($csvFile, "r")) !== FALSE) {

            $insertCount = 0; // Counter for inserted records

            while (($emapData = fgetcsv($file, 10000, ",")) !== FALSE) {

                // Skip header row or any empty row
                if (empty(array_filter($emapData))) continue;

                // Check if student_id already exists
                $checkQuery = "SELECT * FROM list_of_graduate WHERE student_id = '$emapData[1]'";
                $checkResult = mysqli_query($conn, $checkQuery);

                if (mysqli_num_rows($checkResult) > 0) {
                    // Skip inserting if student_id exists
                    continue;
                }

                // Insert data into subject table
                $sql = "INSERT INTO list_of_graduate (`student_id`, `lname`, `fname`, `mname`, `gender`, `course`, `batch`, `contact`, `address`, `email`) 
                                    VALUES ('$emapData[1]', '$emapData[2]', '$emapData[3]', '$emapData[4]', '$emapData[5]', '$emapData[6]', '$emapData[7]', '$emapData[8]', '$emapData[9]', '$emapData[10]')";
                $result = mysqli_query($conn, $sql);
                if (!$result) {
                    echo "<script type=\"text/javascript\">
                            alert(\"Error Importing Data: " . mysqli_error($conn) . "\");
                            window.location = \"index.php\"
                        </script>";
                    exit();
                }

                $insertCount++; // Increment the counter if insertion is successful
            }
            fclose($file);

            if ($insertCount > 0) {
                echo "<script type=\"text/javascript\">
                        alert(\"File has been successfully Imported.\");
                        window.location = \"list_of_graduate.php\"
                    </script>";
            } else {
                echo "<script type=\"text/javascript\">
                        alert(\"Records are up to date.\");
                        window.location = \"list_of_graduate.php\"
                    </script>";
            }

        } else {
            echo "<script type=\"text/javascript\">
                    alert(\"Error Opening File.\");
                    window.location = \"list_of_graduate.php\"
                </script>";
        }

        mysqli_close($conn);
    }
}
?>
