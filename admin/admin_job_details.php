<?php
session_start();
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_name'])) {
  echo "<script>window.location.href = './AdminLogin.php?error=login_please';</script>";
  exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "freelance";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle delete action
if(isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['job_id'])) {
    $job_id = $_GET['job_id'];
    
    // Update job status to 'removed' instead of actually deleting
    $update_sql = "UPDATE job_details SET status = 'removed' WHERE job_id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("i", $job_id);
    
    if($stmt->execute()) {
        $_SESSION['flash_message'] = "Job removed successfully!";
    } else {
        $_SESSION['flash_error'] = "Error removing job: " . $stmt->error;
    }
    $stmt->close();
    
    // Redirect to the same page to prevent form resubmission
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch all jobs

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Base query
$sql = "SELECT job_id, occupation_title, salary, vacancy_available, institute_name, institute_id, job_TS 
        FROM job_details 
        WHERE (status != 'removed' OR status IS NULL) ";

$params = [];
$types = '';

// Update the existing search handling code to:
if (!empty($search)) {
  $sql .= "AND (occupation_title LIKE ? 
              OR institute_name LIKE ? 
              OR institute_id LIKE ?
              OR CAST(job_id AS CHAR) LIKE ?) "; // Added job_id search
  $searchTerm = "%$search%";
  $types = str_repeat('s', 4); // Changed from 3 to 4
  $params = array_fill(0, 4, $searchTerm);
}

$sql .= "ORDER BY job_TS DESC";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

if (!$stmt->execute()) {
    die("Error executing query: " . $stmt->error);
}

$result = $stmt->get_result();
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Panel</title>
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Heroicons CDN (for the search icon) -->
  <script src="https://unpkg.com/heroicons@1.0.6/outline.js"></script>
  <script>
    function handleNavigation(selectElement) {
      const value = selectElement.value; // Get the selected URL
      const text = selectElement.options[selectElement.selectedIndex].text; // Get the selected text

      if (value) {
        // Redirect to the selected page
        window.location.href = value;

        // Update the dropdown label dynamically
        selectElement.options[0].text = text; // Change the placeholder text to the selected option
        selectElement.selectedIndex = 0; // Reset the dropdown to the first option
      }
    }
  </script>
</head>
<body class="bg-cyan-50">

  <!-- Navbar -->
  <div class="bg-white shadow-md p-4 flex justify-between items-center border-b border-gray-200">
    <!-- Logo Section -->
    <div class="flex items-center space-x-4">
      <img src="https://via.placeholder.com/50" alt="Logo" class="h-10 w-10">
      <h2 class="text-2xl font-semibold text-gray-800">Dashboard</h2>
    </div>
    
    <!-- Dropdowns, Search Bar and Icon Section -->
    <div class="flex items-center space-x-6">
      <!-- Search Bar and Icon -->
    <!-- Replace existing search div with this form -->
<form method="GET" action="" class="flex items-center">
    <div class="flex items-center border border-gray-300 rounded-md p-2">
        <input type="text" name="search" placeholder="Search jobs..." 
               value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>"
               class="px-4 py-2 rounded-l-md focus:outline-none text-sm w-64">
        <button type="submit" class="bg-blue-800 text-white px-4 py-2 rounded-r-md hover:bg-blue-600 focus:outline-none">
            Search
        </button>
    </div>
</form>

      <!-- Details Dropdown -->
      <select class="border border-gray-300 rounded-md p-2" onchange="handleNavigation(this)">
        <option value="">Job</option>
        <option value="./admin_dboard.php">Home</option>
        <option value="./admin_institute_details.php">Institute</option>
        <option value="./admin_freelancer_details.php">Freelancer</option>
        <option value="./feedback_details.php">Feedback</option>
      </select>
      
      <!-- Reports Dropdown -->
      <a href="./admin_institute_report.php">Reports</a>

      
      <!-- Profile Dropdown -->
      <select class="border border-gray-300 rounded-md p-2" onchange="handleNavigation(this)">
        <option value="">Hi, Admin</option>
        <option value="../freelancer/freelancer_logout.php">Logout</option>
      </select>
    </div>
  </div>

  <!-- Flash Messages -->
  <?php if (isset($_SESSION['flash_message'])): ?>
    <div id="flashMessage" class="max-w-7xl mx-auto mt-4 p-4 bg-green-100 text-green-800 rounded-lg flex justify-between items-center">
        <span><?= $_SESSION['flash_message'] ?></span>
        <button onclick="document.getElementById('flashMessage').style.display='none'" class="text-green-800 hover:text-green-900 ml-4">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>
    <?php unset($_SESSION['flash_message']); // Unset the session variable immediately after displaying ?>
  <?php endif; ?>

  <?php if (isset($_SESSION['flash_error'])): ?>
    <div id="flashError" class="max-w-7xl mx-auto mt-4 p-4 bg-red-100 text-red-800 rounded-lg flex justify-between items-center">
        <span><?= $_SESSION['flash_error'] ?></span>
        <button onclick="document.getElementById('flashError').style.display='none'" class="text-red-800 hover:text-red-900 ml-4">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>
    <?php unset($_SESSION['flash_error']); // Unset the session variable immediately after displaying ?>
  <?php endif; ?>

  <!-- Main Content -->
  <div class="max-w-7xl mx-auto p-6 mt-12 bg-white shadow-md rounded-lg">
    <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden">
      <thead class="bg-gray-200">
        <tr>
          <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Job ID</th>
          <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Occupation Title</th>
          <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Posted by</th>
          <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Institute ID</th>
          <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Vacancy Available</th>
          <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Posted Date</th>
          <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                echo '<tr class="hover:bg-gray-50">';
                echo '<td class="px-6 py-3 text-sm text-gray-700">' . htmlspecialchars($row['job_id']) . '</td>';
                echo '<td class="px-6 py-3 text-sm text-gray-700">' . htmlspecialchars($row['occupation_title']) . '</td>';
                echo '<td class="px-6 py-3 text-sm text-gray-700">' . htmlspecialchars($row['institute_name']) . '</td>';
                echo '<td class="px-6 py-3 text-sm text-gray-700">' . htmlspecialchars($row['institute_id']) . '</td>';
                echo '<td class="px-6 py-3 text-sm text-gray-700">' . htmlspecialchars($row['vacancy_available']) . '</td>';
                echo '<td class="px-6 py-3 text-sm text-gray-700">' . date('d M Y', strtotime($row['job_TS'])) . '</td>';
                echo '<td class="px-6 py-3 text-sm text-gray-700">';
                echo '<a href="admin_job_details.php?action=delete&job_id=' . $row['job_id'] . '" onclick="return confirm(\'Are you sure you want to remove this job?\');" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-500 ml-2">Delete</a>';
                echo '</td>';
                echo '</tr>';
            }
        } else {
          echo '<tr><td colspan="7" class="px-6 py-3 text-center text-gray-500">';
          echo empty($search) ? 'No jobs found' : 'No jobs found matching "'.htmlspecialchars($search).'"';
          echo '</td></tr>';
      }
        $conn->close();
        ?>
      </tbody>
    </table>
  </div>
  <script>
// Retain search query after page reload
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const searchQuery = urlParams.get('search');
    
    if (searchQuery) {
        document.querySelector('input[name="search"]').value = searchQuery;
    }
});
</script>
</body>
</html>
