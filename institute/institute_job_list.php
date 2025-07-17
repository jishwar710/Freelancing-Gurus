<?php
// Start the session
session_start();

// Check if user is logged in
if (!isset($_SESSION['institute_id'])) {
    header("Location: ./Institute_Login.php"); // Redirect to login page if not logged in
    exit;
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

// Use prepared statement to prevent SQL injection
$stmt = $conn->prepare("SELECT job_id, occupation_title, salary, experience_required, job_description, vacancy_available, skill_required, job_TS FROM job_details WHERE institute_id = ? and status = 'active'");
$stmt->bind_param("s", $_SESSION['institute_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edtfrm'])) {
    $job_id = $_POST['job_id'];
    $occupation_title = $_POST['occupation_title'];
    $experience_required = $_POST['experience_required'];
    $job_description = $_POST['job_description'];
    $vacancy_available = $_POST['vacancy_available'];
    $skill_required = $_POST['skill_required'];

    $stmt = $conn->prepare("UPDATE job_details SET 
        occupation_title = ?, 
        experience_required = ?, 
        job_description = ?, 
        vacancy_available = ?, 
        skill_required = ? 
        WHERE job_id = ? AND institute_id = ?");
    
    $stmt->bind_param("sssssii", 
        $occupation_title,
        $experience_required,
        $job_description,
        $vacancy_available,
        $skill_required,
        $job_id,
        $_SESSION['institute_id']
    );

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Job updated successfully!";
        header("Location: institute_job_list.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Error updating record: " . $stmt->error;
    }

    $stmt->close();
}
//delete job
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_job'])) {
    $job_id = $_POST['delete_job_id'];
    echo "<pre>Debug: Job ID to delete: " . htmlspecialchars($job_id) . "</pre>"; // Debugging output
    $stmt = $conn->prepare("UPDATE job_details SET status = 'deleted' WHERE job_id = ? AND institute_id = ?");
    $stmt->bind_param("ii", $job_id, $_SESSION['institute_id']);
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Job deleted successfully!";
    } else {
        $_SESSION['error_message'] = "Error marking job as deleted: " . $stmt->error;
    }
    header("Location: institute_job_list.php");
    exit();
}
// Fetch messages from session
$success_message = $_SESSION['success_message'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
unset($_SESSION['success_message'], $_SESSION['error_message']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Job Advertisements</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-cyan-50 p-0 ">

<?php if ($success_message): ?>
        <div id="toast" class="fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg flex items-center">
            <span><?php echo $success_message; ?></span>
            <button onclick="document.getElementById('toast').remove()" class="ml-4">✕</button>
        </div>
        <script>
            setTimeout(() => document.getElementById('toast').remove(), 3000);
        </script>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div id="errorToast" class="fixed top-4 right-4 bg-red-500 text-white px-4 py-2 rounded-lg shadow-lg flex items-center">
            <span><?php echo $error_message; ?></span>
            <button onclick="document.getElementById('errorToast').remove()" class="ml-4">✕</button>
        </div>
        <script>
            setTimeout(() => document.getElementById('errorToast').remove(), 3000);
        </script>
    <?php endif; ?>

    <header class="w-full  ">
        <div class="mx-auto flex justify-between h-16 bg-white w-full max-w-full">
            <a rel="noopener noreferrer" href="#" aria-label="Back to homepage" class="flex items-center p-2">
                <h1 class="font-serif text-3xl text-sky-700">FreelancingGurus</h1>
            </a>
            <ul class="items-stretch hidden space-x-3 mr-5 md:flex ">
                <li class="flex">
                    <a rel="noopener noreferrer" href="./instituteAccount.php"
                        class="flex items-center px-4 -mb-1  border-b-2 border-transparent hover:border-blue-500 transition duration-300 ease-in-out font-semibold">Dashboard</a>
                </li>
                <li class="flex">
                    <a rel="noopener noreferrer" href="../freelancer/freelancer_logout.php"
                        class="flex items-center px-4 -mb-1  border-b-2 border-transparent hover:border-blue-500 transition duration-300 ease-in-out font-semibold">Log out</a>
                </li>
            </ul>
        </div>
    </header>

    <div class="mt-[5%] ml-[4%]">
        <h3 class="font-semibold text-3xl text-blue-800">Job Advertisement Details</h3>
    </div>
    
    <section class="py-1 bg-blueGray-50">
        <div class="w-full xl:w-[97%] mb-12 xl:mb-0 px-4 mx-auto mt-2">
            <div class="relative flex flex-col min-w-0 break-words bg-white w-full mb-6 shadow-lg rounded">
                <div class="rounded-t mb-0 px-4 py-3 border-0">
                    <div class="flex flex-wrap items-center">
                        
                    </div>
                </div>

                <div class="block w-full overflow-x-auto">
                    <table class="items-center bg-transparent w-full border-collapse">
                    <thead>
                        <tr>
                            <th class="px-6 bg-blueGray-50 text-blueGray-500 align-middle border border-solid border-blueGray-100 py-3 text-xs uppercase border-l-0 border-r-0 whitespace-nowrap font-semibold text-left">Occupation Title</th>
                            <th class="px-6 bg-blueGray-50 text-blueGray-500 align-middle border border-solid border-blueGray-100 py-3 text-xs uppercase border-l-0 border-r-0 whitespace-nowrap font-semibold text-left">Experience Required</th>
                            <th class="px-6 bg-blueGray-50 text-blueGray-500 align-middle border border-solid border-blueGray-100 py-3 text-xs uppercase border-l-0 border-r-0 whitespace-nowrap font-semibold text-left">Job Description</th>
                            <th class="px-6 bg-blueGray-50 text-blueGray-500 align-middle border border-solid border-blueGray-100 py-3 text-xs uppercase border-l-0 border-r-0 whitespace-nowrap font-semibold text-left">Vacancy Available</th>
                            <th class="px-6 bg-blueGray-50 text-blueGray-500 align-middle border border-solid border-blueGray-100 py-3 text-xs uppercase border-l-0 border-r-0 whitespace-nowrap font-semibold text-left">Skills Required</th>
                            <th class="px-6 bg-blueGray-50 text-blueGray-500 align-middle border border-solid border-blueGray-100 py-3 text-xs uppercase border-l-0 border-r-0 whitespace-nowrap font-semibold text-left">Posted On</th>
                            <th class="px-6 bg-blueGray-50 text-blueGray-500 align-middle border border-solid border-blueGray-100 py-3 text-xs uppercase border-l-0 border-r-0 whitespace-nowrap font-semibold text-center" colspan="2">Action</th>
                        </tr>
                    </thead>

                        <tbody>
                        <?php
if ($result->num_rows > 0) {
    // Output data of each row
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td class='border-t-0 px-6 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left text-blueGray-700'>" . htmlspecialchars($row['occupation_title']) . "</td>";
        echo "<td class='border-t-0 px-6 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4'>" . htmlspecialchars($row['experience_required']) . "</td>";
        echo "<td class='border-t-0 px-6 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4 text-left text-blueGray-700'>" . htmlspecialchars($row['job_description']) . "</td>";
        echo "<td class='border-t-0 px-6 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4'>" . htmlspecialchars($row['vacancy_available']) . "</td>";
        echo "<td class='border-t-0 px-6 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4'>" . htmlspecialchars($row['skill_required']) . "</td>";
        // Add posted date cell
        echo "<td class='border-t-0 px-6 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4'>" . date('Y-m-d H:i', strtotime($row['job_TS'])) . "</td>";
        
        // Edit button
        echo "<td class='border-t-0 px-6 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4'>
                <button class='edit-btn bg-blue-800 text-white active:bg-indigo-600 text-xs font-bold uppercase px-3 py-1 rounded outline-none focus:outline-none mr-1 mb-1 ease-linear transition-all duration-150' 
                        type='button'
                        data-job-id='" . $row['job_id'] . "'
                        data-occupation-title='" . htmlspecialchars($row['occupation_title']) . "'
                        data-salary='" . htmlspecialchars($row['salary']) . "'
                        data-experience='" . htmlspecialchars($row['experience_required']) . "'
                        data-description='" . htmlspecialchars($row['job_description']) . "'
                        data-vacancy='" . htmlspecialchars($row['vacancy_available']) . "'
                        data-skills='" . htmlspecialchars($row['skill_required']) . "'>
                    Edit
                </button>
              </td>";

        // Delete button
        echo "<td class='border-t-0 px-6 align-middle border-l-0 border-r-0 text-xs whitespace-nowrap p-4'>
    <button type='button' class='bg-red-600 text-white active:bg-red-700 text-xs font-bold uppercase px-3 py-1 rounded outline-none focus:outline-none mr-1 mb-1 ease-linear transition-all duration-150' 
            data-job-id='" . $row['job_id'] . "'
            onclick='confirmDelete(" . $row['job_id'] . ")'>
        Delete
    </button>
</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='9' class='text-center py-4'>No job advertisements found.</td></tr>";
}
$stmt->close();
$conn->close();
?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>  
    <!-- Edit Modal -->
<div id="editModal" class="hidden fixed z-10 inset-0 overflow-y-auto">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Edit Job Details</h3>
                <form id="editForm" method="POST">
                    <input type="hidden" name="job_id" id="editJobId">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="editOccupationTitle">Occupation Title</label>
                        <input type="text" name="occupation_title" id="editOccupationTitle" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="editExperience">Experience Required</label>
                        <input type="text" name="experience_required" id="editExperience" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="editDescription">Job Description</label>
                        <textarea name="job_description" id="editDescription" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline h-32"></textarea>
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="editVacancy">Vacancy Available</label>
                        <input type="number" name="vacancy_available" id="editVacancy" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="editSkills">Skills Required</label>
                        <input type="text" name="skill_required" id="editSkills" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                    </div>
                </form>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="submit" form="editForm" name="edtfrm" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                    Save Changes
                </button>
                <button type="button" onclick="closeModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>
<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="hidden fixed z-10 inset-0 overflow-y-auto">
    <div class="lex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="relative bg-white rounded-lg shadow dark:bg-gray-700">
            <div class="flex justify-end p-2">
                <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center dark:hover:bg-gray-600 dark:hover:text-white" onclick="closeDeleteModal()">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                </button>
            </div>
            <div class="p-6 pt-0 text-center">
                <svg class="mx-auto mb-4 text-gray-400 w-14 h-14 dark:text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <h3 class="mb-5 text-lg font-normal text-gray-500 dark:text-gray-400">Are you sure you want to delete this job?</h3>
                <form method="POST" action="" class="d-inline">
                    <input type="hidden" name="delete_job_id" id="deleteJobId" value="">
                    <button type="submit" name="delete_job" class="text-white bg-red-600 hover:bg-red-800 focus:ring-4 focus:outline-none focus:ring-red-300 dark:focus:ring-red-800 font-medium rounded-lg text-sm inline-flex items-center px-5 py-2.5 text-center mr-2">
                        Yes, I'm sure
                    </button>
                </form>
                <button type="button" onclick="closeDeleteModal()" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-gray-200 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10 dark:bg-gray-700 dark:text-gray-300 dark:border-gray-500 dark:hover:text-white dark:hover:bg-gray-600 dark:focus:ring-gray-600">
                    No, cancel
                </button>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Edit Modal Handling
    const editButtons = document.querySelectorAll('.edit-btn');
    const editModal = document.getElementById('editModal');

    editButtons.forEach(button => {
        button.addEventListener('click', () => {
            console.log('Edit button clicked'); // Debugging output
            // Populate form fields
            document.getElementById('editJobId').value = button.dataset.jobId;
            document.getElementById('editOccupationTitle').value = button.dataset.occupationTitle;
            document.getElementById('editExperience').value = button.dataset.experience;
            document.getElementById('editDescription').value = button.dataset.description;
            document.getElementById('editVacancy').value = button.dataset.vacancy;
            document.getElementById('editSkills').value = button.dataset.skills;

            // Show modal
            editModal.classList.remove('hidden');
        });
    });

    // Close Edit Modal
    window.closeModal = function () {
        console.log('Close edit modal'); // Debugging output
        editModal.classList.add('hidden');
    };

    // Close Edit Modal when clicking outside
    editModal.addEventListener('click', (e) => {
        if (e.target === editModal) {
            closeModal();
        }
    });
});

// Move these functions outside of DOMContentLoaded
function confirmDelete(jobId) {
    document.getElementById('deleteJobId').value = jobId;
    document.getElementById('deleteModal').classList.remove('hidden');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}

document.addEventListener('DOMContentLoaded', function () {
    // Edit Modal Handling
    const editButtons = document.querySelectorAll('.edit-btn');
    const editModal = document.getElementById('editModal');

    editButtons.forEach(button => {
        button.addEventListener('click', () => {
            console.log('Edit button clicked'); // Debugging output
            // Populate form fields
            document.getElementById('editJobId').value = button.dataset.jobId;
            document.getElementById('editOccupationTitle').value = button.dataset.occupationTitle;
            document.getElementById('editExperience').value = button.dataset.experience;
            document.getElementById('editDescription').value = button.dataset.description;
            document.getElementById('editVacancy').value = button.dataset.vacancy;
            document.getElementById('editSkills').value = button.dataset.skills;

            // Show modal
            editModal.classList.remove('hidden');
        });
    });

    // Close Edit Modal
    window.closeModal = function () {
        console.log('Close edit modal'); // Debugging output
        editModal.classList.add('hidden');
    };

    // Close Edit Modal when clicking outside
    editModal.addEventListener('click', (e) => {
        if (e.target === editModal) {
            closeModal();
        }
    });

    // Delete Modal Handling
    const deleteButtons = document.querySelectorAll('button[data-modal-toggle="deleteModal"][data-job-id]');
    const deleteModal = document.getElementById('deleteModal');
    const deleteJobIdInput = document.getElementById('deleteJobId');

    deleteButtons.forEach(button => {
        button.addEventListener('click', function () {
            console.log('Delete button clicked'); // Debugging output
            const jobId = this.dataset.jobId;
            deleteJobIdInput.value = jobId;
            deleteModal.classList.remove('hidden');
        });
    });

    // Close Delete Modal when clicking outside
    deleteModal.addEventListener('click', (e) => {
        if (e.target === deleteModal) {
            console.log('Close delete modal'); // Debugging output
            deleteModal.classList.add('hidden');
        }
    });

    // Close Delete Modal when clicking on 'No' or close button
    const closeModalButtons = document.querySelectorAll('[data-modal-toggle="deleteModal"]');
    closeModalButtons.forEach(button => {
        button.addEventListener('click', function () {
            console.log('Close delete modal'); // Debugging output
            deleteModal.classList.add('hidden');
        });
    });
});
</script>
</body>
</html>
