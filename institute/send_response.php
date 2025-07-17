<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['institute_id'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$data = json_decode(file_get_contents('php://input'), true);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "freelance";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Server-side validation
    if (!in_array($data['status'], ['accepted', 'rejected'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid status']);
        exit;
    }
    
    if (empty(trim($data['message']))) {
        echo json_encode(['success' => false, 'message' => 'Message cannot be empty']);
        exit;
    }
}

try {
    // Update application status
    $stmt = $conn->prepare("UPDATE applications SET status = ? WHERE application_id = ?");
    $stmt->bind_param("si", $data['status'], $data['application_id']);
    $stmt->execute();
    
    // Record response (ensure institute_responses table has status column)
    $stmt = $conn->prepare("INSERT INTO institute_responses (application_id, message, status) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $data['application_id'], $data['message'], $data['status']);
    $stmt->execute();

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} finally {
    $conn->close();
}
?>