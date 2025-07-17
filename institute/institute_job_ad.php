<?php
session_start();

// Check if institute is logged in
if (!isset($_SESSION['institute_id']) || !isset($_SESSION['institute_name'])) {
    header("Location: InstituteLogin.php");
    exit();
}

$db = new mysqli('localhost', 'root', '', 'freelance');
$errors = [];
$success = '';

// Retrieve success message from session if exists
if (isset($_SESSION['success'])) {
    $success = $_SESSION['success'];
    unset($_SESSION['success']);
}

$institute_id = $_SESSION['institute_id'];

// Check if institute is verified
$stmt = $db->prepare("SELECT status FROM institute_details WHERE institute_id = ?");
$stmt->bind_param("i", $institute_id);
$stmt->execute();
$status = $stmt->get_result()->fetch_assoc()['status'];

if ($status !== 'verified') {
    die("Your institute is not verified. Contact the admin.");
}

// Form processing
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize inputs
    $fields = [
        'occ' => trim($_POST['occ'] ?? ''),
        'exp_req' => trim($_POST['exp_req'] ?? ''),
        'job_des' => trim($_POST['job_des'] ?? ''),
        'vacancy' => trim($_POST['vacancy'] ?? ''),
        'Skills_req' => trim($_POST['Skills_req'] ?? ''),
        'duration' => trim($_POST['duration'] ?? '')
    ];

    // Validate inputs
    if (empty($fields['occ'])) $errors[] = "Occupation title is required";
    if (empty($fields['exp_req'])) $errors[] = "Experience required is needed";
    if (empty($fields['job_des'])) $errors[] = "Job description cannot be empty";
    if (empty($fields['vacancy']) || !is_numeric($fields['vacancy'])) $errors[] = "Valid vacancy number is required";
    if (empty($fields['Skills_req'])) $errors[] = "Skills required field is mandatory";
    if (empty($fields['duration']) || !is_numeric($fields['duration'])) $errors[] = "Valid duration is required";

    if (empty($errors)) {
        try {
            $stmt = $db->prepare("INSERT INTO job_details 
                (occupation_title, salary, experience_required, job_description, 
                 vacancy_available, skill_required, duration, institute_name, institute_id, job_TS)
                VALUES (?, 2, ?, ?, ?, ?, ?, ?, ?, NOW())");
        
            $stmt->bind_param("sssssssi",
                $fields['occ'],               // occupation_title (string)
                $fields['exp_req'],           // experience_required (string)
                $fields['job_des'],           // job_description (string)
                $fields['vacancy'],           // vacancy_available (integer)            
                $fields['Skills_req'],        // skill_required (string)
                $fields['duration'],          // duration (string)
                $_SESSION['institute_name'],  // institute_name (string)
                $_SESSION['institute_id']     // institute_id (integer)
            );
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Job advertisement uploaded successfully!";
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                $errors[] = "Error uploading job: " . $stmt->error;
            }
            $stmt->close();
        } catch (Exception $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
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
                    <a rel="noopener noreferrer" href="./instituteAccount.php" class="flex items-center px-4 -mb-1 border-b-2 border-transparent hover:border-blue-500 transition duration-300 ease-in-out font-semibold">Dashboard</a>
                </li>
                <li class="flex">
                    <a rel="noopener noreferrer" href="../freelancer/freelancer_logout.php" class="flex items-center px-4 -mb-1 border-b-2 border-transparent hover:border-blue-500 transition duration-300 ease-in-out font-semibold">Log out</a>
                </li>
            </ul>
        </div>
    </header>
    <h1 class="text-3xl text-blue-900 text-center mt-[2%]">Upload Job Advertisement</h1>
    
    <!-- Error/Success Messages -->
    <?php if (!empty($errors)): ?>
        <div class="max-w-md mx-auto mt-4 p-4 bg-red-100 text-red-700 rounded">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div class="max-w-md mx-auto mt-4 p-4 bg-green-100 text-green-700 rounded">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>
    
    <div class="w-[40%] shadow-xl bg-white ml-[30%] mt-[1%] rounded">
        <form class="max-w-md mx-auto pb-10 pt-[5%]" method="post">
            <!-- Occupation Title -->
            <div class="relative z-0 w-full mb-5 group">
                <input type="text" name="occ" value="<?= htmlspecialchars($fields['occ'] ?? '') ?>" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer" placeholder=" " required />
                <label class="peer-focus:font-medium absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 peer-focus:text-blue-600 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">
                    Occupation Title
                </label>
            </div>
            
            <!-- Experience Required -->
            <div class="relative z-0 w-full mb-5 group">
                <input type="text" name="exp_req" value="<?= htmlspecialchars($fields['exp_req'] ?? '') ?>" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer" placeholder=" " required />
                <label class="peer-focus:font-medium absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 peer-focus:text-blue-600 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">
                    Experience Required
                </label>
            </div>
            
            <!-- Job Description -->
            <div class="relative z-0 w-full mb-5 group">
                <textarea name="job_des" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer" placeholder=" " required><?= htmlspecialchars($fields['job_des'] ?? '') ?></textarea>
                <label class="peer-focus:font-medium absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 peer-focus:text-blue-600 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">
                    Job Description
                </label>
            </div>
            
            <!-- Vacancy -->
            <div class="relative z-0 w-full mb-5 group">
                <input type="number" name="vacancy" value="<?= htmlspecialchars($fields['vacancy'] ?? '') ?>" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer" placeholder=" " required />
                <label class="peer-focus:font-medium absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 peer-focus:text-blue-600 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">
                    Vacancy
                </label>
            </div>
            
            <!-- Skills Required -->
            <div class="relative z-0 w-full mb-5 group">
                <input type="text" name="Skills_req" value="<?= htmlspecialchars($fields['Skills_req'] ?? '') ?>" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer" placeholder=" " required />
                <label class="peer-focus:font-medium absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 peer-focus:text-blue-600 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">
                    Skills Required
                </label>
            </div>
            
            <!-- Duration -->
            <div class="relative z-0 w-full mb-5 group">
                <input type="number" name="duration" value="<?= htmlspecialchars($fields['duration'] ?? '') ?>" class="block py-2.5 px-0 w-full text-sm text-gray-900 bg-transparent border-0 border-b-2 border-gray-300 appearance-none focus:outline-none focus:ring-0 focus:border-blue-600 peer" placeholder=" " required />
                <label class="peer-focus:font-medium absolute text-sm text-gray-500 duration-300 transform -translate-y-6 scale-75 top-3 -z-10 origin-[0] peer-focus:start-0 peer-focus:text-blue-600 peer-placeholder-shown:scale-100 peer-placeholder-shown:translate-y-0 peer-focus:scale-75 peer-focus:-translate-y-6">
                    Duration (in months)
                </label>
            </div>
            
            <button type="submit" class="text-white mt-5 bg-blue-800 hover:bg-blue-900 focus:ring-4 focus:outline-none focus:ring-blue-700 font-medium rounded-lg text-sm w-full sm:w-auto px-5 py-2.5 text-center">
                Upload
            </button>
        </form>
    </div>
</body>
</html>
<?php 
$db->close();
?>