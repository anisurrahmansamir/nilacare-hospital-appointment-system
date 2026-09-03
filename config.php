<?php
// Database connection settings
// Change these if your XAMPP MySQL setup is different
$host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "hospital_appointment_system";

// Display details for this single-hospital prototype.
$hospitalName = "Nilai Care Medical Centre";
$hospitalTagline = "Trusted care, simpler appointments";
$hospitalLocation = "Nilai, Negeri Sembilan";

$conn = new mysqli($host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
