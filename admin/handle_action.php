<?php
session_start();

// Database connection
$conn = new mysqli('localhost', 'root', '', 'freelance');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve data from the form
    $report_id = $_POST['report_id'];
    $action = $_POST['action'];
    $def_action = "resolved";
   

    // Update the action column in the reports table
    $update_query = "UPDATE reports SET action = ?, usr_res = ? WHERE report_id = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ssi",$def_action, $action, $report_id);

    if ($stmt->execute()) {
        // Set a success message in the session
        $_SESSION['report_message'] = "Action updated successfully.";
    } else {
        // Set an error message in the session
        $_SESSION['report_message'] = "Failed to update action.";
    }

    // Close the statement
    $stmt->close();
}

// Close the database connection
$conn->close();

// Redirect back to the admin panel
header("Location: admin_institute_report.php");
exit();
?>