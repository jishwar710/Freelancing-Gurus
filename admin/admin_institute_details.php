<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['admin_id']) || !isset($_SESSION['admin_name'])) {
  echo "<script>window.location.href = './AdminLogin.php?error=login_please';</script>";
  exit();
}
// Database connection
$conn = new mysqli('localhost', 'root', '', 'freelance');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $institute_id = $_POST['institute_id'];
    $action = $_POST['action'];
    
    if ($action === 'accept' || $action === 'reject') {
        $status = $action === 'accept' ? 'verified' : 'rejected';
        $stmt = $conn->prepare("UPDATE institute_details SET status = ? WHERE institute_id = ?");
        $stmt->bind_param('si', $status, $institute_id);
        if ($stmt->execute()) {
            $_SESSION['flash_message'] = "Institute status updated successfully!";

            
        } else {
            $_SESSION['flash_error'] = "Failed to update institute status.";

        }
        $stmt->close();
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    } elseif ($action === 'suspend' || $action === 'activate') {
        $new_status = $action === 'suspend' ? 'suspended' : 'active';
        $stmt = $conn->prepare("UPDATE institute_details SET account_status = ? WHERE institute_id = ?");
        $stmt->bind_param('si', $new_status, $institute_id);
        if ($stmt->execute()) {
            $_SESSION['flash_message'] = "Institute account mode updated successfully!";
        } else {
            $_SESSION['flash_error'] = "Failed to update institute account mode.";
        }
        $stmt->close();
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    } elseif ($action === 'delete') {
        $deleted_status = 'deleted';
        $stmt = $conn->prepare("UPDATE institute_details SET account_status = ? WHERE institute_id = ?");
        $stmt->bind_param('si', $deleted_status, $institute_id);
        if ($stmt->execute()) {
            $_SESSION['flash_message'] = "Institute deleted successfully!";
        } else {
            $_SESSION['flash_error'] = "Failed to delete institute.";
        }
        $stmt->close();
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }
    elseif ($action === 'update') {
      $institute_id = $_POST['institute_id'];
      $name = $_POST['name'];
      $email = $_POST['email'];
      $phone = $_POST['phone'];
      $address = $_POST['address'];
      
      $stmt = $conn->prepare("UPDATE institute_details SET 
          institute_name = ?, 
          institute_email = ?, 
          institute_phone = ?, 
          institute_adrs = ? 
          WHERE institute_id = ?");
      $stmt->bind_param('ssssi', $name, $email, $phone, $address, $institute_id);
      
      if ($stmt->execute()) {
          $_SESSION['flash_message'] = "Institute updated successfully!";
      } else {
          $_SESSION['flash_error'] = "Failed to update institute.";
      }
      $stmt->close();
  }
  header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch all institutes

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build base SQL query
$sql = "SELECT * FROM institute_details WHERE account_status != 'deleted' ";
$params = [];
$types = '';

if (!empty($search)) {
    $sql .= "AND (institute_name LIKE ? OR institute_email LIKE ? OR institute_uname LIKE ? OR CAST(institute_phone AS CHAR) LIKE ? OR institute_adrs LIKE ?) ";
    $searchTerm = "%$search%";
    $types = str_repeat('s', 5);
    $params = array_fill(0, 5, $searchTerm);
}

$sql .= "ORDER BY institute_TS DESC";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

if (!$stmt->execute()) {
    die("Failed to execute query: " . $stmt->error);
}

$result = $stmt->get_result();
if (!$result) {
    die("Failed to fetch institutes: " . $conn->error);
}
//reports
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    
    if ($action === 'view_reports') {
        $institute_id = $_POST['institute_id'];
        $stmt = $conn->prepare("SELECT r.report_id, r.job_id, r.flancer_id, r.report_reason, r.report_text, r.report_TS, r.action 
                                FROM reports r 
                                JOIN job_details j ON r.job_id = j.job_id 
                                WHERE j.institute_id = ?");
        $stmt->bind_param('i', $institute_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $reports = $result->fetch_all(MYSQLI_ASSOC);
        
        header('Content-Type: application/json');
        echo json_encode($reports);
        exit();
    }
    // ... other actions
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
        // Open modal and populate form fields
    function openEditModal(instituteId, name, email, phone, address) {
        document.getElementById('modalInstituteId').value = instituteId;
        document.getElementById('modalName').value = name;
        document.getElementById('modalEmail').value = email;
        document.getElementById('modalPhone').value = phone;
        document.getElementById('modalAddress').value = address;
        
        document.getElementById('editModal').classList.remove('hidden');
    }

    // Close modal
    function closeModal() {
        document.getElementById('editModal').classList.add('hidden');
    }

    // Close modal when clicking outside of it
    window.onclick = function(event) {
    const editModal = document.getElementById('editModal');
    const reportsModal = document.getElementById('reportsModal');

    if (event.target === editModal) {
        closeModal();
    } else if (event.target === reportsModal) {
        closeReportsModal();
    }
};
    
function confirmAction(action, instituteId) {
    let message;
    
    if (action === 'suspend') {
        message = 'Are you sure you want to suspend this institute?';
    } else if (action === 'activate') {
        message = 'Are you sure you want to activate this institute?';
    } else if (action === 'delete') {
        message = 'Are you sure you want to delete this institute? This action cannot be undone.';
    }

    if (confirm(message)) {
        // Submit the form
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '<?php echo $_SERVER['PHP_SELF']; ?>';

        const instituteIdInput = document.createElement('input');
        instituteIdInput.type = 'hidden';
        instituteIdInput.name = 'institute_id';
        instituteIdInput.value = instituteId;
        form.appendChild(instituteIdInput);

        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = action;
        form.appendChild(actionInput);

        document.body.appendChild(form);
        form.submit();
    }
}

//view reports
function viewReports(instituteId) {
    // Show the modal
    document.getElementById('reportsModal').classList.remove('hidden');

    // Fetch reports data from the server
    fetch('fetch_reports.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=view_reports&institute_id=' + instituteId
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        const reportsTableBody = document.getElementById('reportsTableBody');
        reportsTableBody.innerHTML = ''; // Clear previous data

        if (data.length === 0) {
            reportsTableBody.innerHTML = '<tr><td colspan="7" class="px-6 py-3 text-center text-gray-700">No reports found</td></tr>';
        } else {
            data.forEach(report => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td class="px-6 py-3 text-sm text-gray-700">${report.report_id}</td>
                    <td class="px-6 py-3 text-sm text-gray-700">${report.job_id}</td>
                    <td class="px-6 py-3 text-sm text-gray-700">${report.flancer_id}</td>
                    <td class="px-6 py-3 text-sm text-gray-700">${report.report_reason}</td>
                    <td class="px-6 py-3 text-sm text-gray-700">${report.report_text}</td>
                    <td class="px-6 py-3 text-sm text-gray-700">${report.report_TS}</td>
                    <td class="px-6 py-3 text-sm text-gray-700">${report.action}</td>
                `;
                reportsTableBody.appendChild(row);
            });
        }
    })
    .catch(error => {
        console.error('Error fetching reports:', error);
        alert('Failed to fetch reports. Please try again.');
    });
}
function closeReportsModal() {
    document.getElementById('reportsModal').classList.add('hidden');
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
                <option value="">Institute</option>
                <option value="./admin_dboard.php">Home</option>
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

    <!-- Edit Modal -->
<div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-semibold mb-4">Edit Institute Details</h3>
        <form id="editForm" method="POST" class="space-y-4">
            <input type="hidden" name="institute_id" id="modalInstituteId">
            <input type="hidden" name="action" value="update">
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Name</label>
                <input type="text" name="name" id="modalName" 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" id="modalEmail" 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Phone</label>
                <input type="text" name="phone" id="modalPhone" 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Address</label>
                <input type="text" name="address" id="modalAddress" 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
            </div>
            
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeModal()" 
                        class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
                    Cancel
                </button>
                <button type="submit" 
                        class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
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
    <div class="max-w-10xl mx-auto mt-4 bg-white shadow-md rounded-lg">
    <<div class="overflow-x-auto">
    <table class="w-full bg-white shadow-md rounded-lg overflow-hidden mt-4">
        <thead class="bg-gray-200">
            <tr>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">ID</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Name</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Email</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Username</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Phone</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Address</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Status</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Mode</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Reports</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-700"><?= $row['institute_id'] ?></td>
                    <td class="px-6 py-4 text-sm text-gray-700"><?= $row['institute_name'] ?></td>
                    <td class="px-6 py-4 text-sm text-gray-700"><?= $row['institute_email'] ?></td>
                    <td class="px-6 py-4 text-sm text-gray-700"><?= $row['institute_uname'] ?></td>
                    <td class="px-6 py-4 text-sm text-gray-700"><?= $row['institute_phone'] ?></td>
                    <td class="px-6 py-4 text-sm text-gray-700"><?= $row['institute_adrs'] ?></td>
                    <td class="px-6 py-4 text-sm text-gray-700">
                        <form method="POST" class="inline">
                            <input type="hidden" name="institute_id" value="<?= $row['institute_id'] ?>">
                            <?php if ($row['status'] === 'pending'): ?>
                                <button type="submit" name="action" value="accept" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-500" aria-label="Accept">
                                    Accept
                                </button>
                                <button type="submit" name="action" value="reject" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-500 ml-2" aria-label="Reject">
                                    Reject
                                </button>
                            <?php else: ?>
                                <span class="text-sm p-2 <?= $row['status'] === 'verified' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?> rounded">
                                    <?= ucfirst($row['status']) ?>
                                </span>
                            <?php endif; ?>
                        </form>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-700">
                        <span class="text-sm p-2 <?= $row['account_status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?> rounded">
                            <?= ucfirst($row['account_status']) ?>
                        </span>
                    </td>
                    <td class="px-6 py-3 text-sm text-gray-700">
                        <button onclick="viewReports('<?= $row['institute_id'] ?>')" 
                                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-500">
                            View Reports
                        </button>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-700">
                        <div class="flex items-center space-x-2">
                            <?php if ($row['account_status'] === 'active'): ?>
                                <button onclick="confirmAction('suspend', '<?= $row['institute_id'] ?>')" 
                                        class="bg-yellow-600 text-white px-4 py-2 rounded-md hover:bg-yellow-500" aria-label="Suspend">
                                    Suspend
                                </button>
                            <?php else: ?>
                                <button onclick="confirmAction('activate', '<?= $row['institute_id'] ?>')" 
                                        class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-500" aria-label="Activate">
                                    Activate
                                </button>
                            <?php endif; ?>
                            <button onclick="openEditModal(
                                '<?= $row['institute_id'] ?>', 
                                '<?= addslashes($row['institute_name']) ?>', 
                                '<?= addslashes($row['institute_email']) ?>', 
                                '<?= addslashes($row['institute_phone']) ?>', 
                                '<?= addslashes($row['institute_adrs']) ?>'
                            )" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-500" aria-label="Edit">
                                Edit
                            </button>
                            <button onclick="confirmAction('delete', '<?= $row['institute_id'] ?>')" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-500" aria-label="Delete">
                                Delete
                            </button>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
    </div>
    <!-- Reports Modal -->
<div id="reportsModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg p-6 w-full">
        <h3 class="text-lg font-semibold mb-4">Reports</h3>
        <table id="reportsTable" class="w-full bg-white shadow-md rounded-lg overflow-hidden mt-4">
            <thead class="bg-gray-200">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Report ID</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Job ID</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Freelancer ID</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Reason</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Details</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Timestamp</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Action</th>
                </tr>
            </thead>
            <tbody id="reportsTableBody">
                <!-- Reports will be populated here -->
            </tbody>
        </table>
        <div class="flex justify-end space-x-2">
            <button type="button" onclick="closeReportsModal()" 
                    class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
                Close
            </button>
        </div>
    </div>
</div>
</body>
</html>
<?php
$conn->close();
?>