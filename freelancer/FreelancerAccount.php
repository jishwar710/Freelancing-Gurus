<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header("Location: FreelancerLogin.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "freelance";

// Get response and new job counts
$new_responses = 0;
$new_jobs = 0;

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Database connection failed");
    }

    // Get new responses count
    $stmt = $conn->prepare("
        SELECT COUNT(*) AS count 
        FROM institute_responses r
        INNER JOIN applications a ON r.application_id = a.application_id
        WHERE a.flancer_id = ? AND r.is_read = 0
    ");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $new_responses = $row['count'];
    $stmt->close();

    // Get new jobs count (MODIFIED TO USE job_TS)
    $stmt = $conn->prepare("
        SELECT COUNT(*) AS count 
        FROM job_details 
        WHERE job_TS > (
            SELECT last_login 
            FROM free_user 
            WHERE flancer_id = ?
        )
    ");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $new_jobs = $row['count'];
    $stmt->close();

    $conn->close();
} catch (Exception $e) {
    // Handle error 
    echo "Error: " . $e->getMessage();
    exit();
}

$freelancer_name = htmlspecialchars($_SESSION['username']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Freelancer Account</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-cyan-50 p-0">
    <header class="w-full">
        <div class="mx-auto flex justify-between h-16 bg-white w-full max-w-full">
            <a rel="noopener noreferrer" href="#" aria-label="Back to homepage" class="flex items-center p-2">
                <h1 class="font-serif text-3xl text-sky-700">FreelancingGurus</h1>
            </a>
            <ul class="items-stretch hidden space-x-3 mr-5 md:flex">
            <li class="flex">
                    <a rel="noopener noreferrer" href="../job/Job.php"
                        class="flex items-center px-4 -mb-1  border-b-2 border-transparent hover:border-blue-500 transition duration-300 ease-in-out font-semibold">Home
                    <?php if ($new_jobs > 0): ?>
                        <span class="top-0 right-0 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs">
                            <?php echo $new_jobs; ?>
                        </span>
                    <?php endif; ?></a>
                </li>
                <li class="flex">
                <a rel="noopener noreferrer" href="./freelancer_logout.php"
                    class="flex items-center px-4 -mb-1 border-b-2 border-transparent hover:border-blue-500 transition duration-300 ease-in-out font-semibold">Logout</a>
            </li>
            </ul>
        </div>
    </header>

    <body>
        <div class="max-w-2xl mx-4 sm:max-w-sm md:max-w-sm lg:max-w-sm xl:max-w-sm sm:mx-auto md:mx-auto lg:mx-auto xl:mx-auto mt-8 bg-white shadow-xl rounded-lg text-gray-900">
            <div class="rounded-t-lg h-32 overflow-hidden">
                <img class="object-cover object-top w-full" src='' alt=''>
            </div>
            <div class="mx-auto w-32 h-32 relative -mt-16 border-4 border-white rounded-full overflow-hidden">
                <img class="object-cover object-center h-39" src='../images/freelancerprofile-removebg-preview.png' alt='Freelancer Profile'>
            </div>
            <div class="text-center mt-2">
                <h2 class="font-semibold mt-4"><?php echo $freelancer_name; ?></h2>
                <p class="text-gray-500"></p>
            </div>
            
            <div class="grid grid-cols-1 flex justify-end">
                  <!-- profile -->
                  <div class="flex justify-between">
                    <div class="flex justify-center mt-4">
                        <img src="../images/search-bar_6188744.png" class="w-[7%] rounded-full">
                        <h1 class="ml-2"><a href="./free_profile.php">My Profile</a></h1>
                    </div>
                </div> 
                <!-- Check Responses with Notification -->
                <div class="flex justify-between">
                    <div class="flex justify-center mt-4 relative">
                        <img src="../images/email_2058176.png" class="w-[7%] rounded-full">
                        <h1 class="ml-2"><a href="./institute_job_response.php">Inbox</a></h1>
                        <?php if ($new_responses > 0): ?>
                            <span class=" top-0 right-0 -mt-2 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs">
                                <?php echo $new_responses; ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="flex justify-between">
                    <div class="flex justify-center mt-4">
                        <img src="../images/search-bar_6188744.png" class="w-[7%] rounded-full">
                        <h1 class="ml-2"><a href="./my_applications.php">My Applications</a></h1>
                    </div>
                </div>

                <div class="flex justify-between">
            <div class="flex justify-center mt-4">
                <img src="../images/search-bar_6188744.png" class="w-[7%] rounded-full">
                <h1><a href="../feedback_form.php">Feedback</a></h1>
            </div>
        </div>
         <!-- Delete Account -->
         <div class="flex justify-between">
                    <div class="flex justify-center mt-4">
                        <img src="../images/search-bar_6188744.png" class="w-[7%] rounded-full">
                        <h1 class="ml-2"><a href="delete_account.php?user_id=<?php echo $_SESSION['user_id']; ?>" onclick="return confirm('Are you sure you want to delete your account? This action cannot be undone.');">Delete Account</a></h1>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html> 