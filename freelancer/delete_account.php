<?php
session_start();

// For debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log access to this file
file_put_contents('delete_log.txt', date('Y-m-d H:i:s') . " - Delete account accessed\n", FILE_APPEND);

// Check if the user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    file_put_contents('delete_log.txt', date('Y-m-d H:i:s') . " - Not logged in\n", FILE_APPEND);
    header("Location: FreelancerLogin.php"); // Redirect to login page if not logged in
    exit();
}

// Check if the user_id parameter is provided
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
    file_put_contents('delete_log.txt', date('Y-m-d H:i:s') . " - User ID: " . $user_id . "\n", FILE_APPEND);
    
    // Verify that the user_id from the URL matches the logged-in user's ID
    if ($user_id != $_SESSION['user_id']) {
        // Security check failed
        file_put_contents('delete_log.txt', date('Y-m-d H:i:s') . " - Security check failed. Session user ID: " . $_SESSION['user_id'] . "\n", FILE_APPEND);
        $_SESSION['error'] = "Security verification failed. Please try again.";
        header("Location: free_profile.php");
        exit();
    }
    
    // Database connection
    $host = "localhost";
    $dbname = "freelance";
    $username = "root";
    $password = "";
    
    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        file_put_contents('delete_log.txt', date('Y-m-d H:i:s') . " - Database connected\n", FILE_APPEND);
        
        // Begin transaction for data integrity
        $conn->beginTransaction();
        
        // First, get all application IDs for this user
        $stmt = $conn->prepare("SELECT application_id FROM applications WHERE flancer_id = :user_id");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $applications = $stmt->fetchAll(PDO::FETCH_COLUMN);
        file_put_contents('delete_log.txt', date('Y-m-d H:i:s') . " - Found " . count($applications) . " applications\n", FILE_APPEND);
        
        // If there are any applications, delete related responses first
        if (!empty($applications)) {
            $placeholders = implode(',', array_fill(0, count($applications), '?'));
            $stmt = $conn->prepare("DELETE FROM institute_responses WHERE application_id IN ($placeholders)");
            foreach ($applications as $index => $app_id) {
                $stmt->bindValue($index + 1, $app_id);
            }
            $stmt->execute();
            $respCount = $stmt->rowCount();
            file_put_contents('delete_log.txt', date('Y-m-d H:i:s') . " - Deleted " . $respCount . " responses\n", FILE_APPEND);
        }
        
        // Now delete the applications
        $stmt = $conn->prepare("DELETE FROM applications WHERE flancer_id = :user_id");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $appCount = $stmt->rowCount();
        file_put_contents('delete_log.txt', date('Y-m-d H:i:s') . " - Deleted " . $appCount . " applications\n", FILE_APPEND);
        
        // Delete any reports made by this user
        $stmt = $conn->prepare("DELETE FROM reports WHERE flancer_id = :user_id");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $reportCount = $stmt->rowCount();
        file_put_contents('delete_log.txt', date('Y-m-d H:i:s') . " - Deleted " . $reportCount . " reports\n", FILE_APPEND);
        
        // Check for feedback entries and anonymize them
        $stmt = $conn->prepare("UPDATE feedback SET username = 'Deleted User', email = '' WHERE username = (SELECT flancer_uname FROM free_user WHERE flancer_id = :user_id)");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $feedbackCount = $stmt->rowCount();
        file_put_contents('delete_log.txt', date('Y-m-d H:i:s') . " - Anonymized " . $feedbackCount . " feedback entries\n", FILE_APPEND);
        
        // Finally, delete the user account
        $stmt = $conn->prepare("DELETE FROM free_user WHERE flancer_id = :user_id");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->execute();
        $userDeleted = $stmt->rowCount();
        file_put_contents('delete_log.txt', date('Y-m-d H:i:s') . " - Deleted user account: " . ($userDeleted ? "Yes" : "No") . "\n", FILE_APPEND);
        
        // Commit the transaction
        $conn->commit();
        file_put_contents('delete_log.txt', date('Y-m-d H:i:s') . " - Transaction committed\n", FILE_APPEND);
        
        // Clear session and redirect to login page with success message
        session_unset();
        session_destroy();
        
        // Redirect with success message
        file_put_contents('delete_log.txt', date('Y-m-d H:i:s') . " - Redirecting to login page with success message\n", FILE_APPEND);
        header("Location: FreelancerLogin.php?deleted=success");
        exit();
        
    } catch (PDOException $e) {
        // Rollback the transaction if something failed
        $conn->rollBack();
        
        // Log the error
        file_put_contents('delete_log.txt', date('Y-m-d H:i:s') . " - ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
        
        // Set error message and redirect back to profile
        $_SESSION['error'] = "Error deleting account: " . $e->getMessage();
        header("Location: free_profile.php");
        exit();
    }
} else {
    // If someone tries to access this page directly without the user_id parameter
    file_put_contents('delete_log.txt', date('Y-m-d H:i:s') . " - No user_id parameter provided\n", FILE_APPEND);
    header("Location: free_profile.php");
    exit();
}
?>
