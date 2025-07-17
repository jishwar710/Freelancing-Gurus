<?php
// Start the session
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header("Location: FreelancerLogin.php"); // Redirect to login page if not logged in
    exit();
}

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "freelance";

// Create a connection to the database
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user's applied job IDs
$userId = $_SESSION['user_id'];
$appliedJobIds = []; // Initialize as empty array

// Get new responses count
$new_responses = 0;
$responseStmt = $conn->prepare("
    SELECT COUNT(*) AS count 
    FROM institute_responses r
    INNER JOIN applications a ON r.application_id = a.application_id
    WHERE a.flancer_id = ? AND r.is_read = 0
");
$responseStmt->bind_param("i", $userId);
$responseStmt->execute();
$responseResult = $responseStmt->get_result();
$responseRow = $responseResult->fetch_assoc();
$new_responses = $responseRow['count'];
$responseStmt->close();

$appliedStmt = $conn->prepare("SELECT job_id, application_TS FROM applications WHERE flancer_id = ?");
$appliedStmt->bind_param("i", $userId);
$appliedStmt->execute();
$appliedResult = $appliedStmt->get_result();

// Single loop to populate associative array
while ($appliedRow = $appliedResult->fetch_assoc()) {
    $appliedJobIds[$appliedRow['job_id']] = $appliedRow['application_TS'];
}

$appliedStmt->close();

$query = "
    SELECT j.*, 
           i.institute_name,
           i.institute_adrs,
           i.institute_email,
           i.institute_phone
    FROM job_details j
    INNER JOIN institute_details i 
        ON j.institute_id = i.institute_id 
    WHERE i.status = 'verified'
    AND i.account_status = 'active'
";
$result = $conn->query($query);

// Get search and sort parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Define sort options
$sortOptions = [
    'newest' => 'j.job_TS DESC',
    'oldest' => 'j.job_TS ASC',
    'title_asc' => 'j.occupation_title ASC',
    'title_desc' => 'j.occupation_title DESC',
    'salary_high' => 'CAST(j.salary AS UNSIGNED) DESC',
    'salary_low' => 'CAST(j.salary AS UNSIGNED) ASC',
    'status_active' => 'j.status = "active" DESC',
    'status_deleted' => 'j.status = "deleted" DESC',
    'status_removed' => 'j.status = "removed" DESC'
];

// Build base query
$query = "
    SELECT j.*, 
           i.institute_name,
           i.institute_adrs,
           i.institute_email,
           i.institute_phone
    FROM job_details j
    INNER JOIN institute_details i 
        ON j.institute_id = i.institute_id 
    WHERE i.status = 'verified'
    AND i.account_status = 'active'
    AND j.status = 'active' -- Default to active jobs only
";

// Add search conditions
if (!empty($search)) {
    $query .= " AND (j.occupation_title LIKE ? OR j.job_description LIKE ? OR i.institute_name LIKE ?)";
}

// Add sorting
$query .= " ORDER BY " . $sortOptions[$sort];

// Prepare and execute query
$stmt = $conn->prepare($query);

if (!empty($search)) {
    $searchTerm = "%$search%";
    $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Advertisements</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Add these script tags in the <head> section -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
     <style>
/* Custom scrollbar for modal */
::-webkit-scrollbar {
    width: 8px;
}
::-webkit-scrollbar-track {
    background: #f1f1f1;
}
::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}
::-webkit-scrollbar-thumb:hover {
    background: #555;
}
/* Add to your existing styles */
.flex.gap-4.justify-end {
    gap: 1rem;
    align-items: center;
}
</style>
</head>
<body class="bg-cyan-50 p-0 font-serif text-sky-900">
<?php
if (isset($_SESSION['message'])) {
    $alertType = (strpos($_SESSION['message'], 'already') !== false) ? 'bg-red-100 border-red-400 text-red-700' : 'bg-green-100 border-green-400 text-green-700';
?>
    <div class="<?= $alertType ?> border px-4 py-3 rounded relative fixed top-0 left-0 w-full z-50 text-center" role="alert">
        <span class="block sm:inline"><?= $_SESSION['message'] ?></span>
    </div>
<?php
    unset($_SESSION['message']);
}
?>

    <header class="w-full">
        <div class="mx-auto flex justify-between h-16 bg-white w-full max-w-full">
            <a rel="noopener noreferrer" href="#" aria-label="Back to homepage" class="flex items-center p-2">
              <h1 class="font-serif text-3xl text-sky-700">FreelancingGurus</h1>
            </a>
            <ul class="items-stretch hidden space-x-3 mr-5 md:flex">
                 
            <li class="flex">
                    <a rel="noopener noreferrer" href="../freelancer/FreelancerAccount.php" class="flex items-center px-4 -mb-1 border-b-2 hover:border-blue-500 transition duration-300 ease-in-out">Dashboard
                    <?php if ($new_responses > 0): ?>
                        <span class="top-0 right-0 bg-red-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs">
                            <?php echo $new_responses; ?>
                        </span>
                    <?php endif; ?>
                    </a>
                </li>
                
                <li class="flex">
                    <a rel="noopener noreferrer" href="../freelancer/freelancer_logout.php" class="flex items-center px-4 -mb-1 border-b-2 hover:border-blue-500 transition duration-300 ease-in-out">Log out</a>
                </li>
            </ul>
        </div>
    </header>

    <div class="mt-[5%] ml-[7%]">
        <h3 class="font-semibold text-3xl text-blue-800">Job Advertisement Details</h3>
    </div>
    
    <!-- Search and Sort Section -->
    <div class="w-[90%] mx-auto mt-4 mb-6 flex flex-wrap gap-4 items-center ml-[7%]">
        <form method="GET" class="flex gap-4 items-center">
            <!-- Search Input -->
            <div class="relative">
                <input type="text" name="search" placeholder="Search jobs..." 
                       value="<?= htmlspecialchars($search) ?>"
                       class="pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <svg class="w-5 h-5 absolute left-3 top-2.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>

            <!-- Sort Dropdown -->
            <select name="sort" onchange="this.form.submit()" 
            class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Sort by: Newest First</option>
            <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>Sort by: Oldest First</option>
            <option value="title_asc" <?= $sort === 'title_asc' ? 'selected' : '' ?>>Sort by: Title (A-Z)</option>
            <option value="title_desc" <?= $sort === 'title_desc' ? 'selected' : '' ?>>Sort by: Title (Z-A)</option>
           
            </select>

            <!-- Reset Button -->
            <?php if (!empty($search) || $sort !== 'newest'): ?>
                <a href="?" class="px-4 py-2 text-gray-600 hover:text-blue-800 transition-colors">
                    Clear Filters
                </a>
            <?php endif; ?>
        </form>
    </div>

    <section class="py-1 bg-blueGray-50">
        <div class="w-full xl:w-[90%] mb-12 xl:mb-0 px-4 mx-auto mt-2">
            <div class="relative flex flex-col min-w-0 break-words bg-white w-full mb-6 shadow-lg rounded">
                
                <div class="block w-full overflow-x-auto">
                    <table class="items-center bg-transparent w-full border-collapse">
                        <thead>
                            <tr>
                                <th class="px-6 bg-blueGray-50 text-blueGray-500 align-middle border border-solid border-blueGray-100 py-3 text-xs uppercase border-l-0 border-r-0 whitespace-nowrap font-semibold text-left">Occupation Title</th>
                                <th class=" bg-blueGray-50 text-blueGray-500 align-middle border border-solid border-blueGray-100 py-3 text-xs uppercase border-l-0 border-r-0 whitespace-nowrap font-semibold text-left">Experience Required</th>
                                <th class="bg-blueGray-50 text-blueGray-500 align-middle border border-solid border-blueGray-100 py-3 text-xs uppercase border-l-0 border-r-0 whitespace-nowrap font-semibold text-left">Job Description</th>
                                <th class=" bg-blueGray-50 text-blueGray-500 align-middle border border-solid border-blueGray-100 py-3 text-xs uppercase border-l-0 border-r-0 whitespace-nowrap font-semibold text-left">Vacancy Available</th>    
                                <th class=" bg-blueGray-50 text-blueGray-500 align-middle border border-solid border-blueGray-100 py-3 text-xs uppercase border-l-0 border-r-0 whitespace-nowrap font-semibold text-left">Institute Name</th>
                                <th class="bg-blueGray-50 text-blueGray-500 align-middle border border-solid border-blueGray-100 py-3 text-xs uppercase border-l-0 border-r-0 whitespace-nowrap font-semibold text-left">Posted On</th>
                                <th class="bg-blueGray-50 text-blueGray-500 align-middle border border-solid border-blueGray-100 py-3 text-xs uppercase border-l-0 border-r-0 whitespace-nowrap font-semibold text-left">Status</th>
                                <th class="bg-blueGray-50 text-blueGray-500 align-middle border border-solid border-blueGray-100 py-3 text-xs uppercase border-l-0 border-r-0 whitespace-nowrap font-semibold text-left">Actions</th>
                                </tr>
                            </thead>

                            <tbody>
    <?php
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $jobId = $row['job_id'];
            $isApplied = isset($appliedJobIds[$jobId]);
            $row['is_applied'] = $isApplied;
            if ($isApplied) {
                $row['applied_date'] = $appliedJobIds[$jobId];
            }
            $jsonDetails = htmlspecialchars(json_encode($row), ENT_QUOTES, 'UTF-8');
            
            echo "<tr data-details='{$jsonDetails}' class='cursor-pointer hover:bg-blue-50 transition-colors'>";
            echo "<td class='border-t-0 px-6 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left text-blueGray-700'>" . htmlspecialchars($row['occupation_title']) . "</td>";
            echo "<td class='border-t-0 px-6 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4'>" . htmlspecialchars($row['experience_required']) . "</td>";
            echo "<td class='border-t-0 px-6 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left text-blueGray-700'>" . htmlspecialchars($row['job_description']) . "</td>";
            echo "<td class='border-t-0 px-6 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4'>" . htmlspecialchars($row['vacancy_available']) . "</td>";
            echo "<td class='border-t-0 px-6 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4'>" . htmlspecialchars($row['institute_name']) . "</td>";
            echo "<td class='border-t-0 px-6 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4'>" . date('Y-m-d ', strtotime($row['job_TS'])) . "</td>";
            $statusColor = ($row['status'] === 'active') ? 'text-green-600' : 'text-red-600';
            echo "<td class='border-t-0 px-6 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 $statusColor'>" . htmlspecialchars($row['status']) . "</td>";
            echo "<td class='border-t-0 px-6 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4'>";
            
            echo "<div class='flex items-center gap-2'>";
            echo "<form method='post' action='apply.php' class='m-0'>";
            echo "<input type='hidden' name='job_id' value='" . $row['job_id'] . "'>";
            
            if ($isApplied || $row['status'] === 'deleted' || $row['status'] === 'removed') {
                $buttonText = $isApplied ? 'Applied' : 'N/A';
                echo "<button class='bg-gray-400 text-white text-xs font-bold uppercase px-3 py-1 rounded outline-none cursor-not-allowed mr-1 mb-1' disabled>{$buttonText}</button>";
            } else {
                echo "<button class='bg-blue-800 text-white active:bg-indigo-600 text-xs font-bold uppercase px-3 py-1 rounded outline-none focus:outline-none mr-1 mb-1 ease-linear transition-all duration-150' type='submit'>Apply</button>";
            }
            echo "</form>"; 
            echo "</div>";
            echo "</td>";
            echo "</tr>"; 
        }
    } else {
        echo "<tr><td colspan='8' class='text-center py-4'>No job advertisements found.</td></tr>";
    }
    $conn->close();
    ?>
</tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>  

   <!-- Details Modal -->
<div id="detailsModal" class="hidden fixed inset-0 bg-black bg-opacity-50  z-50">
    <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-2">
        <div class="flex justify-between items-start mb-4">
        <h3 class="text-2xl font-bold text-blue-800">Job Details</h3>
                <button id="closeModal" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <div class="space-y-4 max-h-[70vh] overflow-y-auto pr-2">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="mb-4">
                <p class="font-semibold">Job Title:</p>
                    <p id="modalJobTitle"></p>
                </div>
                <div>
                    <p class="font-semibold">Job Description:</p>
                    <p id="modalDescription" class="text-gray-600 whitespace-pre-line"></p>
                </div>
                
                    <div>
                        <p class="font-semibold">Experience Required:</p>
                        <p id="modalExperience" class="text-gray-600"></p>
                    </div>
                    <div>
                        <p class="font-semibold">Vacancy Available:</p>
                        <p id="modalVacancy" class="text-gray-600"></p>
                    </div>
                    <div>
                    <p class="font-semibold">Skills Required:</p>
                    <p id="modalSkills" class="text-gray-600"></p>
                </div>
                    <div>
                        <p class="font-semibold">Posted On:</p>
                        <p id="modalPosted" class="text-gray-600"></p>
                    </div>
                    <div>
                    <p class="font-semibold">Status:</p>
                    <p id="modalStatus" class=""> </p>
                </div>
                <div>
    <p class="font-semibold">Duration(in months):</p>
    <p id="modalDuration" class="text-gray-600"></p>
</div>
                </div>
    
                
                
                

                <div class="border-t pt-4 mt-4">
                    <h4 class="text-lg font-bold text-blue-800 mb-3">Institute Details</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="font-semibold">Name:</p>
                            <p id="modalInstitute" class="text-gray-600"></p>
                        </div>
                        <div>
                            <p class="font-semibold">Address:</p>
                            <p id="instituteAddress" class="text-gray-600"></p>
                        </div>
                        <div>
                            <p class="font-semibold">Contact Number:</p>
                            <p id="instituteContact" class="text-gray-600"></p>
                        </div>
                        <div>
                            <p class="font-semibold">Email:</p>
                            <p id="instituteEmail" class="text-gray-600"></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-6 text-right">
    <div class="flex justify-end gap-4">
        <button id="modalDownloadBtn" onclick="downloadPDF(window.currentDetails); event.stopPropagation()" 
            class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition-colors">
            Download
        </button>
        <form method="post" action="apply.php" id="modalApplyForm">
            <input type="hidden" name="job_id" id="modalJobId" value="">
            <button id="modalApplyButton" 
                    class="px-6 py-2 text-white rounded-md transition-colors" 
                    type="submit">
                Apply Now
            </button>
        </form>
        <button onclick="showReportModalFromDetailsModal()" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 transition-colors">
            Report
        </button>
    </div>
</div>
    </div>
</div>

<!-- Report Modal -->
<div id="reportModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50">
    <div class="relative top-10 mx-auto p-5 border w-11/12 md:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-2">
            <div class="flex justify-between items-start mb-4">
                <h3 class="text-2xl font-bold text-blue-800">Report Job Posting</h3>
                <button onclick="closeReportModal()" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form id="reportForm" action="submit_report.php" method="POST">
                <input type="hidden" name="job_id" id="reportJobId">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Reason for reporting</label>
                        <select name="reason" required class="mt-1 block w-full rounded-md border border-gray-300 p-2">
                            <option value="">Select a reason</option>
                            <option value="spam">Spam or misleading</option>
                            <option value="fraud">Suspected fraud</option>
                            <option value="inappropriate">Inappropriate content</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Additional details</label>
                        <textarea name="details" rows="4" required
                            class="mt-1 block w-full rounded-md border border-gray-300 p-2"
                            placeholder="Please provide more information about your report"></textarea>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-4">
                    <button type="button" onclick="closeReportModal()"
                        class="px-4 py-2 text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-6 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                        Submit Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>

function showReportModal(jobId) {
    document.getElementById('reportJobId').value = jobId;
    document.getElementById('reportModal').classList.remove('hidden');
}
function showReportModalFromDetailsModal() {
    // Get the current job details from the modal
    const details = window.currentDetails;
    document.getElementById('reportJobId').value = details.job_id;

    // Show the Report Modal
    document.getElementById('reportModal').classList.remove('hidden');
}
function closeReportModal() {
    document.getElementById('reportModal').classList.add('hidden');
    document.getElementById('reportForm').reset();
}

// Close modal when clicking outside
document.getElementById('reportModal').addEventListener('click', (e) => {
    if (e.target === document.getElementById('reportModal')) {
        closeReportModal();
    }
});

document.addEventListener('DOMContentLoaded', () => {
    // Handle row clicks
    document.querySelectorAll('tbody tr').forEach(row => {
        row.addEventListener('click', (e) => {
            if (e.target.closest('button') || e.target.closest('form')) return;
            const details = JSON.parse(row.dataset.details);
            showModal(details);
        });
    });

    // Handle modal close
    document.getElementById('closeModal').addEventListener('click', () => {
        document.getElementById('detailsModal').classList.add('hidden');
    });

    // Close modal when clicking outside
    document.getElementById('detailsModal').addEventListener('click', (e) => {
        if (e.target === document.getElementById('detailsModal')) {
            document.getElementById('detailsModal').classList.add('hidden');
        }
    });

    // Alert timeout
    const alert = document.querySelector('[role="alert"]');
    if (alert) {
        setTimeout(() => {
            alert.style.transition = 'opacity 1s ease-out';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 1000);
        }, 3000);
    }
});

function showModal(details) {
    // Job Details
    window.currentDetails = details;
    document.getElementById('modalJobTitle').textContent = details.occupation_title;
    document.getElementById('modalExperience').textContent = details.experience_required;
    document.getElementById('modalDescription').textContent = details.job_description;
    document.getElementById('modalVacancy').textContent = details.vacancy_available;
    document.getElementById('modalSkills').textContent = details.skill_required;
    document.getElementById('modalDuration').textContent = details.duration;
    document.getElementById('modalPosted').textContent = new Date(details.job_TS).toLocaleDateString();
    const statusElement = document.getElementById('modalStatus');
    statusElement.textContent = details.status;
    statusElement.className = details.status === 'active' ? 'text-green-600' : 'text-red-600';
    
    // Institute Details
    document.getElementById('modalInstitute').textContent = details.institute_name;
    document.getElementById('instituteAddress').textContent = details.institute_adrs;
    document.getElementById('instituteContact').textContent = details.institute_phone;
    document.getElementById('instituteEmail').textContent = details.institute_email;

    // Apply Button
    const applyButton = document.getElementById('modalApplyButton');
    document.getElementById('modalJobId').value = details.job_id;
    
    if (details.is_applied || details.status === 'deleted' || details.status === 'removed') {
        applyButton.disabled = true;
        applyButton.textContent = details.is_applied ? 'Applied' : 'Not Available';
        applyButton.classList.add('bg-gray-400', 'cursor-not-allowed');
        applyButton.classList.remove('bg-blue-800', 'hover:bg-blue-700');
    } else {
        applyButton.disabled = false;
        applyButton.textContent = 'Apply Now';
        applyButton.classList.add('bg-blue-800', 'hover:bg-blue-700');
        applyButton.classList.remove('bg-gray-400', 'cursor-not-allowed');
    }

    const downloadBtn = document.getElementById('modalDownloadBtn');
    if (details.is_applied) {
        downloadBtn.style.display = 'inline-block';
    } else {
        downloadBtn.style.display = 'none';
    }
    
    document.getElementById('detailsModal').classList.remove('hidden');
}

//pdf download 
// Add event listeners for download buttons
document.querySelectorAll('.download-btn').forEach(button => {
    button.addEventListener('click', function(e) {
        e.stopPropagation();
        const details = JSON.parse(this.dataset.details);
        downloadPDF(details);
    });
});

// Modified downloadPDF function
function downloadPDF(details) {
    try {
    const doc = new jspdf.jsPDF();
    const pageWidth = doc.internal.pageSize.width;
    
    // Main header
    doc.setFontSize(22);
    doc.setFont(undefined, 'bold');
    doc.text('FreelancingGurus', pageWidth / 2, 15, { align: 'center' });
    
    // Subtitle
    doc.setFontSize(16);
    doc.text("Job Application Details", pageWidth / 2, 25, { align: 'center' });
    
    // Add decorative line
    doc.setLineWidth(0.3);
    doc.line(15, 27, pageWidth - 15, 27);

    // Create table data
    const tableData = [
        ["Applied on", new Date(details.applied_date).toLocaleDateString('en-US')],
        ["Job Title", details.occupation_title],
        ["Institute Name", details.institute_name],
        ["Experience Required (in years)", details.experience_required],
        ["Vacancy Available", details.vacancy_available],
        ["Posted Date", new Date(details.job_TS).toLocaleDateString('en-US')],
        ["Institute Email", details.institute_email],
        ["Institute Phone", details.institute_phone]
    ];

    // Generate table
    doc.autoTable({
        startY: 35,
        head: [['Field', 'Details']],
        body: tableData,
        theme: 'striped',
        styles: { 
            fontSize: 12,
            cellPadding: 3,
            halign: 'left',
            valign: 'middle'
        },
        headStyles: { 
            fillColor: [33, 150, 243],
            textColor: 255,
            fontStyle: 'bold',
            fontSize: 13
        },
        columnStyles: {
            0: { cellWidth: 60, fontStyle: 'bold' },
            1: { cellWidth: 'auto' }
        },
        margin: { top: 10 },
        didDrawPage: () => {
            // Add watermark
            doc.setFontSize(40);
            doc.setTextColor(230, 230, 230);
            doc.setGState(new jspdf.GState({ opacity: 0.3 }));
            doc.text('FreelancingGurus', pageWidth / 2, doc.internal.pageSize.height / 2, {
                align: 'center',
                angle: 45
            });
        }
    });

    // Trigger download and show notification
    doc.save(`Job_Details_${details.job_id}.pdf`);
    setTimeout(showDownloadNotification, 300);
    } catch (error) {
        console.error('Error generating PDF:', error);
        showErrorNotification();
    }

// Updated notification function with better timing
function showDownloadNotification() {
    const notification = document.createElement('div');
    notification.className = 'fixed top-4 right-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg shadow-lg animate-slide-in';
    notification.innerHTML = `
        <div class="flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9.707 5.707a1 1 0 00-1.414-1.414L9 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span>Download has started.</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.add('opacity-0', 'transition-opacity', 'duration-500');
        setTimeout(() => notification.remove(), 500);
    }, 2500);
}

// Add this CSS for animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slide-in {
        from { transform: translateX(100%); }
        to { transform: translateX(0); }
    }
    .animate-slide-in {
        animation: slide-in 0.3s ease-out;
    }
`;
document.head.appendChild(style);
    
    setTimeout(() => {
        notification.classList.add('opacity-0');
        setTimeout(() => {
            notification.remove();
        }, 500);
    }, 2500);
}

// Prevent row click when clicking download button
document.querySelectorAll('button').forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.stopPropagation();
    });
});

</script>
</body>
</html>
