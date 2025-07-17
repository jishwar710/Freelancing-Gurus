<?php
session_start();

// Check if the institute is logged in
if (!isset($_SESSION['institute_id']) || !isset($_SESSION['institute_uname'])) {
    header("Location: InstituteLogin.php");
    exit();
}

$institute_id = $_SESSION['institute_id'];

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "freelance";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch applications for the logged-in institute (only pending applications)
$stmt = $conn->prepare("
    SELECT a.application_id, j.occupation_title, f.flancer_name, f.flancer_email, f.flancer_phone, 
           f.flancer_qualification, f.flancer_uni, a.application_TS, a.status 
    FROM applications a 
    INNER JOIN job_details j ON a.job_id = j.job_id 
    INNER JOIN free_user f ON a.flancer_id = f.flancer_id 
    WHERE j.institute_id = ? AND a.status = 'pending'
");
$stmt->bind_param("i", $institute_id);
$stmt->execute();
$result = $stmt->get_result();

// Mark applications as seen
$update_stmt = $conn->prepare("
    UPDATE applications 
    SET is_new = 0 
    WHERE application_id IN (
        SELECT a.application_id 
        FROM applications a
        INNER JOIN job_details j ON a.job_id = j.job_id
        WHERE j.institute_id = ?
    )
");
$update_stmt->bind_param("i", $institute_id);
$update_stmt->execute();
$update_stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applicants</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-cyan-50 p-0">
    <header class="w-full">
        <div class="mx-auto flex justify-between h-16 bg-white w-full max-w-full">
            <a rel="noopener noreferrer" href="#" aria-label="Back to homepage" class="flex items-center p-2">
                <h1 class="font-serif text-3xl text-sky-700">FreelancingGurus</h1>
            </a>
            <ul class="items-stretch hidden space-x-3 mr-5 md:flex">
                <li class="flex">
                    <a rel="noopener noreferrer" href="../index.php"
                        class="flex items-center px-4 -mb-1 border-b-2 border-transparent hover:border-blue-500 transition duration-300 ease-in-out font-semibold">Home</a>
                </li>
                <li class="flex">
                    <a rel="noopener noreferrer" href="./instituteAccount.php"
                        class="flex items-center px-4 -mb-1 border-b-2 border-transparent hover:border-blue-500 transition duration-300 ease-in-out font-semibold">Dashboard</a>
                </li>
                <li class="flex">
                    <a rel="noopener noreferrer" href="../freelancer/freelancer_logout.php"
                        class="flex items-center px-4 -mb-1 border-b-2 border-transparent hover:border-blue-500 transition duration-300 ease-in-out font-semibold">Logout</a>
                </li>
            </ul>
            <button class="flex justify-end p-4 md:hidden">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="w-6 h-6">
                    <!-- SVG Path -->
                </svg>
            </button>
        </div>
    </header>

    <div class="mt-[5%] ml-[6%]">
        <h3 class="font-semibold text-3xl text-blue-800">Applicants</h3>
    </div>

    <section class="py-1 bg-blueGray-50">
        <div class="w-full xl:w-[95%] mb-12 xl:mb-0 mx-auto mt-2">
            <div class="relative flex flex-col min-w-0 break-words bg-white w-full mb-6 shadow-lg rounded">
                <div class="block w-full overflow-x-auto">
                    <table class="items-center bg-transparent w-full border-collapse" id="applicationsTable">
                        <thead>
                            <tr>
                                <!-- New Occupation Title Column -->
                                <th class="px-6 bg-blueGray-50 text-blueGray-500 align-middle border border-solid border-blueGray-100 py-3 text-xs uppercase border-l-0 border-r-0 whitespace-nowrap font-semibold text-left">Occupation Title</th>
                                <th class="px-6 bg-blueGray-50 text-blueGray-500 align-middle border border-solid border-blueGray-100 py-3 text-xs uppercase border-l-0 border-r-0 whitespace-nowrap font-semibold text-left">Name</th>
                                <th class="px-6 bg-blueGray-50 text-blueGray-500 align-middle border border-solid border-blueGray-100 py-3 text-xs uppercase border-l-0 border-r-0 whitespace-nowrap font-semibold text-left">Email</th>
                                <th class="px-6 bg-blueGray-50 text-blueGray-500 align-middle border border-solid border-blueGray-100 py-3 text-xs uppercase border-l-0 border-r-0 whitespace-nowrap font-semibold text-left">Mobile no.</th>
                                <th class="px-6 bg-blueGray-50 text-blueGray-500 align-middle border border-solid border-blueGray-100 py-3 text-xs uppercase border-l-0 border-r-0 whitespace-nowrap font-semibold text-left">Qualification</th>
                                <th class="px-6 bg-blueGray-50 text-blueGray-500 align-middle border border-solid border-blueGray-100 py-3 text-xs uppercase border-l-0 border-r-0 whitespace-nowrap font-semibold text-left">University</th>
                                <th class="px-6 bg-blueGray-50 text-blueGray-500 align-middle border border-solid border-blueGray-100 py-3 text-xs uppercase border-l-0 border-r-0 whitespace-nowrap font-semibold text-left">Applied On</th>
                                <th class="px-6 bg-blueGray-50 text-blueGray-500 align-middle border border-solid border-blueGray-100 py-3 text-xs uppercase border-l-0 border-r-0 whitespace-nowrap font-semibold text-left">Message</th>
                                <th class="px-6 bg-blueGray-50 text-blueGray-500 align-middle border border-solid border-blueGray-100 py-3 text-xs uppercase border-l-0 border-r-0 whitespace-nowrap font-semibold text-left">Status</th>
                                <th class="px-6 bg-blueGray-50 text-blueGray-500 align-middle border border-solid border-blueGray-100 py-3 text-xs uppercase border-l-0 border-r-0 whitespace-nowrap font-semibold text-left">Actions</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr id="row-<?= $row['application_id'] ?>">
                                        <!-- New Occupation Title Data -->
                                        <td class="border-t-0 px-6 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4">
                                            <?= htmlspecialchars($row['occupation_title']) ?>
                                        </td>
                                        <td class="border-t-0 px-6 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4">
                                            <?= htmlspecialchars($row['flancer_name']) ?>
                                        </td>
                                        <td class="border-t-0 px-6 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4">
                                            <?= htmlspecialchars($row['flancer_email']) ?>
                                        </td>
                                        <td class="border-t-0 px-6 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4">
                                            <?= htmlspecialchars($row['flancer_phone']) ?>
                                        </td>
                                        <td class="border-t-0 px-6 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4">
                                            <?= htmlspecialchars($row['flancer_qualification']) ?>
                                        </td>
                                        <td class="border-t-0 px-6 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4">
                                            <?= htmlspecialchars($row['flancer_uni']) ?>
                                        </td>
                                        <td class="border-t-0 px-6 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4">
                                            <?= date('Y-m-d H:i', strtotime($row['application_TS'])) ?>
                                        </td>
                                        <td class="border-t-0 px-6 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4">
                                            <textarea name="message" class="border-2 w-full p-1" placeholder="Enter your response..." required></textarea>
                                        </td>
                                        <td class="border-t-0 px-6 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4">
                                            <select name="status" class="border-2 p-1 rounded status-select" required>
                                                <option value="pending">Pending</option>
                                                <option value="accepted">Accept</option>
                                                <option value="rejected">Reject</option>
                                            </select>
                                        </td>
                                        <td class="border-t-0 px-6 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4">
                                            <form class="response-form" data-application-id="<?= $row['application_id'] ?>">
                                                <input type="hidden" name="application_id" value="<?= $row['application_id'] ?>">
                                                <button type="submit" class="bg-blue-800 text-white text-xs font-bold uppercase px-3 py-1 rounded">
                                                    Send Response
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10" class="text-center py-4">No pending applications found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.response-form').forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const applicationId = form.dataset.applicationId;
            const statusElement = form.closest('tr').querySelector('.status-select');
            const messageElement = form.closest('tr').querySelector('textarea');
            const status = statusElement.value;
            const message = messageElement.value.trim();

            // Client-side validation
            if (status === 'pending') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Action Required',
                    text: 'Please select either "Accept" or "Reject" from the status dropdown',
                    confirmButtonColor: '#1e40af',
                });
                statusElement.focus();
                return;
            }

            if (message === '') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Message Required',
                    text: 'Please enter a response message for the applicant',
                    confirmButtonColor: '#1e40af',
                });
                messageElement.focus();
                return;
            }

            try {
                const response = await fetch('send_response.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        application_id: applicationId,
                        status: status,
                        message: message
                    })
                });

                const result = await response.json();

                if (result.success) {
                    // Remove the row
                    document.getElementById(`row-${applicationId}`).remove();

                    // Show success message
                    Swal.fire({
                        icon: 'success',
                        title: 'Response Sent!',
                        text: 'Status updated and response recorded.',
                        confirmButtonColor: '#1e40af',
                    });

                    // Check if table is empty
                    if (document.querySelectorAll('#applicationsTable tbody tr').length === 0) {
                        const tbody = document.querySelector('#applicationsTable tbody');
                        tbody.innerHTML = `<tr><td colspan='10' class='text-center py-4'>No pending applications found.</td></tr>`;
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed!',
                        text: result.message || 'Failed to send response.',
                        confirmButtonColor: '#1e40af',
                    });
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'An error occurred while sending the response.',
                    confirmButtonColor: '#1e40af',
                });
                console.error('Error:', error);
            }
        });
    });
});
</script>
</body>
</html>
<?php
$stmt->close();
$conn->close();
?>