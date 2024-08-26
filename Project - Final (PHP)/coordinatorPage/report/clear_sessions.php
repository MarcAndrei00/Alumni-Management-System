<?php
session_start();

// Unset specific session variables
unset($_SESSION['course_filter']);
unset($_SESSION['batch_filter']);
unset($_SESSION['status_filter']);
unset($_SESSION['alumni_stat']);

?>
