<?php
session_start();

if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_name'])) {
  echo "<script>window.location.href = './AdminLogin.php?error=login_please';</script>";
  exit();
}

$conn = new mysqli('localhost', 'root', '', 'freelance');
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $flancer_id = $_POST['flancer_id'];
    
    switch($_POST['action']) {
        case 'update':
            
            // Handle Edit action
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update') {
        $flancer_id = $_POST['flancer_id'];
        $name = $_POST['name'];
        $email = $_POST['email'];
        $username = $_POST['username'];
        $phone = $_POST['phone'];

        $stmt = $conn->prepare("UPDATE free_user SET 
                              flancer_name = ?, 
                              flancer_email = ?, 
                              flancer_uname = ?, 
                              flancer_phone = ? 
                              WHERE flancer_id = ?");
        $stmt->bind_param('ssssi', $name, $email, $username, $phone, $flancer_id);
        
        if ($stmt->execute()) {
            $_SESSION['flash_message'] = "User updated successfully!";
        } else {
            $_SESSION['flash_error'] = "Error updating user: " . $stmt->error;
        }
        $stmt->close();
        
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
  }
            break;
            
        case 'delete':
           
  // Handle Delete action
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $flancer_id = $_POST['flancer_id'];
    
    // Update status to 'deleted' instead of deleting the record to avoid foreign key constraint issues
    $stmt = $conn->prepare("UPDATE free_user SET status = 'deleted' WHERE flancer_id = ?");
    $stmt->bind_param('i', $flancer_id);
    
    if ($stmt->execute()) {
        $_SESSION['flash_message'] = "User deleted successfully!";
    } else {
        $_SESSION['flash_error'] = "Error deleting user: " . $stmt->error;
    }
    $stmt->close();
    
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

            break;
            
        case 'suspend':
            $stmt = $conn->prepare("UPDATE free_user SET status = IF(status='active', 'suspended', 'active') WHERE flancer_id = ?");
            $stmt->bind_param('i', $flancer_id);
            
            if ($stmt->execute()) {
                $_SESSION['flash_message'] = "User status updated successfully!";
            } else {
                $_SESSION['flash_error'] = "Error updating status: " . $stmt->error;
            }
            $stmt->close();
            break;
    }
    
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Replace the existing $result = $conn->query(...) with:

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Base query
$sql = "SELECT * FROM free_user WHERE status != 'deleted' ";
$params = [];
$types = '';

if (!empty($search)) {
    $sql .= "AND (flancer_id LIKE ? OR flancer_name LIKE ? OR flancer_email LIKE ? OR flancer_uname LIKE ? OR flancer_phone LIKE ?) ";
    $searchTerm = "%$search%";
    $types = str_repeat('s', 5);
    $params = array_fill(0, 5, $searchTerm);
}

$sql .= "ORDER BY flancer_TS DESC";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

if (!$stmt->execute()) {
    die("Failed to execute query: " . $stmt->error);
}

$result = $stmt->get_result();
if (!$result) {
    die("Failed to fetch users: " . $conn->error);
}
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
        <!-- Replace the existing search div with this form -->
<form method="GET" action="" class="flex items-center">
    <div class="flex items-center border border-gray-300 rounded-md p-2">
        <input type="text" name="search" placeholder="Search..." 
               value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>" 
               class="px-4 py-2 rounded-l-md focus:outline-none text-sm">
        <button type="submit" class="bg-blue-500 text-white p-2 rounded-r-md hover:bg-blue-400 focus:outline-none">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 3a7 7 0 100 14 7 7 0 000-14zM10 13a3 3 0 110-6 3 3 0 010 6z" />
            </svg>
        </button>
    </div>
</form>
        <!-- Details Dropdown -->
        <select class="border border-gray-300 rounded-md p-2" onchange="handleNavigation(this)">
        
          <option value="">Freelancer</option>
          <option value="./admin_dboard.php">Home</option>
          <option value="./admin_institute_details.php">Institute</option>
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

    <!-- Suspend Confirmation Modal -->
<div id="suspendModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
  <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
    <form method="POST">
      <div class="p-6 space-y-4">
        <h3 class="text-xl font-semibold text-gray-800">Confirm Status Change</h3>
        <p class="text-gray-600" id="suspendModalText">Are you sure you want to change this user's status?</p>
        <input type="hidden" name="flancer_id" id="suspendUserId">
        <input type="hidden" name="action" value="suspend">
      </div>
      <div class="p-4 border-t flex justify-end space-x-3">
        <button type="button" onclick="closeSuspendModal()" 
                class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
          Cancel
        </button>
        <button type="submit" 
                class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-500">
          Confirm
        </button>
      </div>
    </form>
  </div>
</div>

  <!-- Delete Confirmation Modal -->
  <div id="deleteModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
        <form method="POST">
          <div class="p-6 space-y-4">
            <h3 class="text-xl font-semibold text-gray-800">Confirm Deletion</h3>
            <p class="text-gray-600">Are you sure you want to delete this user? This action cannot be undone.</p>
            <input type="hidden" name="flancer_id" id="deleteUserId">
            <input type="hidden" name="action" value="delete">
          </div>
          <div class="p-4 border-t flex justify-end space-x-3">
            <button type="button" onclick="closeDeleteModal()" class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
              Cancel
            </button>
            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-500">
              Delete
            </button>
          </div>
        </form>
      </div>
    </div>
  <!-- Edit Modal -->
  <div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
      <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
        <form method="POST">
          <div class="p-6 space-y-4">
            <h3 class="text-xl font-semibold text-gray-800">Edit User</h3>
            <input type="hidden" name="flancer_id" id="editUserId">
            <input type="hidden" name="action" value="update">
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
              <input type="text" name="name" id="editName" 
                    class="w-full px-3 py-2 border rounded-md" required>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
              <input type="email" name="email" id="editEmail" 
                    class="w-full px-3 py-2 border rounded-md" required>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Username</label>
              <input type="text" name="username" id="editUsername" 
                    class="w-full px-3 py-2 border rounded-md" required>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
              <input type="tel" name="phone" id="editPhone" 
                    class="w-full px-3 py-2 border rounded-md" required>
            </div>
          </div>
          <div class="p-4 border-t flex justify-end space-x-3">
            <button type="button" onclick="closeEditModal()" 
                    class="px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
              Cancel
            </button>
            <button type="submit" 
                    class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-500">
              Save Changes
            </button>
          </div>
        </form>
      </div>
    </div>
    <!-- Flash Messages -->
  <?php if(isset($_SESSION['flash_message'])): ?>
  <div class="max-w-7xl mx-auto mt-4 p-4 bg-green-100 text-green-800 rounded-lg flex justify-between items-center">
    <span><?= $_SESSION['flash_message'] ?></span>
    <button onclick="this.parentElement.remove()" class="text-green-800 hover:text-green-900 ml-4">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
      </svg>
    </button>
    <?php unset($_SESSION['flash_message']); ?>
  </div>
  <?php endif; ?>

  <?php if(isset($_SESSION['flash_error'])): ?>
  <div class="max-w-7xl mx-auto mt-4 p-4 bg-red-100 text-red-800 rounded-lg flex justify-between items-center">
    <span><?= $_SESSION['flash_error'] ?></span>
    <button onclick="this.parentElement.remove()" class="text-red-800 hover:text-red-900 ml-4">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
      </svg>
    </button>
    <?php unset($_SESSION['flash_error']); ?>
  </div>
  <?php endif; ?>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto  mt-4 bg-white shadow-md rounded-lg">
      <table class="w-full bg-white shadow-md rounded-lg overflow-hidden mt-4">
        <thead class="bg-gray-200">
          <tr>
            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">ID</th>
            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Name</th>
            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Email</th>
            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">User ID</th>
            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Phone</th>
            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Status</th>
            <th class="px-6 py-3 text-sm font-semibold text-gray-700" >Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $result->fetch_assoc()): ?>
          <tr class="hover:bg-gray-50">
            <td class="px-6 py-3 text-sm text-gray-700"><?= $row['flancer_id'] ?></td>
            <td class="px-6 py-3 text-sm text-gray-700"><?= htmlspecialchars($row['flancer_name']) ?></td>
            <td class="px-6 py-3 text-sm text-gray-700"><?= htmlspecialchars($row['flancer_email']) ?></td>
            <td class="px-6 py-3 text-sm text-gray-700"><?= $row['flancer_uname'] ?></td>
            <td class="px-6 py-3 text-sm text-gray-700"><?= $row['flancer_phone'] ?></td>
            <td class="px-6 py-3 text-sm text-gray-700">
  <span class="px-2 py-1 rounded-full <?= $row['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
    <?= ucfirst($row['status']) ?>
  </span>
</td>

            <td class="px-6 py-3 text-sm text-gray-700 space-x-2">
            <button onclick="showEditModal(
                '<?= $row['flancer_id'] ?>',
                '<?= htmlspecialchars($row['flancer_name']) ?>',
                '<?= htmlspecialchars($row['flancer_email']) ?>',
                '<?= htmlspecialchars($row['flancer_uname']) ?>',
                '<?= htmlspecialchars($row['flancer_phone']) ?>'
              )" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-500">
                Edit
              </button> 
              <button onclick="showDeleteModal(<?= $row['flancer_id'] ?>)" 
                      class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-500">
                Delete
              </button>
              <button onclick="showSuspendModal(<?= $row['flancer_id'] ?>)" 
          class="<?= $row['status'] === 'active' ? 'bg-yellow-600 hover:bg-yellow-500' : 'bg-green-600 hover:bg-green-500' ?> text-white px-4 py-2 rounded-md">
    <?= $row['status'] === 'active' ? 'Suspend' : 'Activate' ?>
  </button>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>

    <script>
      // Retain search parameter after form submission
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const searchParam = urlParams.get('search');
    
    if (searchParam) {
        document.querySelector('input[name="search"]').value = searchParam;
    }
});
      // Delete Modal Functions
      function showDeleteModal(userId) {
        document.getElementById('deleteUserId').value = userId;
        document.getElementById('deleteModal').classList.remove('hidden');
      }

      function closeDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
      }

      // Close modal when clicking outside
      document.getElementById('deleteModal').addEventListener('click', (e) => {
        if (e.target === document.getElementById('deleteModal')) {
          closeDeleteModal();
        }
      });

      // Close modal with ESC key
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
          closeDeleteModal();
        }
      });
      // Edit Modal Functions
      function showEditModal(id, name, email, username, phone) {
        document.getElementById('editUserId').value = id;
        document.getElementById('editName').value = name;
        document.getElementById('editEmail').value = email;
        document.getElementById('editUsername').value = username;
        document.getElementById('editPhone').value = phone;
        document.getElementById('editModal').classList.remove('hidden');
      }

      function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
      }

      // Shared modal close functions
      function setupModalClose(modalId) {
        document.getElementById(modalId).addEventListener('click', (e) => {
          if (e.target === document.getElementById(modalId)) {
            document.getElementById(modalId).classList.add('hidden');
          }
        });
      }

      // Initialize modal close handlers
      setupModalClose('editModal');
      setupModalClose('deleteModal');

      // ESC key handler
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
          closeEditModal();
          closeDeleteModal();
        }
      });
      
      // Suspend Modal Functions
function showSuspendModal(userId) {
  document.getElementById('suspendUserId').value = userId;
  document.getElementById('suspendModal').classList.remove('hidden');
}

function closeSuspendModal() {
  document.getElementById('suspendModal').classList.add('hidden');
}

// Add to setupModalClose
setupModalClose('suspendModal');

// Update ESC key handler
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') {
    closeEditModal();
    closeDeleteModal();
    closeSuspendModal();
  }
});

    </script>
  </body>
  </html>
