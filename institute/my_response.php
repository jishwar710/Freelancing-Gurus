<?php
session_start();
if (!isset($_SESSION['institute_id'])) {
    header("Location: InstituteLogin.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "freelance";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Get responses for this institute
// Get all responses for the institute
$stmt = $conn->prepare("
    SELECT r.*, f.flancer_name, j.occupation_title 
    FROM institute_responses r
    JOIN applications a ON r.application_id = a.application_id
    JOIN job_details j ON a.job_id = j.job_id
    JOIN free_user f ON a.flancer_id = f.flancer_id
    WHERE j.institute_id = ?
");
$stmt->bind_param("i", $_SESSION['institute_id']);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Responses</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-cyan-50">
<header class="w-full">
        <div class="mx-auto flex justify-between h-16 bg-white w-full max-w-full">
            <a rel="noopener noreferrer" href="#" aria-label="Back to homepage" class="flex items-center p-2">
                <h1 class="font-serif text-3xl text-sky-700">FreelancingGurus</h1>
            </a>
            <ul class="items-stretch hidden space-x-3 mr-5 md:flex">
            <li class="flex">
                    <a rel="noopener noreferrer" href="./instituteAccount.php"
                        class="flex items-center px-4 -mb-1 border-b-2 border-transparent hover:border-blue-500 transition duration-300 ease-in-out font-semibold">Dashboard</a>
                </li>
                <li class="flex">
                    <a rel="noopener noreferrer" href="./institute_prof.php"
                        class="flex items-center px-4 -mb-1 border-b-2 border-transparent hover:border-blue-500 transition duration-300 ease-in-out font-semibold">My Profile</a>
                </li>
                <li class="flex">
                    <a rel="noopener noreferrer" href="freelancer_logout.php"
                        class="flex items-center px-4 -mb-1 border-b-2 border-transparent hover:border-blue-500 transition duration-300 ease-in-out font-semibold">Logout</a>
                </li>
            </ul>
        </div>
    </header>
    <div class="container mx-auto">
        <h1 class="text-3xl font-bold text-blue-800 mt-10 mb-6">My Responses</h1>
        
        <table class="min-w-full bg-white rounded-lg overflow-hidden">
            <thead class="bg-blue-800 text-white">
                <tr>
                    <th class="px-6 py-3">Freelancer</th>
                    <th class="px-6 py-3">Job Title</th>
                    <th class="px-6 py-3">Message</th>
                    <th class="px-6 py-3">Sent On</th>
                </tr>
            </thead>
            <tbody class="text-gray-700">
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4"><?= htmlspecialchars($row['flancer_name']) ?></td>
                    <td class="px-6 py-4"><?= htmlspecialchars($row['occupation_title']) ?></td>
                    <td class="px-6 py-4"><?= htmlspecialchars($row['message']) ?></td>
                    <td class="px-6 py-4"><?= date('Y-m-d H:i', strtotime($row['response_TS'])) ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>