<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['institute_id']) || !isset($_SESSION['institute_email'])) {
    header("Location: InstituteLogin.php"); // Redirect to login page if not logged in
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

    // Fetch institute data
    $stmt = $conn->prepare("SELECT * FROM institute_details WHERE institute_uname = :username");
    $stmt->bindParam(':username', $_SESSION['institute_uname']);
    $stmt->execute();
    $institute = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$institute) {
        echo "Institute not found!";
        exit();
    }
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
    exit();
}

$reg_date = $institute['institute_TS'];
$formatted_reg = date('m-d-Y', strtotime($reg_date));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Institute Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-cyan-50 p-0">
    <header class="w-full">
        <div class="mx-auto flex justify-between h-16 bg-white w-full max-w-full">
            <a href="#" class="flex items-center p-2">
                <h1 class="font-serif text-3xl text-sky-700">FreelancingGurus</h1>
            </a>
            <ul class="items-stretch hidden space-x-3 mr-5 md:flex">
                <li class="flex">
                    <a href="../job/Job.php"
                        class="flex items-center px-4 -mb-1 border-b-2 border-transparent hover:border-blue-500 transition duration-300 ease-in-out font-semibold">Home</a>
                </li>
                <li class="flex">
                    <a href="./instituteAccount.php"
                        class="flex items-center px-4 -mb-1 border-b-2 border-transparent hover:border-blue-500 transition duration-300 ease-in-out font-semibold">Dashboard</a>
                </li>
                <li class="flex">
                    <a href="../aboutus.html"
                        class="flex items-center px-4 -mb-1 border-b-2 border-transparent hover:border-blue-500 transition duration-300 ease-in-out font-semibold">About Us</a>
                </li>
            </ul>
        </div>
    </header>

    <div class="max-w-2xl mx-auto mt-8 bg-white shadow-xl rounded-lg text-gray-900">
        <!-- Banner -->
        <div class="rounded-t-lg h-32 overflow-hidden">
            <img class="object-cover object-top w-full" src="../p1.jpg" alt="Institute Banner">
        </div>

        <!-- Institute Logo -->
        <div class="mx-auto w-32 h-32 relative -mt-16 border-4 border-white rounded-full overflow-hidden">
            <img class="object-cover object-center h-32 w-32" src="../p2.png" alt="Institute Logo">
        </div>

        <!-- Institute Details -->
        <div class="text-center mt-2">
            <h2 class="font-semibold text-xl"><?php echo htmlspecialchars($institute['institute_name']); ?></h2>
            <p class="text-gray-500"><?php echo htmlspecialchars($institute['institute_email']); ?></p>
        </div>

        <!-- Profile Information -->
        <div class="p-6">
            <h3 class="text-lg font-semibold mb-4">Institute Information</h3>
            <div class="space-y-2">
                <p><strong>Username:</strong> <?php echo htmlspecialchars($institute['institute_uname']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($institute['institute_email']); ?></p>
                <p><strong>Phone:</strong> <?php echo htmlspecialchars($institute['institute_phone']); ?></p>
                <p><strong>Address:</strong> <?php echo htmlspecialchars($institute['institute_adrs']); ?></p>
                <p><strong>Status:</strong> <span class="capitalize"><?php echo htmlspecialchars($institute['status']); ?></span></p>
                <p><strong>Registered On:</strong> <?php echo $formatted_reg; ?></p>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="p-4 border-t mx-8 mt-2">
            <button class="w-full block mx-auto rounded-full bg-gray-900 hover:shadow-lg font-semibold text-white px-6 py-2">
                <a href="./institute_logout.php">Logout</a>
            </button>
        </div>
    </div>
</body>
</html>