<?php
session_start();
$conn = new mysqli("localhost", "root", "", "alumni_management_system");

// EVENT ARCHIVER
$sql = "SELECT * FROM event";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $timezone = new DateTimeZone('Asia/Manila');
    while ($row = $result->fetch_assoc()) {
        $eventDate = new DateTime($row["date"], $timezone);
        $eventTime = new DateTime($row["end_time"], $timezone);

        $currentDateTime = new DateTime('now', $timezone);
        $interval = $currentDateTime->diff($eventDate);
        $formattedDateCreated = $eventDate->format('Y-m-d');

        if (($interval->days >= 1 && $interval->invert == 1) || ($eventDate->format('Y-m-d') == $currentDateTime->format('Y-m-d') && $eventTime <= (clone $currentDateTime))) {
            // Deleting the event from the database
            $event_id = $row['event_id'];

            $sql_archive = "INSERT INTO event_archive (event_id, title, date, start_time, end_time, venue, address, event_for, description, image, going, interested, not_interested, date_created)" .
                " SELECT event_id, title, date, start_time, end_time, venue, address, event_for, description, image, going, interested, not_interested, date_created FROM event WHERE event_id=$event_id";
            $conn->query($sql_archive);

            $sql_delete = "DELETE FROM event WHERE event_id = $event_id";
            $conn->query($sql_delete);
        }
    }
}

// FOR ALUMNI 
$sql = "SELECT * FROM alumni";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $timezone = new DateTimeZone('Asia/Manila');
    while ($row = $result->fetch_assoc()) {
        $eventDatetime = new DateTime($row["last_login"], $timezone);
        $activity = $row["status"];
        $currentDateTime = new DateTime('now', $timezone);

        $interval = $currentDateTime->diff($eventDatetime);
        $formattedDateCreated = $eventDatetime->format('Y-m-d');
        if (($interval->days >= 10 && $interval->invert == 1) && ($activity === 'Verified')) {
            // echo "Status: Inactive for last 10 days or Above (Last login: $formattedDateCreated) <br>";

            $alumni_id = $row["alumni_id"];
            $sql_archive = "INSERT INTO alumni_archive (alumni_id, student_id, fname, mname, lname, gender, course, batch_startYear, batch_endYear, contact, address, email, password, last_login, picture, date_created)" .
                "SELECT alumni_id, student_id, fname, mname, lname, gender, course, batch_startYear, batch_endYear, contact, address, email, password, last_login, picture, date_created FROM alumni WHERE alumni_id=$alumni_id";
            $conn->query($sql_archive);

            $stmt = $conn->prepare("UPDATE alumni_archive SET status = 'Inactive' WHERE alumni_id = ?");
            $stmt->bind_param("s", $alumni_id);
            $stmt->execute();
            $stmt->close();

            //delete data in table alumni
            $sql_delete = "DELETE FROM alumni WHERE alumni_id=$alumni_id";
            $conn->query($sql_delete);
        }
    }
}