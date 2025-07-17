<?php
session_start();
// DB Config
$db = new mysqli('localhost', 'root', '', 'freelance');
$errors = [];

// Check if there are errors stored in the session
if (isset($_SESSION['errors'])) {
    $errors = $_SESSION['errors'];
    unset($_SESSION['errors']); // Clear errors after displaying
}

// Check for account deletion success message
$accountDeleted = false;
if (isset($_GET['deleted']) && $_GET['deleted'] === 'success') {
    $accountDeleted = true;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid = htmlspecialchars(trim($_POST['uid'] ?? ''));
    $password = htmlspecialchars(trim($_POST['password'] ?? ''));

    // Validation
    if (!$uid) $errors[] = "Username required";
    if (!$password) $errors[] = "Password required";

    if (empty($errors)) {
        // Check if the user exists in the database
        $stmt = $db->prepare("SELECT flancer_id, flancer_uname, flancer_pass, flancer_email, status FROM free_user WHERE flancer_uname = ?");
        $stmt->bind_param("s", $uid);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            // Check if the user is suspended
            if ($user['status'] === 'suspended') {
                $errors[] = "Your account is suspended. Please contact the admin.";
            } elseif ($user['status'] === 'deleted') {
                $errors[] = "This account has been deleted. Please contact the administrator for assistance.";
            } elseif ($password === $user['flancer_pass']) {
                // Password is correct, set session variables
                $_SESSION['user_id'] = $user['flancer_id'];
                $_SESSION['username'] = $user['flancer_uname'];
                $_SESSION['email'] = $user['flancer_email'];

                $user_id = $_SESSION['user_id'];

                $update_stmt = $db->prepare("UPDATE free_user SET last_login = NOW() WHERE flancer_id = ?");
                $update_stmt->bind_param("i", $user_id);
                $update_stmt->execute();
                $update_stmt->close();

                // Redirect to the freelancer dashboard
                header("Location: ../job/Job.php");
                exit(); // Stop further execution
            } else {
                $errors[] = "Invalid password";
            }
        } else {
            $errors[] = "User not found";
        }
    }

    // Store errors in session and redirect back to the same page
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Freelancer Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-cyan-50 p-0 "></body>

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
                    
                    <a href="../institute/InstituteLogin.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Institute Login</a>
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
                    <a href="./freelance_regi.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Freelancer Registration</a>
                    <a href="../institute/InstituteRegi.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Institute Registration</a>
                </div>
            </li>
            <li class="flex">
                <a rel="noopener noreferrer" href="../aboutus.html"
                    class="flex items-center px-4 -mb-1 border-b-2 border-transparent hover:border-blue-500 transition duration-300 ease-in-out font-semibold">About
                    Us</a>
            </li>
        </ul>
        <button class="flex justify-end p-4 md:hidden">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                class="w-6 h-6">
                <!-- SVG Path -->
            </svg>
        </button>
    </div>
</header>
<div class="flex justify-center">
    <div>
        <img src="../images/freelancelogin-removebg-preview.png" alt="" class="w-[70%] mt-[20%] ml-[30%]">
    </div>
    <div
        class="relative mx-auto w-full max-w-md bg-white px-6 pt-10 pb-8 shadow-xl ring-1 ring-gray-900/5 sm:rounded-xl sm:px-10 mt-[4%] mr-[20%]">

        <div class="w-full">
            <div class="">
                <h1 class="text-3xl font-semibold text-blue-950 "> Freelancer login</h1>
                <p class="mt-2 text-gray-500">Login to continue</p>
            </div>
            <div class="mt-[5%]">
    <?php if (!empty($errors)): ?>
        <div class="bg-red-100 text-red-700 p-3 mb-4 rounded">
            <?= implode('<br>', $errors) ?>
        </div>
    <?php endif ?>
    <?php if ($accountDeleted): ?>
        <div class="bg-green-100 text-green-700 p-3 mb-4 rounded">
            Your account has been successfully deleted. Thank you for using our platform.
        </div>
    <?php endif ?>
    <form action="" method="post">
        <div class="relative mt-6">
            <input type="text" name="uid" placeholder="Email Address"
                class="peer mt-1 w-full border-b-2 border-gray-300 px-0 py-1 placeholder:text-transparent focus:border-gray-500 focus:outline-none"
                autocomplete="NA" />
            <label for="email"
                class="pointer-events-none absolute top-0 left-0 origin-left -translate-y-1/2 transform text-sm text-gray-800 opacity-75 transition-all duration-100 ease-in-out peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-500 peer-focus:top-0 peer-focus:pl-0 peer-focus:text-sm peer-focus:text-gray-800">User
                Name</label>
        </div>
        <div class="relative mt-6">
            <input type="password" name="password" placeholder="Password"
                class="peer peer mt-1 w-full border-b-2 border-gray-300 px-0 py-1 placeholder:text-transparent focus:border-gray-500 focus:outline-none" />
            <label for="password"
                class="pointer-events-none absolute top-0 left-0 origin-left -translate-y-1/2 transform text-sm text-gray-800 opacity-75 transition-all duration-100 ease-in-out peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-500 peer-focus:top-0 peer-focus:pl-0 peer-focus:text-sm peer-focus:text-gray-800">Password</label>
        </div>
        <div class="my-6">
            <button type="submit" class="w-full rounded-md bg-blue-950 px-3 py-4 text-white ">Login</button>
        </div>
        <div class="text-center">
            <p>New Freelancer? <a href="./freelance_regi.php"
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