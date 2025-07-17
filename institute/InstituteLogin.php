<?php
session_start();
// DB Config
$db = new mysqli('localhost', 'root', '', 'freelance');
$errors = [];

// Check for stored errors
if (isset($_SESSION['errors'])) {
    $errors = $_SESSION['errors'];
    unset($_SESSION['errors']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // Validation
    if (!$email) $errors[] = "Institute username required";
    if (!$password) $errors[] = "Password required";

    if (empty($errors)) {
        // Check if institute exists
        $stmt = $db->prepare("SELECT * FROM institute_details WHERE institute_uname = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $institute = $result->fetch_assoc();

            // Verify password
            if ($password === $institute['institute_pass']) {
                // Check account status
                if ($institute['account_status'] === 'suspended') {
                    $errors[] = "Your account is suspended. Please contact the administrator.";
                } else if ($institute['account_status'] === 'deleted') {
                    $errors[] = "This account has been deleted. Please contact the administrator for assistance.";
                } else {
                    // Set session variables
                    $_SESSION['institute_id'] = $institute['institute_id'];
                    $_SESSION['institute_email'] = $institute['institute_email'];
                    $_SESSION['institute_uname'] = $institute['institute_uname'];
                    $_SESSION['institute_name'] = $institute['institute_name'];

                    // Redirect to dashboard
                    header("Location: ./instituteAccount.php");
                    exit();
                }
            } else {
                $errors[] = "Invalid password";
            }
        } else {
            $errors[] = "Institute not found";
        }
    }

    // Store errors and redirect
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}
?>

<!-- The rest of your HTML remains the same -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="bg-cyan-50 p-0 ">

    <header class="w-full  ">
        <div class="mx-auto flex justify-between h-16 bg-white w-full max-w-full">
            <a rel="noopener noreferrer" href="#" aria-label="Back to homepage" class="flex items-center p-2">
                <h1 class="font-serif text-3xl text-sky-700">FreelancingGurus</h1>
            </a>
            <ul class="items-stretch hidden space-x-3 mr-5 md:flex ">
                <li class="flex">
                    <a rel="noopener noreferrer" href="../index.php"
                        class="flex items-center px-4 -mb-1  border-b-2 border-transparent hover:border-blue-500 transition duration-300 ease-in-out font-semibold">Home</a>
                </li>
                <!-- Login Dropdown -->
            <li class="flex relative group">
                <button class="flex items-center px-4 -mb-1 border-b-2 border-transparent hover:border-blue-500 transition duration-300 ease-in-out font-semibold">
                    Login <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div class="absolute hidden group-hover:block top-full right-0 w-48 bg-white shadow-lg rounded-md py-2 z-10">
                    <a href="../freelancer/FreelancerLogin.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Freelancer Login</a>
                    <a href="../admin/AdminLogin.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Admin Login</a>
                </div>
            </li>
            <!-- Register Dropdown -->
            <li class="flex relative group">
                <button class="flex items-center px-4 -mb-1 border-b-2 border-transparent hover:border-blue-500 transition duration-300 ease-in-out font-semibold">
                    Register <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div class="absolute hidden group-hover:block top-full right-0 w-48 bg-white shadow-lg rounded-md py-2 z-10">
                    <a href="./freelancer/freelance_regi.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Freelancer Registration</a>
                    <a href="./InstituteRegi.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Institute Registration</a>
                </div>
            </li>
                <li class="flex">
                    <a rel="noopener noreferrer" href="../aboutus.html"
                        class="flex items-center px-4 -mb-1 border-b-2 border-transparent hover:border-blue-500 transition duration-300 ease-in-out font-semibold">About
                        Us</a>
                </li>
            </ul>
        </div>
    </header>
    <div class="flex justify-center">
        <div>
            <img src="../images/institudebg-removebg-preview.png" alt="" class="w-[60%] mt-[20%] ml-[50%]">
        </div>
        <div class="relative mx-auto w-full max-w-md bg-white px-6 pt-10 pb-8 shadow-xl ring-1 ring-gray-900/5 sm:rounded-xl sm:px-10 mt-[4%] mr-[20%]">
            <div class="w-full">
                <div class="">
                    <h1 class="text-3xl font-semibold text-blue-950 "> Institute login</h1>
                    <p class="mt-2 text-gray-500">Login to continue</p>
                </div>
                <div class="mt-[5%]">
                    <?php if (!empty($errors)): ?>
                        <div class="bg-red-100 text-red-700 p-3 mb-4 rounded">
                            <?= implode('<br>', $errors) ?>
                        </div>
                    <?php endif ?>
                    <form action="" method="post">
                    <div class="relative mt-6">
                            <input type="text" name="username" id="username" placeholder="Inastitute Username"
                                class="peer mt-1 w-full border-b-2 border-gray-300 px-0 py-1 placeholder:text-transparent focus:border-gray-500 focus:outline-none"
                                autocomplete="NA" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" />
                            <label for="email"
                                class="pointer-events-none absolute top-0 left-0 origin-left -translate-y-1/2 transform text-sm text-gray-800 opacity-75 transition-all duration-100 ease-in-out peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-500 peer-focus:top-0 peer-focus:pl-0 peer-focus:text-sm peer-focus:text-gray-800">Institute Username</label>
                        </div>  
                        <div class="relative mt-6">
                            <input type="password" name="password" id="password" placeholder="Password"
                                class="peer peer mt-1 w-full border-b-2 border-gray-300 px-0 py-1 placeholder:text-transparent focus:border-gray-500 focus:outline-none" />
                            <label for="password"
                                class="pointer-events-none absolute top-0 left-0 origin-left -translate-y-1/2 transform text-sm text-gray-800 opacity-75 transition-all duration-100 ease-in-out peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-500 peer-focus:top-0 peer-focus:pl-0 peer-focus:text-sm peer-focus:text-gray-800">Password</label>
                        </div>
                        <div class="my-6">
                            <button type="submit"
                                class="w-full rounded-md bg-blue-950 px-3 py-4 text-white ">Login</button>
                        </div>
                        <div class="text-center ">
                            <p>New Institute? <a href="InstituteRegi.php"
                                    class="hover:text-blue-700 underline underline-offset-2">SignUp</a></p>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
    
    </div>
</body>

</html>