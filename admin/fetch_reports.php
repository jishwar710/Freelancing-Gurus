<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_name'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'freelance');
if ($conn->connect_error) {
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];

    if ($action === 'view_reports') {
        // Fetch reports for a specific institute
        $institute_id = $_POST['institute_id'];
        $stmt = $conn->prepare("SELECT r.report_id, r.job_id, r.flancer_id, r.report_reason, r.report_text, r.report_TS, r.action 
                                FROM reports r 
                                JOIN job_details j ON r.job_id = j.job_id 
                                WHERE j.institute_id = ?");
        $stmt->bind_param('i', $institute_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $reports = $result->fetch_all(MYSQLI_ASSOC);

        header('Content-Type: application/json');
        echo json_encode($reports);
        exit();
    }
}

echo json_encode(['error' => 'Invalid request']);
exit();
?>