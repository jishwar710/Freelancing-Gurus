<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Document</title>
</head>
<!-- <body class="bg-cyan-50 p-0 font-serif text-sky-900 "> -->

<header class="w-full  ">
    <div class="mx-auto flex justify-between h-16 bg-white w-full max-w-full">
        <a rel="noopener noreferrer" href="#" aria-label="Back to homepage" class="flex items-center p-2">
            <h1 class="font-serif text-3xl text-sky-700">FreelancingGurus</h1>
        </a>
        <ul class="items-stretch hidden space-x-3 mr-5 md:flex ">
            <li class="flex">
                <a rel="noopener noreferrer" href="index.php"
                    class="flex items-center px-4 -mb-1 text-indigo-600  border-b-2 border-transparent hover:border-blue-500 transition duration-300 ease-in-out font-semibold">Home</a>
            </li>
          
            <!-- Login Dropdown -->
            <li class="flex relative group">
                <button class="flex items-center px-4 -mb-1 border-b-2 border-transparent hover:border-blue-500 transition duration-300 ease-in-out font-semibold">
                    Login <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div class="absolute hidden group-hover:block top-full right-0 w-48 bg-white shadow-lg rounded-md py-2 z-10">
                    <a href="./freelancer/FreelancerLogin.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Freelancer Login</a>
                    <a href="./institute/InstituteLogin.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Institute Login</a>
                    <a href="./admin/AdminLogin.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Admin Login</a>
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
                    <a href="./institute/InstituteRegi.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Institute Registration</a>
                </div>
            </li>
            <li class="flex">
                <a rel="noopener noreferrer" href="./aboutus.html"
                    class="flex items-center px-4 -mb-1 border-b-2 border-transparent hover:border-blue-500 transition duration-300 ease-in-out font-semibold">About
                    Us</a>
            </li>
        </ul>
        
    </div>
</header>

<body>
    <section class="bg-sky-900 text-white">
        <div class="container mx-auto px-6 py-12 flex flex-col md:flex-row items-center">
            <div class="md:w-1/2">
                <h1 class="text-4xl font-bold">Our Mission</h1>
                <p class="mt-4 text-xl">Our aim is to create a comprehensive online platform that facilitates seamless
                    collaboration and connection between freelance and teachers and educational institutes and to
                    revolutionize the way freelance teachers and educational institutes connect and collaborate ,
                    ultimately fostering innovation,excellence and inclusivity in the field of education.</p>

            </div>
            <div class="md:w-1/2 mt-6 ml-80 md:mt-5">
                <img src="./images/1-removebg-preview.png" alt="Hero Image" class="rounded-lg">
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class=" bg-cyan-50  mx-auto px-6 py-12">
        <div class="text-center mb-12 ">
            <h2 class="text-3xl font-bold">Create an Account for Freelancer</h2>
            <p class="text-gray-900 font-bold mt-4">

                Go to <a href="FreelancerLogin.html" class="text-indigo-500">Login</a> if you already have an account
                just Sign in to your account, otherwise you can create free account by clicking <a
                    href="FreelancerReg.html" class="text-indigo-500"><br>create account</a>
            </p>
        </div>

        <div class="flex flex-wrap justify-center space-x-4">
            <div class="w-full md:w-1/4 p-6">
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <div class="flex items-center justify-center ml-20 h-20 w-20  text-white rounded-full">
                        <!-- Add your icon here -->
                        <img src="./images/question-removebg-preview.png" alt="">
                    </div>
                    <h3 class="text-xl font-bold mt-4 ml-8">How does it Work?</h3>
                    <p class="text-gray-600 mt-2 ml-4">Create your free account now and start exploring the world of
                        thousands of jobs listed with us.<br>
                        <br>
                    </p>
                </div>
            </div>
            <div class="w-full md:w-1/4 p-6">
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <div class="flex items-center mr-4 justify-center ml-20 h-20 w-20  text-white rounded-full">
                        <img src="./images/icons8-search-job-96.png" alt="">
                        <!-- Add your icon here -->

                    </div>
                    <h3 class="text-xl font-bold mt-4 text-center mr-4">Search Jobs</h3>
                    <p class="text-gray-600 mt-2 ml-4">After Successful Login you can Search thousands of Jobs listed on
                        our Platform.Simply search for a Job and just click Apply.</p>
                </div>
            </div>
            <div class="w-full md:w-1/4 p-6">
                <div class="bg-white p-6 rounded-lg shadow-lg">
                    <div class="flex items-center  justify-center ml-20 h-16 w-16  text-white rounded-full">
                        <!-- Add your icon here -->
                        <img src="./images/icons8-apply-96.png" alt="">
                    </div>
                    <h3 class="text-xl font-bold mt-4 text-center mr-4">Apply</h3>
                    <p class="text-gray-600 mt-2 ml-4">Search for a Job and click Apply
                        Check your Dashboard and Email for Acceptance and any Further details required by the
                        Institute..</p>
                </div>
            </div>
        </div>
    </section>

</body>

</html>