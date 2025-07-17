<?php
session_start();

// Check admin authentication
if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_name'])) {
  echo "<script>window.location.href = './AdminLogin.php?error=login_please';</script>";
  exit();
}
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "freelance";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch feedback data
$feedback_query = "SELECT username, email, rating, feedback_text, created_at 
                   FROM feedback 
                   ORDER BY created_at DESC";
$result = $conn->query($feedback_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Feedback Details</title>
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
<body class="bg-cyan-50 text-gray-800">
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
      <div class="flex items-center border border-gray-300 rounded-md p-2">
        <input type="text" placeholder="Search..." class="px-4 py-2 rounded-l-md focus:outline-none text-sm">
        <!-- Search Icon -->
        <button class="bg-blue-500 text-white p-2 rounded-r-md hover:bg-blue-400 focus:outline-none">
          <!-- Heroicon Search Magnifying Glass -->
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 3a7 7 0 100 14 7 7 0 000-14zM10 13a3 3 0 110-6 3 3 0 010 6z" />
          </svg>
        </button>
      </div>

      <!-- Details Dropdown -->
      <select class="border border-gray-300 rounded-md p-2" onchange="handleNavigation(this)">
        <option value="">Feedback</option>
        <option value="./admin_dboard.php">Home</option>
        <option value="./admin_institute_details.php">Institute</option>
        <option value="./admin_freelancer_details.php">Freelancer</option>
        <option value="./admin_job_details.php">Job</option>
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

  <div class="container mx-auto mt-6 p-4">
        <h2 class="text-2xl font-bold mb-4">Feedback Details</h2>

        <div class="overflow-x-auto">
            <div class="max-w-7xl mx-auto p-6 bg-white shadow-md rounded-lg">
                <table class="min-w-full bg-white shadow-md rounded-lg overflow-hidden">
                    <thead class="bg-blue-300 text-black">
                        <tr>
                            <th class="px-6 py-3 text-left text-sm font-medium">Sr. No</th>
                            <th class="px-6 py-3 text-left text-sm font-medium">Name</th>
                            <th class="px-6 py-3 text-left text-sm font-medium">Email ID</th>
                            <th class="px-6 py-3 text-left text-sm font-medium">Rating</th>
                            <th class="px-6 py-3 text-left text-sm font-medium">Description</th>
                            <th class="px-6 py-3 text-left text-sm font-medium">Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php $counter = 1; ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr class="border-b hover:bg-gray-100">
                                    <td class="px-6 py-4 text-sm"><?= $counter ?></td>
                                    <td class="px-6 py-4 text-sm"><?= htmlspecialchars($row['username']) ?></td>
                                    <td class="px-6 py-4 text-sm"><?= htmlspecialchars($row['email']) ?></td>
                                    <td class="px-6 py-4 text-sm">
                                        <?php
                                        $ratingClass = '';
                                        $rating = strtolower(trim($row['rating']));
                                        if ($rating === 'positive') {
                                            $ratingClass = 'bg-green-100 text-green-800';
                                        } elseif ($rating === 'neutral') {
                                            $ratingClass = 'bg-yellow-100 text-yellow-800';
                                        } elseif ($rating === 'negative') {
                                            $ratingClass = 'bg-red-100 text-red-800';
                                        } else {
                                            $ratingClass = 'bg-gray-100 text-gray-800';
                                        }
                                        ?>
                                        <span class="px-2 py-1 rounded-full <?= $ratingClass ?>">
                                            <?= ucfirst(htmlspecialchars($row['rating'])) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <?= $row['feedback_text'] ? htmlspecialchars($row['feedback_text']) : 'No feedback provided' ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <?= date('d M Y H:i', strtotime($row['created_at'])) ?>
                                    </td>
                                </tr>
                                <?php $counter++; ?>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                    No feedback records found
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<!-- Add this modal structure before the closing </body> tag -->
<div id="feedbackModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl">
        <div class="flex justify-between items-center p-4 border-b">
            <h3 class="text-xl font-semibold">Feedback Details</h3>
            <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-600">Name:</label>
                    <p id="modal-name" class="mt-1 text-gray-900"></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600">Email:</label>
                    <p id="modal-email" class="mt-1 text-gray-900"></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600">Rating:</label>
                    <p id="modal-rating" class="mt-1"></p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-600">Date:</label>
                    <p id="modal-date" class="mt-1 text-gray-900"></p>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600">Feedback:</label>
                <p id="modal-feedback" class="mt-1 text-gray-900 whitespace-pre-wrap"></p>
            </div>
        </div>
        <div class="p-4 border-t flex justify-end space-x-2">
            <button onclick="closeModal()" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                Close
            </button>
        </div>
    </div>
</div>

<script>
// Add this script after the table
document.querySelectorAll('tbody tr').forEach(row => {
    row.addEventListener('click', (e) => {
        // Prevent modal opening when clicking links/buttons
        if (e.target.tagName === 'A' || e.target.tagName === 'BUTTON') return;
        
        const cells = row.querySelectorAll('td');
        const rating = cells[3].textContent.trim().toLowerCase();
        
        // Set modal content
        document.getElementById('modal-name').textContent = cells[1].textContent;
        document.getElementById('modal-email').textContent = cells[2].textContent;
        document.getElementById('modal-rating').innerHTML = `
            <span class="px-2 py-1 rounded-full ${getRatingColor(rating)}">
                ${cells[3].textContent}
            </span>
        `;
        document.getElementById('modal-date').textContent = cells[5].textContent;
        document.getElementById('modal-feedback').textContent = cells[4].textContent;
        
        // Show modal
        document.getElementById('feedbackModal').classList.remove('hidden');
    });
});

function getRatingColor(rating) {
    switch(rating) {
        case 'positive': return 'bg-green-100 text-green-800';
        case 'neutral': return 'bg-yellow-100 text-yellow-800';
        case 'negative': return 'bg-red-100 text-red-800';
        default: return 'bg-gray-100 text-gray-800';
    }
}

function closeModal() {
    document.getElementById('feedbackModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('feedbackModal').addEventListener('click', (e) => {
    if (e.target === document.getElementById('feedbackModal')) {
        closeModal();
    }
});

// Close modal with ESC key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !document.getElementById('feedbackModal').classList.contains('hidden')) {
        closeModal();
    }
});
</script>
    <?php $conn->close(); ?>
</body>
</html>
