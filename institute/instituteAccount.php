<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['institute_id']) || !isset($_SESSION['institute_email'])) {
    header("Location: InstituteLogin.php"); // Redirect to login page if not logged in
    exit();
}
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "freelance";
$new_applications = 0;
try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    if (!$conn->connect_error) {
        $stmt = $conn->prepare("
            SELECT COUNT(*) AS count 
            FROM applications a
            INNER JOIN job_details j ON a.job_id = j.job_id
            WHERE j.institute_id = ? AND a.is_new = 1
        ");
        $stmt->bind_param("i", $_SESSION['institute_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $new_applications = $row['count'];
        $stmt->close();
    }
    $conn->close();
} catch (Exception $e) {
    // Handle error
}

try {
    $conn = new mysqli($servername, $username, $password, $dbname);
    if (!$conn->connect_error) {
        // Fetch verification status
        $stmt = $conn->prepare("SELECT status FROM institute_details WHERE institute_id = ?");
        $stmt->bind_param("i", $_SESSION['institute_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $status_row = $result->fetch_assoc();
        $is_verified = ($status_row['status'] === 'verified');
        $stmt->close();
    }
    $conn->close();
} catch (Exception $e) {
    // Handle error
}

// Display verification alerts
if (isset($_SESSION['verification_error'])) {
    echo "<script>alert('" . $_SESSION['verification_error'] . "');</script>";
    unset($_SESSION['verification_error']);
}

// Retrieve the institute name from the session
$institute_name = htmlspecialchars($_SESSION['institute_name']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Institute Account</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-cyan-50 p-0 ">

    <header class="w-full  ">
        <div class="mx-auto flex justify-between h-16 bg-white w-full max-w-full">
            <a rel="noopener noreferrer" href="#" aria-label="Back to homepage" class="flex items-center p-2">
                <h1 class="font-serif text-3xl text-sky-700">FreelancingGurus</h1>
            </a>
            <ul class="items-stretch hidden space-x-3 mr-5 md:flex ">
                
            <li class="flex">
                    <a rel="noopener noreferrer" href="./institute_prof.php"
                        class="flex items-center px-4 -mb-1 border-b-2 border-transparent hover:border-blue-500 transition duration-300 ease-in-out font-semibold">My Profile</a>
                </li>
                <li class="flex">
                    <a rel="noopener noreferrer" href="../freelancer/freelancer_logout.php"
                        class="flex items-center px-4 -mb-1 border-b-2 border-transparent hover:border-blue-500 transition duration-300 ease-in-out font-semibold">Log out</a>
                </li>
            </ul>
            <button class="flex justify-end p-4 md:hidden">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    class="w-6 h-6">
                    <!-- SVG Path -->
                </svg>
            </button>
        </div>
    </header>

<div class="max-w-2xl mx-4 sm:max-w-sm md:max-w-sm lg:max-w-sm xl:max-w-sm sm:mx-auto md:mx-auto lg:mx-auto xl:mx-auto mt-2 bg-white shadow-xl rounded-lg text-gray-900">
    <div class="rounded-t-lg h-32 overflow-hidden">
        <img class="object-cover object-top w-full" src='' alt=''>
    </div>
    <div class="mx-auto w-32 h-32 relative -mt-16 border-4 border-white rounded-full overflow-hidden">
        <img class="object-cover object-center h-39" src='freelancerprofile-removebg-preview.png' alt=''>
    </div>
    <div class="text-center mt-2">
        <h2 class="font-semibold mt-4"><?php echo $institute_name; ?></h2>
        <p class="text-gray-500"></p>
    </div>
    <div class="grid grid-cols-1 flex justify-end">
        <div class="flex justify-between">
        <div class="flex justify-center mt-4">
    <img src="../images/search-bar_6188744.png" class="w-[7%] rounded-full">
    <?php if ($is_verified) : ?>
        <h1><a href="institute_job_ad.php" class="text-blue-600 hover:underline">Post Job Advertisement</a></h1>
    <?php else : ?>
        <h1>
            <a href="#" 
               onclick="alert('Your institute is not verified. Please wait for admin approval.'); return false;" 
               class="text-red-600 cursor-not-allowed">
               Post Job Advertisement
            </a>
        </h1>
    <?php endif; ?>
</div>
        </div>
        <div class="flex justify-between">
            <div class="flex justify-center mt-4">
                <img src="../images/email_2058176.png" class="w-[7%] rounded-full">
                <h1><a href="institute_job_list.php">My Job Advertisements</a></h1>
            </div>
        </div>
        <div class="flex justify-between">
    <div class="flex justify-center mt-4 relative">
        <img src="../images/search-bar_6188744.png" class="w-[7%] rounded-full">
        <h1 class="ml-2"><a href="./applicant.php">New Applications</a></h1>
        <?php if ($new_applications > 0): ?>
            <span class="top-0 right-0 -mr-4 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs">
                <?= $new_applications ?>
            </span>
        <?php endif; ?>
    </div>
</div>
        <div class="flex justify-between">
            <div class="flex justify-center mt-4">
                <img src="../images/search-bar_6188744.png" class="w-[7%] rounded-full">
                <h1><a href="./my_response.php">My Responses</a></h1>
            </div>
        </div>
        <div class="flex justify-between">
            <div class="flex justify-center mt-4">
                <img src="../images/search-bar_6188744.png" class="w-[7%] rounded-full">
                <h1><a href="./ins_update.php">Change Password</a></h1>
            </div>
        </div>
        <div class="flex justify-between">
            <div class="flex justify-center mt-4">
                <img src="../images/search-bar_6188744.png" class="w-[7%] rounded-full">
                <h1><a href="../feedback_form.php">Feedback</a></h1>
            </div>
        </div>
    </div>

</div>


</body>
</html>
