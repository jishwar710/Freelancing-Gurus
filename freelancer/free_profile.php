<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    // Redirect to login page if not logged in
    header("Location: FreelancerLogin.php"); 
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

    // Fetch user data from the database
    $stmt = $conn->prepare("SELECT * FROM free_user WHERE flancer_uname = :username");
    $stmt->bindParam(':username', $_SESSION['username']);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "User not found!";
        exit();
    }
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit();
}
$reg_date=$user['flancer_TS'];
$new_reg= date('m-d-Y',strtotime($reg_date));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Freelancer Profile</title>
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
                        class="flex items-center px-4 -mb-1 border-b-2 border-transparent hover:border-blue-500 transition duration-300 ease-in-out font-semibold">Home</a>
                </li>
                <li class="flex">
                    <a rel="noopener noreferrer" href="./freelancerAccount.php"
                        class="flex items-center px-4 -mb-1 border-b-2 border-transparent hover:border-blue-500 transition duration-300 ease-in-out font-semibold">Dashboard</a>
                </li>
                <li class="flex">
                    <a rel="noopener noreferrer" href="./freelancer_logout.php"
                        class="flex items-center px-4 -mb-1 border-b-2 border-transparent hover:border-blue-500 transition duration-300 ease-in-out font-semibold">Log Out</a>
                </li>
            </ul>
        </div>
    </header>

    <div class="max-w-2xl mx-auto mt-8 bg-white shadow-xl rounded-lg text-gray-900 ">
        <!-- Profile Header -->
        <div class="rounded-t-lg h-32 overflow-hidden">
            <img class="object-cover object-top w-full" src="../images/profile-banner.png" alt="Profile Banner">
        </div>

        <!-- Profile Picture -->
        <div class="mx-auto w-32 h-32 relative -mt-16 border-4 border-white rounded-full overflow-hidden">
            <img class="object-cover object-center h-32 w-32" src="../images/freelancerprofile-removebg-preview.png" alt="Profile Picture">
        </div>

        <!-- Profile Details -->
        <div class="text-center mt-2">
            <h2 class="font-semibold text-xl"><?php echo htmlspecialchars($user['flancer_name']); ?></h2>
            <p class="text-gray-500"><?php echo htmlspecialchars($user['flancer_email']); ?></p>
        </div>

        <!-- Profile Information -->
        <div class="p-6">
            <h3 class="text-lg font-semibold mb-4">Profile Information</h3>
            <div class="space-y-2">
                <p><strong>Username:</strong> <?php echo htmlspecialchars($user['flancer_uname']); ?></p>
                <p><strong>Full Name:</strong> <?php echo htmlspecialchars($user['flancer_name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['flancer_email']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['flancer_phone']); ?></p>
                <p><strong>Qualification:</strong> <?php echo htmlspecialchars($user['flancer_qualification']); ?></p>
                <p><strong>University/Board:</strong> <?php echo htmlspecialchars($user['flancer_uni']); ?></p>
               
                <p><strong>Registered On:</strong> <?php echo $new_reg; ?></p>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="p-4 border-t mx-8 mt-2">
            <button class="w-full block mx-auto rounded-full bg-sky-900 hover:shadow-lg font-semibold text-white px-6 py-2">
                <a href="./free_update.php">Update</a>
            </button>
        </div>  
    </div>
</body>
</html>