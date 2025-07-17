
<?php
session_start();
$conn = new mysqli('localhost', 'root', '', 'freelance');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check admin authentication
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_name'])) {
    header("Location: admin_login.php");
    exit();
}

// Handle search query
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Fetch reports with related data
$query = "
    SELECT 
        r.report_id,
        r.job_id,  
        r.report_reason,
        r.report_text,
        r.report_TS,
        r.action,
        r.usr_res,
        j.occupation_title,
        i.institute_name,
        f.flancer_uname
    FROM reports r
    INNER JOIN job_details j ON r.job_id = j.job_id
    INNER JOIN institute_details i ON j.institute_id = i.institute_id
    INNER JOIN free_user f ON r.flancer_id = f.flancer_id
    WHERE 
        j.occupation_title LIKE ? OR
        i.institute_name LIKE ? OR
        f.flancer_uname LIKE ? OR
        r.report_reason LIKE ? OR
        r.report_text LIKE ? OR
        r.report_id LIKE ? OR
        r.job_id LIKE ?  
    ORDER BY r.report_TS DESC
";


// Prepare the statement
$stmt = $conn->prepare($query);
$searchParam = "%$search%";
$stmt->bind_param("sssssss",$searchParam, $searchParam, $searchParam, $searchParam, $searchParam, $searchParam, $searchParam);
$stmt->execute();
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

    // Function to clear the search
    function clearSearch() {
      window.location.href = window.location.pathname; // Redirect to the same page without query parameters
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
      <form method="GET" action="" class="flex items-center border border-gray-300 rounded-md p-2">
        <input type="text" name="search" placeholder="Search..." class="px-4 py-2 rounded-l-md focus:outline-none text-sm" value="<?= htmlspecialchars($search) ?>">
        <!-- Search Icon -->
        <button type="submit" class="bg-blue-500 text-white p-2 rounded-r-md hover:bg-blue-400 focus:outline-none">
          <!-- Heroicon Search Magnifying Glass -->
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 3a7 7 0 100 14 7 7 0 000-14zM10 13a3 3 0 110-6 3 3 0 010 6z" />
          </svg>
        </button>
        <!-- Clear Button -->
        <?php if (!empty($search)): ?>
          <button type="button" onclick="clearSearch()" class="ml-2 bg-gray-300 text-gray-700 p-2 rounded-md hover:bg-gray-400 focus:outline-none">
            Clear
          </button>
        <?php endif; ?>
      </form>

      <!-- Details Dropdown -->
      <select class="border border-gray-300 rounded-md p-2" onchange="handleNavigation(this)">
      <option value="./admin_dboard.php">Home</option> 
        <option value="./admin_institute_details.php">Institute</option>
        <option value="./admin_freelancer_details.php">Freelancer</option>
        <option value="./admin_job_details.php">Job</option>
        <option value="./feedback_details.php">Feedback</option>
      </select>
      
      <!-- Reports Dropdown -->
      <a href="#">Reports</a>

      <!-- Profile Dropdown -->
      <select class="border border-gray-300 rounded-md p-2" onchange="handleNavigation(this)">
        <option value="">Hi, Admin</option>
        <option value="../freelancer/freelancer_logout.php">Logout</option>
      </select>
    </div>
  </div>

  <!-- Main Content -->
  <div class="max-w-7xl mx-auto p-6 mt-12 bg-white shadow-md rounded-lg">
    <?php if (isset($_SESSION['report_message'])): ?>
      <div class="mb-4 p-3 bg-green-100 text-green-700 rounded">
        <?= $_SESSION['report_message']; unset($_SESSION['report_message']); ?>
      </div>
    <?php endif; ?>

    <!-- Reports Table -->
    <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden">
      <thead class="bg-gray-200">
        <tr>
          <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Report ID</th>
          <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Job ID</th>
          <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Job Title</th>
          <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Institute</th>
          <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Reason</th>
          <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Details</th>
          <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Reported By</th>
          <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Date</th>
          <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Status</th>
          <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Actions</th>
        </tr>
      </thead>
      <tbody>
  <?php if ($result->num_rows > 0): ?>
    <?php while ($report = $result->fetch_assoc()): ?>
      <tr class="hover:bg-gray-50">
        <td class="px-6 py-3 text-sm text-gray-700">#<?= $report['report_id'] ?></td>
        <td class="px-6 py-3 text-sm text-gray-700"><?= htmlspecialchars($report['job_id']) ?></td>
        <td class="px-6 py-3 text-sm text-gray-700"><?= htmlspecialchars($report['occupation_title']) ?></td>
        <td class="px-6 py-3 text-sm text-gray-700"><?= htmlspecialchars($report['institute_name']) ?></td>
        <td class="px-6 py-3 text-sm text-gray-700 capitalize"><?= $report['report_reason'] ?></td>
        <td class="px-6 py-3 text-sm text-gray-700 max-w-xs"><?= htmlspecialchars($report['report_text']) ?></td>
        <td class="px-6 py-3 text-sm text-gray-700"><?= htmlspecialchars($report['flancer_uname']) ?></td>
        <td class="px-6 py-3 text-sm text-gray-700"><?= date('M d, Y', strtotime($report['report_TS'])) ?></td>
        <td class="px-6 py-3 text-sm">
          <span class="px-2 py-1 rounded-full <?= $report['action'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' ?>">
            <?= ucfirst($report['action']) ?>
          </span>
        </td>
        <td class="px-6 py-3 text-sm">
          <?php if ($report['action'] === 'resolved'): ?>
            <span class="px-2 py-1 rounded-full bg-gray-100 text-green-800">
              <?= ucfirst($report['usr_res']) ?>
            </span>
          <?php else: ?>
            <form action="handle_action.php" method="post" onsubmit="return validateForm(this)">
  <input type="hidden" name="report_id" value="<?= $report['report_id'] ?>">
  <select name="action" class="border border-gray-300 rounded-md p-2">
    <option value="">Select Action</option>
    <option value="job_deleted">Job has been deleted</option>
    <option value="institute_suspended">Institute has been suspended</option>
    <option value="report has been viewed">Your report has been seen and is under review</option>
  </select>
  <button type="submit" class="bg-blue-500 text-white p-2 rounded-md hover:bg-blue-400 focus:outline-none">Submit</button>
  <!-- Error message container -->
  <div id="error-message" class="text-red-500 text-sm mt-2" style="display: none;">Please select an action.</div>
</form>

<script>
  // Function to validate the form
  function validateForm(form) {
    const action = form.action.value; // Get the selected action
    const errorMessage = document.getElementById('error-message'); // Get the error message element

    if (!action) {
      // If no action is selected, show the error message
      errorMessage.style.display = 'block';
      return false; // Prevent form submission
    }

    // If an action is selected, hide the error message and allow submission
    errorMessage.style.display = 'none';
    return true;
  }
</script>
          <?php endif; ?>
        </td>
      </tr>
    <?php endwhile; ?>
  <?php else: ?>
    <tr>
      <td colspan="10" class="px-6 py-3 text-center text-gray-500">No reports found</td>
    </tr>
  <?php endif; ?>
</tbody>
    </table>
  </div>

</body>
</html>
<?php $conn->close(); ?>