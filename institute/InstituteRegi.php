<?php
session_start();
$db = new mysqli('localhost', 'root', '', 'freelance');

$errors = [];
$fields = ['institute_name', 'institute_email', 'institute_phone', 'institute_adrs', 'institute_uname', 'institute_pass'];
$form = array_fill_keys($fields, '');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and store form data
    foreach ($fields as $field) {
        $form[$field] = htmlspecialchars(trim($_POST[$field] ?? ''));
    }

    // Validation
    if (empty($form['institute_name'])) $errors[] = "Institute name required";
    if (!filter_var($form['institute_email'], FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
    if (!preg_match('/^\d{10}$/', $form['institute_phone'])) $errors[] = "Phone must be 10 digits";
    if (empty($form['institute_adrs'])) $errors[] = "Address required";
    if (empty($form['institute_uname'])) $errors[] = "Username required";
    
    // Password validation
    if (strlen($form['institute_pass']) < 6 || 
        !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{6,}$/', $form['institute_pass'])) {
        $errors[] = "Password must be 6+ chars with 1 uppercase, 1 lowercase, 1 number, and 1 symbol";
    }
    if ($form['institute_pass'] !== trim($_POST['cnf_pass'] ?? '')) $errors[] = "Passwords do not match";

    if (empty($errors)) {
        $stmt = $db->prepare("INSERT INTO institute_details 
            (institute_name, institute_uname, institute_phone, institute_adrs, institute_email, institute_pass, status) 
            VALUES (?, ?, ?, ?, ?, ?, 'pending')");
        
        $stmt->bind_param("ssssss", 
            $form['institute_name'],
            $form['institute_uname'],
            $form['institute_phone'],
            $form['institute_adrs'],
            $form['institute_email'],
            $form['institute_pass']
        );

        if ($stmt->execute()) {
            echo '<script>alert("Registration successful!"); window.location.href = "./InstituteLogin.php";</script>';
            exit();
        } else {
            $errors[] = "Registration failed: " . $stmt->error;
        }
        $stmt->close();
    }
    
    $_SESSION['errors'] = $errors;
    $_SESSION['form'] = $form;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Institute Registration</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Password toggle functionality
            function setupPasswordToggle(inputId, toggleId) {
                const input = document.getElementById(inputId);
                const toggle = document.getElementById(toggleId);
                
                toggle.addEventListener('click', () => {
                    const type = input.type === 'password' ? 'text' : 'password';
                    input.type = type;
                    toggle.textContent = type === 'password' ? 'Show' : 'Hide';
                });
            }

            setupPasswordToggle('institute_pass', 'toggle-pass');
            setupPasswordToggle('cnf_pass', 'toggle-cnf-pass');

            // Password validation
            const passwordInput = document.getElementById('institute_pass');
            const requirements = {
                length: document.getElementById('req-length'),
                uppercase: document.getElementById('req-uppercase'),
                lowercase: document.getElementById('req-lowercase'),
                number: document.getElementById('req-number'),
                symbol: document.getElementById('req-symbol')
            };

            function updateRequirements(password) {
                const checks = {
                    length: password.length >= 6,
                    uppercase: /[A-Z]/.test(password),
                    lowercase: /[a-z]/.test(password),
                    number: /\d/.test(password),
                    symbol: /[\W_]/.test(password)
                };

                Object.entries(checks).forEach(([key, isValid]) => {
                    requirements[key].querySelector('span').textContent = isValid ? '✓' : '✗';
                    requirements[key].querySelector('span').className = isValid ? 'text-green-500' : 'text-red-500';
                });

                document.getElementById('length-counter').textContent = `${password.length}/6`;
            }

            passwordInput.addEventListener('input', function() {
                updateRequirements(this.value);
                document.getElementById('institute_pass-error').hidden = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{6,}$/.test(this.value);
            });

            // Confirm password validation
            document.getElementById('cnf_pass').addEventListener('input', function() {
                const isValid = this.value === passwordInput.value;
                document.getElementById('cnf_pass-error').hidden = isValid;
            });

            // Field validation
            const validations = {
                institute_name: v => v.trim() !== '',
                institute_email: v => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v),
                institute_phone: v => /^\d{10}$/.test(v),
                institute_adrs: v => v.trim() !== '',
                institute_uname: v => v.trim() !== ''
            };

            Object.keys(validations).forEach(field => {
                document.getElementById(field).addEventListener('input', function() {
                    const isValid = validations[field](this.value);
                    document.getElementById(`${field}-error`).hidden = isValid;
                });
            });
        });
    </script>
</head>
<body class="bg-cyan-50 p-0">
<header class="w-full  ">
        <div class="mx-auto flex justify-between h-16 bg-white w-full max-w-full">
            <a rel="noopener noreferrer" href="#" aria-label="Back to homepage" class="flex items-center p-2">
                <h1 class="font-serif text-3xl text-sky-700">FreelancingGurus</h1>
            </a>
            <ul class="items-stretch hidden space-x-3 mr-5 md:flex ">
                <li class="flex">
                    <a rel="noopener noreferrer" href="../homepage2.html"
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
                    <a href="./InstituteLogin.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Institute Login</a>
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
    <div class="max-w-xl mx-auto mt-10 bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="text-2xl py-4 px-6 bg-blue-950 text-white text-center font-bold uppercase">
            Institute Registration
        </div>
        
        <?php if (!empty($_SESSION['errors'])): ?>
            <div class="bg-red-100 text-red-700 p-3 m-4 rounded">
                <?php foreach ($_SESSION['errors'] as $error): ?>
                    <?= $error ?><br>
                <?php endforeach; ?>
            </div>
        <?php 
            unset($_SESSION['errors']);
            endif; 
        ?>

        <form class="py-4 px-6" method="POST">
            <!-- Institute Name -->
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2" for="institute_name">Institute Name</label>
                <input class="w-full p-2 border rounded hover:scale-105 duration-300" 
                       id="institute_name" name="institute_name" type="text" 
                       value="<?= htmlspecialchars($_SESSION['form']['institute_name'] ?? '') ?>" required>
                <span class="text-red-500 text-sm" id="institute_name-error" hidden>Institute name required</span>
            </div>

            <!-- Email -->
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2" for="institute_email">Email</label>
                <input class="w-full p-2 border rounded hover:scale-105 duration-300" 
                       id="institute_email" name="institute_email" type="email" 
                       value="<?= htmlspecialchars($_SESSION['form']['institute_email'] ?? '') ?>" required>
                <span class="text-red-500 text-sm" id="institute_email-error" hidden>Invalid email format</span>
            </div>

            <!-- Phone -->
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2" for="institute_phone">Contact Number</label>
                <input class="w-full p-2 border rounded hover:scale-105 duration-300" 
                       id="institute_phone" name="institute_phone" type="tel" 
                       value="<?= htmlspecialchars($_SESSION['form']['institute_phone'] ?? '') ?>" required>
                <span class="text-red-500 text-sm" id="institute_phone-error" hidden>10-digit number required</span>
            </div>

            <!-- Address -->
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2" for="institute_adrs">Location</label>
                <input class="w-full p-2 border rounded hover:scale-105 duration-300" 
                       id="institute_adrs" name="institute_adrs" type="text" 
                       value="<?= htmlspecialchars($_SESSION['form']['institute_adrs'] ?? '') ?>" required>
                <span class="text-red-500 text-sm" id="institute_adrs-error" hidden>Address required</span>
            </div>

            <!-- Username -->
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2" for="institute_uname">Institute Username</label>
                <input class="w-full p-2 border rounded hover:scale-105 duration-300" 
                       id="institute_uname" name="institute_uname" type="text" 
                       value="<?= htmlspecialchars($_SESSION['form']['institute_uname'] ?? '') ?>" required>
                <span class="text-red-500 text-sm" id="institute_uname-error" hidden>Username required</span>
            </div>

            <!-- Password -->
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Password</label>
                <div class="relative">
                    <input type="password" id="institute_pass" name="institute_pass" 
                           class="w-full p-2 border rounded pr-10 hover:scale-105 duration-300" required>
                    <button type="button" id="toggle-pass" class="absolute inset-y-0 right-0 px-3 py-2 text-sm">
                        Show
                    </button>
                </div>
                <span class="text-red-500 text-sm" id="institute_pass-error" hidden>Password requirements not met</span>
                <div class="text-sm text-gray-600 mt-2">
                    <div class="mb-1">Password must contain:</div>
                    <div class="space-y-1">
                        <div id="req-length" class="flex items-center gap-2">
                            <span class="text-red-500">✗</span>
                            <span>Minimum 6 characters (<span id="length-counter">0/6</span>)</span>
                        </div>
                        <div id="req-uppercase" class="flex items-center gap-2">
                            <span class="text-red-500">✗</span>
                            <span>1 uppercase letter</span>
                        </div>
                        <div id="req-lowercase" class="flex items-center gap-2">
                            <span class="text-red-500">✗</span>
                            <span>1 lowercase letter</span>
                        </div>
                        <div id="req-number" class="flex items-center gap-2">
                            <span class="text-red-500">✗</span>
                            <span>1 number</span>
                        </div>
                        <div id="req-symbol" class="flex items-center gap-2">
                            <span class="text-red-500">✗</span>
                            <span>1 symbol</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Confirm Password -->
            <div class="mb-6">
                <label class="block text-gray-700 font-bold mb-2">Confirm Password</label>
                <div class="relative">
                    <input type="password" id="cnf_pass" name="cnf_pass" 
                           class="w-full p-2 border rounded pr-10 hover:scale-105 duration-300" required>
                    <button type="button" id="toggle-cnf-pass" class="absolute inset-y-0 right-0 px-3 py-2 text-sm">
                        Show
                    </button>
                </div>
                <span class="text-red-500 text-sm" id="cnf_pass-error" hidden>Passwords do not match</span>
            </div>

            <div class="flex items-center justify-center mb-4">
                <button class="bg-blue-950 text-white py-2 px-4 rounded hover:bg-gray-800 w-full" type="submit">
                    Submit
                </button>
            </div>
        </form>
    </div>
</body>
</html>
<?php 
$db->close();
unset($_SESSION['form']);
?>