<?php
session_start();

if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_name'])) {
  echo "<script>window.location.href = './AdminLogin.php?error=login_please';</script>";
  exit();
}

// Database connection
$conn = new mysqli('localhost', 'root', '', 'freelance');
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Fetch counts
$counts = [
  'institutes' => $conn->query("SELECT COUNT(*) AS total FROM institute_details")->fetch_assoc()['total'],
  'freelancers' => $conn->query("SELECT COUNT(*) AS total FROM free_user")->fetch_assoc()['total'],
  'applications' => $conn->query("SELECT COUNT(*) AS total FROM applications")->fetch_assoc()['total'],
  'jobs' => $conn->query("SELECT COUNT(*) AS total FROM job_details")->fetch_assoc()['total']
];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Panel</title>
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
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
<body class="bg-cyan-50 h-screen flex flex-col">
  <!-- Main Content -->
  <div class="flex-1 bg-gray-50 flex flex-col">
    <!-- Top Bar -->
    <div class="bg-white shadow-md p-4 flex justify-between items-center border-b border-gray-200">
      <!-- Logo Section -->
      <div class="flex items-center space-x-4">
        <h1 class="text-2xl font-bold text-gray-800">FreelancingGurus</h1>
      </div>
      
      <!-- Dropdowns, Search Bar and Button Section -->
      <div class="flex items-center space-x-6">
      
        
        <!-- Details Dropdown -->
        <select class="border border-gray-300 rounded-md p-2" onchange="handleNavigation(this)">
          <option value="./admin_dboard.php">Home</option>
          <option value="./admin_institute_details.php">Institute</option>
          <option value="./admin_freelancer_details.php">Freelancer</option>
          <option value="./admin_job_details.php">Job</option>
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
          
    <!-- Content Area -->
    <div class="flex-1 p-8 mt-0 overflow-auto bg-cyan-50">
        <h2 class="text-4xl font-semibold text-gray-800 text-center">Dashboard</h2>
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-2 gap-14 p-14">
        <!-- Stats Cards -->
        <div class="bg-white shadow-lg rounded-lg p-12 text-center transition-transform transform hover:scale-105 hover:shadow-2xl">
          <h3 class="text-xl font-semibold text-gray-800">Total Institutes</h3>
          <p class="text-3xl text-blue-500"><?php echo $counts['institutes']; ?></p>
        </div>
        
        <div class="bg-white shadow-lg rounded-lg p-12 text-center transition-transform transform hover:scale-105 hover:shadow-2xl">
          <h3 class="text-xl font-semibold text-gray-800">Total Freelancers</h3>
          <p class="text-3xl text-green-500"><?php echo $counts['freelancers']; ?></p>
        </div>
        
        <div class="bg-white shadow-lg rounded-lg p-12 text-center transition-transform transform hover:scale-105 hover:shadow-2xl">
          <h3 class="text-xl font-semibold text-gray-800">Total Job Applications</h3>
          <p class="text-3xl text-red-500"><?php echo $counts['applications']; ?></p>
        </div>
        
        <div class="bg-white shadow-lg rounded-lg p-12 text-center transition-transform transform hover:scale-105 hover:shadow-2xl">
          <h3 class="text-xl font-semibold text-gray-800">Total Jobs Posted</h3>
          <p class="text-3xl text-red-500"><?php echo $counts['jobs']; ?></p>
        </div>
      </div>
    </div>
  </div>
</body>
</html>