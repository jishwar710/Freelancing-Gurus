<?php
// InstituteProfile.php
session_start();

if (!isset($_SESSION['institute_uname'])) {
    header('Location: InstituteLogin.php');
    exit();
}

$db = new mysqli('localhost', 'root', '', 'freelance');
$errors = [];
$success = '';

// Fetch profile data
$current_uname = $_SESSION['institute_uname'];
$stmt = $db->prepare("SELECT * FROM institute_details WHERE institute_uname = ?");
$stmt->bind_param("s", $current_uname);
$stmt->execute();
$result = $stmt->get_result();
$profile = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $form = [];
    foreach ($fields as $field) {
        $form[$field] = htmlspecialchars(trim($_POST[$field] ?? ''));
    }
    $new_password = !empty($form['institute_pass']) ? $form['institute_pass'] : null;

    // Validation
    if (empty($form['institute_name'])) $errors[] = "Institute name required";
    if (!filter_var($form['institute_email'], FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
    if (!preg_match('/^\d{10}$/', $form['institute_phone'])) $errors[] = "Phone must be 10 digits";
    if (empty($form['institute_adrs'])) $errors[] = "Address required";

    // Username change check
    if ($form['institute_uname'] !== $current_uname) {
        $check_stmt = $db->prepare("SELECT institute_uname FROM institute_details WHERE institute_uname = ?");
        $check_stmt->bind_param("s", $form['institute_uname']);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows > 0) {
            $errors[] = "Username already taken";
        }
        $check_stmt->close();
    }

    // Password validation if provided
    if ($new_password) {
        if (strlen($new_password) < 6 || 
            !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{6,}$/', $new_password)) {
            $errors[] = "Password must be 6+ chars with 1 uppercase, 1 lowercase, 1 number, and 1 symbol";
        }
        if ($new_password !== trim($_POST['cnf_pass'] ?? '')) $errors[] = "Passwords do not match";
    }

    if (empty($errors)) {
        // Build dynamic update query
        $query = "UPDATE institute_details SET 
                  institute_name = ?, institute_email = ?, institute_phone = ?, 
                  institute_adrs = ?, institute_uname = ?";
        $params = [
            $form['institute_name'],
            $form['institute_email'],
            $form['institute_phone'],
            $form['institute_adrs'],
            $form['institute_uname']
        ];
        $types = "sssss";

        // Add password if changed
        if ($new_password) {
            $query .= ", institute_pass = ?";
            $params[] = $new_password;
            $types .= "s";
        }

        $query .= " WHERE institute_uname = ?";
        $params[] = $current_uname;
        $types .= "s";

        $update_stmt = $db->prepare($query);
        $update_stmt->bind_param($types, ...$params);

        if ($update_stmt->execute()) {
            $success = "Profile updated successfully!";
            $_SESSION['institute_uname'] = $form['institute_uname'];
            // Refresh profile data
            $profile = array_merge($profile, $form);
        } else {
            $errors[] = "Update failed: " . $update_stmt->error;
        }

        $update_stmt->close();
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Institute Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Same JavaScript as registration page
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
    <!-- Same header as registration page -->
    <header class="w-full">
        <div class="mx-auto flex justify-between h-16 bg-white w-full max-w-full">
            <a rel="noopener noreferrer" href="#" aria-label="Back to homepage" class="flex items-center p-2">
                <h1 class="font-serif text-3xl text-sky-700">FreelancingGurus</h1>
            </a>
            <ul class="items-stretch hidden space-x-3 mr-5 md:flex">
                
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

    <div class="max-w-xl mx-auto mt-10 bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="text-2xl py-4 px-6 bg-blue-950 text-white text-center font-bold uppercase">
            Institute Profile
        </div>

        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 text-red-700 p-3 m-4 rounded">
                <?php foreach ($errors as $error): ?>
                    <?= $error ?><br>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="bg-green-100 text-green-700 p-3 m-4 rounded">
                <?= $success ?>
            </div>
        <?php endif; ?>

        <form class="py-4 px-6" method="POST">
            <!-- Institute Name -->
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Institute Name</label>
                <input class="w-full p-2 border rounded hover:scale-105 duration-300" 
                    name="institute_name" value="<?= htmlspecialchars($profile['institute_name']) ?>" readonly>
            </div>

            <!-- Email -->
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Email</label>
                <input class="w-full p-2 border rounded hover:scale-105 duration-300" 
                    name="institute_email" type="email" value="<?= htmlspecialchars($profile['institute_email']) ?>" readonly>
            </div>

            <!-- Phone -->
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Contact Number</label>
                <input class="w-full p-2 border rounded hover:scale-105 duration-300" 
                    name="institute_phone" value="<?= htmlspecialchars($profile['institute_phone']) ?>" readonly>
            </div>

            <!-- Address -->
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Location</label>
                <input class="w-full p-2 border rounded hover:scale-105 duration-300" 
                    name="institute_adrs" value="<?= htmlspecialchars($profile['institute_adrs']) ?>" readonly>
            </div>

            <!-- Username -->
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Username</label>
                <input class="w-full p-2 border rounded hover:scale-105 duration-300" 
                    name="institute_uname" value="<?= htmlspecialchars($profile['institute_uname']) ?>" readonly>
            </div>

            <!-- Password -->
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">New Password (leave blank to keep current)</label>
                <div class="relative">
                    <input type="password" id="institute_pass" name="institute_pass" 
                        class="w-full p-2 border rounded pr-10 hover:scale-105 duration-300">
                    <button type="button" id="toggle-pass" class="absolute inset-y-0 right-0 px-3 py-2 text-sm">
                        Show
                    </button>
                </div>
                <!-- Include same password requirements from registration -->
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
            


            <!-- Confirm Password -->
            <div class="mb-6">
                <label class="block text-gray-700 font-bold mb-2">Confirm New Password</label>
                <div class="relative">
                    <input type="password" id="cnf_pass" name="cnf_pass" 
                        class="w-full p-2 border rounded pr-10 hover:scale-105 duration-300">
                    <button type="button" id="toggle-cnf-pass" class="absolute inset-y-0 right-0 px-3 py-2 text-sm">
                        Show
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-center mb-4">
                <button class="bg-blue-950 text-white py-2 px-4 rounded hover:bg-gray-800 w-full" type="submit">
                    Update Profile
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