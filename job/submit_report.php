<?php
session_start();
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "freelance";

// Create a connection to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    die("Unauthorized access");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jobId = filter_input(INPUT_POST, 'job_id', FILTER_VALIDATE_INT);
    $reason = filter_input(INPUT_POST, 'reason', FILTER_SANITIZE_STRING);
    $details = filter_input(INPUT_POST, 'details', FILTER_SANITIZE_STRING);
    $flancerId = $_SESSION['user_id'];
    $action = "pending";

    if (!$jobId || !$reason || !$details) {
        $_SESSION['error'] = "All fields are required";
        header("Location: ".$_SERVER['HTTP_REFERER']);
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO reports (job_id, flancer_id, report_reason, report_text,action) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $jobId, $flancerId, $reason, $details, $action);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Report submitted successfully";
    } else {
        $_SESSION['error'] = "Error submitting report";
    }

    $stmt->close();
    header("Location: ".$_SERVER['HTTP_REFERER']);
    exit();
}
?>