<?php
session_start();
require "../vendor/autoload.php";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header("Location: FreelancerLogin.php");
    exit();
}

$flancer_id = $_SESSION['user_id'];
$job_id = $_POST['job_id'];

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "freelance";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Check for existing application
$check = $conn->prepare("SELECT * FROM applications WHERE flancer_id = ? AND job_id = ?");
$check->bind_param("ii", $flancer_id, $job_id);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    $_SESSION['message'] = "⚠️ You've already applied to this job!";
    header("Location: Job.php");
    exit();
}

// Insert new application
$stmt = $conn->prepare("INSERT INTO applications (flancer_id, job_id) VALUES (?, ?)");
$stmt->bind_param("ii", $flancer_id, $job_id);
if ($stmt->execute()) {
    // Get job details
    $jobQuery = $conn->prepare("SELECT occupation_title, institute_name FROM job_details WHERE job_id = ?");
    $jobQuery->bind_param("i", $job_id);
    $jobQuery->execute();
    $jobResult = $jobQuery->get_result()->fetch_assoc();
    
    // Get freelancer email
    $userQuery = $conn->prepare("SELECT flancer_email, flancer_name FROM free_user WHERE flancer_id = ?");
    $userQuery->bind_param("i", $flancer_id);
    $userQuery->execute();
    $userResult = $userQuery->get_result()->fetch_assoc();

    // Send confirmation email
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'freelancinggurus0@gmail.com';
        $mail->Password = 'dybh ixsw dxyi vekv';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('freelancinggurus0@gmail.com', 'Freelancing Gurus');
        $mail->addAddress($userResult['flancer_email'], $userResult['flancer_name']);

        $mail->isHTML(true);
        $mail->Subject = 'Job Application Confirmation - ' . $jobResult['job_title'];
        $mail->Body = '
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap");
                .header { background-color: #1e293b; padding: 24px; text-align: center; }
                .content { padding: 32px 24px; font-family: Poppins, sans-serif; }
                .job-card { background: #f8fafc; border-radius: 8px; padding: 20px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class="header">
                <h2 style="color: white; margin: 0;">Freelancing Gurus</h2>
            </div>
            
            <div class="content">
                <h3 style="color: #1e293b; margin-bottom: 24px;">Hello ' . $userResult['flancer_name'] . ',</h3>
                
                <div class="job-card">
                    <p style="margin: 0 0 16px; font-size: 16px;">Your application for the following position has been successfully submitted:</p>
                    <h4 style="margin: 0 0 8px; color: #1e293b; font-size: 18px;">' . $jobResult['occupation_title'] . '</h4>
                    <p style="margin: 0; color: #64748b;">Institute Name: ' . $jobResult['institute_name'] . '</p>
                </div>

                <p style="color: #64748b; line-height: 1.6;">
                    We will notify you when the employer reviews your application.<br>
                    You can track your applications in your dashboard.
                </p>
                
                <div style="text-align: center; margin: 32px 0;">
                    <a href="http://yourdomain.com/dashboard.php" 
                       style="background-color: #1e293b; color: white; padding: 12px 24px;
                              border-radius: 4px; text-decoration: none; display: inline-block;">
                        View Dashboard
                    </a>
                </div>
            </div>
        </body>
        </html>';

        $mail->send();
    } catch (Exception $e) {
        error_log("Email Error: " . $mail->ErrorInfo);
    }

    $_SESSION['message'] = "✅ Application submitted successfully!";
} else {
    $_SESSION['message'] = "❌ Error: Could not submit application";
}

header("Location: Job.php");
exit();
?>