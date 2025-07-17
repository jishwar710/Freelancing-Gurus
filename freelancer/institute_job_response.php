<?php
session_start();

// Check if FREELANCER is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header("Location: FreelancerLogin.php");
    exit();
}

$freelancer_id = $_SESSION['user_id'];

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "freelance";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Pagination for Responses
$records_per_page = 10;
$stmt_count = $conn->prepare("
    SELECT COUNT(*) 
    FROM institute_responses r
    INNER JOIN applications a ON r.application_id = a.application_id
    WHERE a.flancer_id = ?
");
$stmt_count->bind_param("i", $freelancer_id);
$stmt_count->execute();
$stmt_count->bind_result($total_records);
$stmt_count->fetch();
$stmt_count->close();

$total_pages = ceil($total_records / $records_per_page);
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $records_per_page;

$stmt = $conn->prepare("
    SELECT r.message, r.response_TS, i.institute_name, j.occupation_title, r.status, r.is_read, r.application_id 
    FROM institute_responses r
    INNER JOIN applications a ON r.application_id = a.application_id
    INNER JOIN job_details j ON a.job_id = j.job_id
    INNER JOIN institute_details i ON j.institute_id = i.institute_id
    WHERE a.flancer_id = ?
    LIMIT ?, ?
");
$stmt->bind_param("iii", $freelancer_id, $offset, $records_per_page);
$stmt->execute();
$result = $stmt->get_result();

// Now mark responses as read
$update_stmt = $conn->prepare("
    UPDATE institute_responses r
    JOIN applications a ON r.application_id = a.application_id
    SET r.is_read = 1 
    WHERE a.flancer_id = ?
");
$update_stmt->bind_param("i", $freelancer_id);
$update_stmt->execute();
$update_stmt->close();

// Pagination for Reports
$reports_per_page = 10;
$reports_stmt_count = $conn->prepare("
    SELECT COUNT(*) 
    FROM reports re
    INNER JOIN job_details j ON re.job_id = j.job_id
    INNER JOIN institute_details i ON j.institute_id = i.institute_id
    INNER JOIN free_user f ON re.flancer_id = f.flancer_id
    WHERE re.flancer_id = ?
");
$reports_stmt_count->bind_param("i", $freelancer_id);
$reports_stmt_count->execute();
$reports_stmt_count->bind_result($total_reports);
$reports_stmt_count->fetch();
$reports_stmt_count->close();

$total_reports_pages = ceil($total_reports / $reports_per_page);
$current_reports_page = isset($_GET['reports_page']) ? (int)$_GET['reports_page'] : 1;
$reports_offset = ($current_reports_page - 1) * $reports_per_page;

$reports_stmt = $conn->prepare("
    SELECT 
        re.report_id, 
        re.job_id, 
        re.flancer_id, 
        re.report_reason, 
        re.report_text, 
        re.report_TS,  
        re.usr_res,
        re.action, 
        j.occupation_title,
        i.institute_name,
        f.flancer_uname
    FROM reports re
    INNER JOIN job_details j ON re.job_id = j.job_id
    INNER JOIN institute_details i ON j.institute_id = i.institute_id
    INNER JOIN free_user f ON re.flancer_id = f.flancer_id
    WHERE re.flancer_id = ?
    LIMIT ?, ?
");
$reports_stmt->bind_param("iii", $freelancer_id, $reports_offset, $reports_per_page);
$reports_stmt->execute();
$reports_result = $reports_stmt->get_result();

$stmt->close();
$reports_stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Responses</title>
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
                    <a rel="noopener noreferrer" href="./FreelancerAccount.php"
                        class="flex items-center px-4 -mb-1 border-b-2 border-transparent hover:border-blue-500 transition duration-300 ease-in-out font-semibold">Dashboard</a>
                </li>
                <li class="flex">
                    <a rel="noopener noreferrer" href="freelancer_logout.php"
                        class="flex items-center px-4 -mb-1 border-b-2 border-transparent hover:border-blue-500 transition duration-300 ease-in-out font-semibold">Logout</a>
                </li>
            </ul>
        </div>
    </header>

    <div class="mt-[5%] ml-[7%]">
        <h3 class="font-semibold text-3xl text-blue-800">Responses</h3>
    </div>

    <section class="py-1 bg-blueGray-50">
        <div class="w-full xl:w-[90%] mb-12 xl:mb-0 px-4 mx-auto mt-2">
            <div class="relative flex flex-col min-w-0 break-words bg-white w-full mb-6 shadow-lg rounded">
                <div class="block w-full overflow-x-auto mx-auto">
                    <table class="items-center bg-transparent w-full border-collapse">
                        <thead>
                            <tr>
                                <th class="px-6 bg-blueGray-50 text-blueGray-500 align-middle border border-solid border-blueGray-100 py-3 text-xs uppercase border-l-0 border-r-0 whitespace-nowrap font-semibold text-center">Institute</th>
                                <th class="px-6 bg-blueGray-50 text-blueGray-500 align-middle border border-solid border-blueGray-100 py-3 text-xs uppercase border-l-0 border-r-0 whitespace-nowrap font-semibold text-center">Job Title</th>
                                <th class="px-6 bg-blueGray-50 text-blueGray-500 align-middle border border-solid border-blueGray-100 py-3 text-xs uppercase border-l-0 border-r-0 whitespace-nowrap font-semibold text-center">Message</th>
                                <th class="px-6 bg-blueGray-50 text-blueGray-500 align-middle border border-solid border-blueGray-100 py-3 text-xs uppercase border-l-0 border-r-0 whitespace-nowrap font-semibold text-center">Status</th>
                                <th class="px-6 bg-blueGray-50 text-blueGray-500 align-middle border border-solid border-blueGray-100 py-3 text-xs uppercase border-l-0 border-r-0 whitespace-nowrap font-semibold text-center">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr class="hover:bg-gray-200 <?= $row['is_read'] == 0 ? 'new-response' : '' ?>">
                                        <td class="border-t-0 px-6 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-center">
                                            <?= htmlspecialchars($row['institute_name']) ?>
                                        </td>
                                        <td class="border-t-0 px-6 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-center">
                                            <?= htmlspecialchars($row['occupation_title']) ?>
                                        </td>
                                        <td class="border-t-0 px-6 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-center">
                                            <?= htmlspecialchars($row['message']) ?>
                                        </td>
                                        <td class="border-t-0 px-6 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-center">
                                            <?= htmlspecialchars($row['status']) ?>
                                        </td>
                                        <td class="border-t-0 px-6 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-center">
                                            <?= date('M d, Y', strtotime($row['response_TS'])) ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4">No responses received yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <div class="flex justify-center mt-4">
        <nav class="flex justify-between items-center">
            <a href="?page=<?= $current_page > 1 ? $current_page - 1 : 1 ?>" class="px-3 py-2 text-sm text-blue-500 hover:text-blue-700">Previous</a>
            <span class="text-sm text-gray-700">Page <?= $current_page ?> of <?= $total_pages ?></span>
            <a href="?page=<?= $current_page < $total_pages ? $current_page + 1 : $total_pages ?>" class="px-3 py-2 text-sm text-blue-500 hover:text-blue-700">Next</a>
        </nav>
    </div>

    <div class="mt-[5%] ml-[7%]">
        <h3 class="font-semibold text-3xl text-blue-800">Reports</h3>
    </div>

    <section class="py-1 bg-blueGray-50">
        <div class="w-full xl:w-[90%] mb-12 xl:mb-0 px-4 mx-auto mt-2">
            <div class="relative flex flex-col min-w-0 break-words bg-white w-full mb-6 shadow-lg rounded">
                <div class="block w-full overflow-x-auto mx-auto">
                    <table class="items-center bg-transparent w-full border-collapse">
                        <!-- Updated thead section for Reports -->
<thead>
    <tr>
        <th class="px-6 bg-blueGray-50 text-blueGray-500 align-middle border border-solid border-blueGray-100 py-3 text-xs uppercase border-l-0 border-r-0 whitespace-nowrap font-semibold text-center">Report ID</th>                                
        <th class="px-6 bg-blueGray-50 text-blueGray-500 align-middle border border-solid border-blueGray-100 py-3 text-xs uppercase border-l-0 border-r-0 whitespace-nowrap font-semibold text-center">Report Reason</th>
        <th class="px-6 bg-blueGray-50 text-blueGray-500 align-middle border border-solid border-blueGray-100 py-3 text-xs uppercase border-l-0 border-r-0 whitespace-nowrap font-semibold text-center">Additional details</th>
        <th class="px-6 bg-blueGray-50 text-blueGray-500 align-middle border border-solid border-blueGray-100 py-3 text-xs uppercase border-l-0 border-r-0 whitespace-nowrap font-semibold text-center">Date</th>
        <th class="px-6 bg-blueGray-50 text-blueGray-500 align-middle border border-solid border-blueGray-100 py-3 text-xs uppercase border-l-0 border-r-0 whitespace-nowrap font-semibold text-center">Status</th>
        <th class="px-6 bg-blueGray-50 text-blueGray-500 align-middle border border-solid border-blueGray-100 py-3 text-xs uppercase border-l-0 border-r-0 whitespace-nowrap font-semibold text-center">Action</th>
    </tr>
</thead>

<!-- Updated tbody section for Reports -->
<tbody>
    <?php if ($reports_result->num_rows > 0): ?>
        <?php while ($report = $reports_result->fetch_assoc()): ?>
            <tr class="hover:bg-gray-200">
                <td class="border-t-0 px-6 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-center">
                    <?= htmlspecialchars($report['report_id']) ?>
                </td>
                <td class="border-t-0 px-6 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-center">
                    <?= htmlspecialchars($report['report_reason']) ?>
                </td>
                <td class="border-t-0 px-6 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-center">
                    <?= htmlspecialchars($report['report_text']) ?>
                </td>                                       
                <td class="border-t-0 px-6 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-center">
                    <?= date('M d, Y', strtotime($report['report_TS'])) ?>
                </td>
                <td class="border-t-0 px-6 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-center">
                    <?= htmlspecialchars($report['action']) ?>
                </td>
                <td class="border-t-0 px-6 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-center">
                    <?= htmlspecialchars($report['usr_res']) ?>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="6" class="text-center py-4">No reports available.</td>
        </tr>
    <?php endif; ?>
</tbody> 
                    </table>
                </div>
            </div>
        </div>
    </section>

    <div class="flex justify-center mt-4">
        <nav class="flex justify-between items-center">
            <a href="?page=<?= $current_page ?>&reports_page=<?= $current_reports_page > 1 ? $current_reports_page - 1 : 1 ?>" class="px-3 py-2 text-sm text-blue-500 hover:text-blue-700">Previous</a>
            <span class="text-sm text-gray-700">Page <?= $current_reports_page ?> of <?= $total_reports_pages ?></span>
            <a href="?page=<?= $current_page ?>&reports_page=<?= $current_reports_page < $total_reports_pages ? $current_reports_page + 1 : $total_reports_pages ?>" class="px-3 py-2 text-sm text-blue-500 hover:text-blue-700">Next</a>
        </nav>
    </div>
    <script>
        // Gradually remove highlight after 3 seconds
        document.addEventListener('DOMContentLoaded', () => {
            const newRows = document.querySelectorAll('.new-response');
            newRows.forEach(row => {
                setTimeout(() => {
                    row.style.backgroundColor = '';
                }, 3000);
            });
        });
    </script>
</body>
</html>