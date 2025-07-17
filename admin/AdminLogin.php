<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['uid']);
    $password = trim($_POST['password']);

    // Validate hardcoded admin credentials first
    if ($email !== 'admin@gmail.com' || $password !== 'admin') {
        echo "<script>window.location.href = './AdminLogin.php';</script>";
        exit();
    }

    // Database connection
    $servername = "localhost";
    $username = "root";
    $dbpassword = "";
    $dbname = "freelance";

    $conn = new mysqli($servername, $username, $dbpassword, $dbname);

    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Verify credentials against database
    $stmt = $conn->prepare("SELECT flancer_id, flancer_name FROM free_user WHERE flancer_email = ? AND flancer_pass = ?");
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        $_SESSION['admin_id'] = $admin['flancer_id'];
        $_SESSION['admin_name'] = $admin['flancer_name'];

        // Close resources before redirecting
        $stmt->close();
        $conn->close();

        // Redirect to admin dashboard using JavaScript
        echo "<script>window.location.href = './admin_dboard.php' </script>";
        exit();
    } else {
        // Close resources before redirecting
        $stmt->close();
        $conn->close();

        // Redirect back to login page with error using JavaScript
        echo "<script>window.location.href = './AdminLogin.php?error=invalid_credentials';</script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
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
                    <a rel="noopener noreferrer" href="../homepage2.html"
                        class="flex items-center px-4 -mb-1 border-b-2 border-transparent hover:border-blue-500 transition duration-300 ease-in-out font-semibold">Home</a>
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
                    <a href="../institute/InstituteLogin.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Institute Login</a>
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
                    <a href="../freelancer/freelance_regi.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Freelancer Registration</a>
                    <a href="../institute/InstituteRegi.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Institute Registration</a>
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
            <img src="../images/adminbg-removebg-preview.png" alt="" class="w-[50%] mt-[20%] ml-[60%]">
        </div>
        <div
            class="relative mx-auto w-full max-w-md bg-white px-6 pt-10 pb-8 shadow-xl ring-1 ring-gray-900/5 sm:rounded-xl sm:px-10 mt-[5%] mr-[20%]">
            <div class="w-full">
                <div class="text-center">
                    <h1 class="text-3xl font-semibold text-blue-950">Admin Login</h1>
                </div>
                <div class="mt-10">
                    <form method="post">
                        <div class="relative mt-6">
                            <input type="email" name="uid" placeholder="Email Address"
                                class="peer mt-1 w-full border-b-2 border-gray-300 px-0 py-1 placeholder:text-transparent focus:border-gray-500 focus:outline-none"
                                autocomplete="off" required />
                            <label for="text"
                                class="pointer-events-none absolute top-0 left-0 origin-left -translate-y-1/2 transform text-sm text-gray-800 opacity-75 transition-all duration-100 ease-in-out peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-500 peer-focus:top-0 peer-focus:pl-0 peer-focus:text-sm peer-focus:text-gray-800">Email
                                Address</label>
                        </div>
                        <div class="relative mt-6">
                            <input type="password" name="password" placeholder="Password"
                                class="peer mt-1 w-full border-b-2 border-gray-300 px-0 py-1 placeholder:text-transparent focus:border-gray-500 focus:outline-none"
                                required />
                            <label for="password"
                                class="pointer-events-none absolute top-0 left-0 origin-left -translate-y-1/2 transform text-sm text-gray-800 opacity-75 transition-all duration-100 ease-in-out peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-base peer-placeholder-shown:text-gray-500 peer-focus:top-0 peer-focus:pl-0 peer-focus:text-sm peer-focus:text-gray-800">Password</label>
                        </div>
                        <div class="my-6">
                            <button type="submit"
                                class="w-full rounded-md bg-blue-950 px-3 py-4 text-white">Login</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    </div>
</body>
</html>