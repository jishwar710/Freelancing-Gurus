<?php
session_start();
require "../vendor/autoload.php";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
// DB Config
$db = new mysqli('localhost', 'root', '', 'freelance');

$errors = [];
$fields = ['name', 'email', 'phone', 'quali', 'uni', 'uid', 'pwd', 'con_pwd'];
$form = array_fill_keys($fields, '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($fields as $field) {
        $form[$field] = htmlspecialchars(trim($_POST[$field] ?? ''));
    }

    // Validation
    $stat = "active";
    if (!$form['name']) $errors[] = "Name required";
    if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email";
    if (!preg_match('/^\d{10}$/', $form['phone'])) $errors[] = "Invalid phone";
    if (!$form['quali']) $errors[] = "Qualification required";
    if (!$form['uni']) $errors[] = "University required";
    if (!$form['uid']) $errors[] = "Username required";
    if (strlen($form['pwd']) < 6 || !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{6,}$/', $form['pwd'])) {
        $errors[] = "Password must be at least 6 characters with 1 uppercase, 1 lowercase, 1 number, and 1 symbol";
    }
    if ($form['pwd'] !== $form['con_pwd']) $errors[] = "Passwords mismatch";

    if (empty($errors)) {
        $stmt = $db->prepare("INSERT INTO free_user (flancer_name, flancer_email, flancer_phone, flancer_qualification, flancer_uni, flancer_uname, flancer_pass, flancer_TS, status) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?)");
        $stmt->bind_param("ssssssss", $form['name'], $form['email'], $form['phone'], $form['quali'], $form['uni'], $form['uid'], $form['pwd'], $stat);;
        
        if ($stmt->execute()) {
            
            $mail = new PHPMailer(true);

            $mail->isSMTP();                            
            $mail->Host = 'smtp.gmail.com '; 
            $mail->SMTPAuth = true;                     
            $mail->Username = 'freelancinggurus0@gmail.com';                
            $mail->Password = 'dybh ixsw dxyi vekv';                         
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // recipients
            $mail->setFrom('freelancinggurus0@gmail.com', 'Freelancing Gurus');
            $mail->addAddress($form['email'], $form['name']);     // Add a recipient

            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = 'Job application';
            $mail->Subject = 'Welcome to Freelancing Gurus - Registration Successful';
            $mail->Body    = '
            <!DOCTYPE html>
            <html>
            <head>
                <style>
                    @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap");
                </style>
            </head>
            <body style="font-family: sans-serif; margin: 0; padding: 0; background-color: #f3f4f6;">
                <div style="max-width: 600px; margin: 20px auto; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <header style="background: #1e293b; padding: 24px; text-align: center;">
                        <img src="https://example.com/logo.png" alt="Freelancing Gurus" style="height: 40px;">
                        <h1 style="color: white; margin: 16px 0 0; font-size: 24px;">Welcome to Our Community!</h1>
                    </header>
                    
                <div style="padding: 32px 24px;">
                    <h2 style="color: #1e293b; margin: 0 0 24px; font-size: 20px;">Hi '.$form['name'].',</h2>
                    
                    <div style="background: #f8fafc; border-radius: 6px; padding: 16px; margin-bottom: 24px;">
                        <p style="margin: 0 0 12px; color: #64748b;">Your account has been successfully created. Here are your details:</p>
                
                <table style="width: 100%; border-collapse: collapse;">
                    <tr style="border-bottom: 1px solid #e2e8f0;">
                        <td style="padding: 8px 0; color: #64748b; width: 120px;">Username</td>
                        <td style="padding: 8px 0; color: #1e293b; font-weight: 500;">'.$form['uid'].'</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #e2e8f0;">
                        <td style="padding: 8px 0; color: #64748b;">Email</td>
                        <td style="padding: 8px 0; color: #1e293b; font-weight: 500;">'.$form['email'].'</td>
                    </tr>
                    <tr style="border-bottom: 1px solid #e2e8f0;">
                        <td style="padding: 8px 0; color: #64748b;">Phone</td>
                        <td style="padding: 8px 0; color: #1e293b; font-weight: 500;">'.$form['phone'].'</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #64748b;">University</td>
                        <td style="padding: 8px 0; color: #1e293b; font-weight: 500;">'.$form['uni'].'</td>
                    </tr>
                </table>
            </div>

            

            <div style="color: #64748b; font-size: 14px; line-height: 1.6;">
                <p style="margin: 0 0 8px;">Need help? Contact our support team at freelancinggurus0@gmail.com</p>
                <p style="margin: 0;">Follow us on <a href="#" style="color: #3b82f6; text-decoration: none;">Twitter</a> 
                | <a href="#" style="color: #3b82f6; text-decoration: none;">LinkedIn</a></p>
            </div>
        </div>

        <footer style="background: #f8fafc; padding: 16px; text-align: center; color: #64748b; font-size: 12px;">
            <p style="margin: 0;">© '.date('Y').' Freelancing Gurus. All rights reserved.</p>
        
        </footer>
            </div>
        </body>
        </html>';   // HTML message body
            $mail->send();
            echo '<script>alert("User registered successfully!"); window.location.href = "./FreelancerLogin.php";</script>';
            exit();
        } else {
            $errors[] = "Registration failed";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en" class="bg-cyan-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Freelancer Registration</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        const messages = {
            name: "Name required", email: "Invalid email", phone: "10-digit number required",
            quali: "Qualification required", uni: "University required", uid: "Username required",
            pwd: "Password doesn't meet requirements",
            con_pwd: "Passwords must match"
        };

        function checkPasswordRequirements(password) {
            const requirements = {
                length: password.length >= 6,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /\d/.test(password),
                symbol: /[\W_]/.test(password)
            };

            // Update requirement indicators
            Object.keys(requirements).forEach(req => {
                const indicator = document.getElementById(`req-${req}`);
                const icon = indicator.querySelector('span');
                if (requirements[req]) {
                    icon.classList.remove('text-red-500');
                    icon.classList.add('text-green-500');
                    icon.textContent = '✓';
                } else {
                    icon.classList.remove('text-green-500');
                    icon.classList.add('text-red-500');
                    icon.textContent = '✗';
                }
            });

            // Update length counter
            document.getElementById('length-counter').textContent = `${password.length}/6`;

            return Object.values(requirements).every(v => v);
        }

        function validate(field, value) {
            let valid = false;
            
            if (field === 'pwd') {
                valid = checkPasswordRequirements(value);
            } else {
                valid = {
                    name: v => v, 
                    email: v => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v),
                    phone: v => /^\d{10}$/.test(v), 
                    quali: v => v, 
                    uni: v => v,
                    uid: v => v, 
                    con_pwd: v => v === document.getElementById('pwd').value
                }[field](value);
            }

            const err = document.getElementById(`${field}-error`);
            err.hidden = valid;
            document.getElementById(field).classList.toggle('border-red-500', !valid);
            if (!valid) err.textContent = messages[field];
            return valid;
        }

        function validateAll(e) {
            e.preventDefault();
            let valid = true;
            ['name','email','phone','quali','uni','uid','pwd','con_pwd'].forEach(field => {
                if (!validate(field, document.getElementById(field).value.trim())) valid = false;
            });
            if (valid) e.target.submit();
        }

        function togglePasswordVisibility(fieldId) {
            const field = document.getElementById(fieldId);
            const toggle = document.getElementById(`toggle-${fieldId}`);
            if (field.type === 'password') {
                field.type = 'text';
                toggle.textContent = 'Hide';
            } else {
                field.type = 'password';
                toggle.textContent = 'Show';
            }
        }

        window.onload = () => {
            ['name','email','phone','quali','uni','pwd','con_pwd'].forEach(field => {
                document.getElementById(field).addEventListener('input', function() {
                    validate(field, this.value);
                });
            });
        };
    </script>
</head>
<body>
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
                    <a href="./FreelancerLogin.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Freelancer Login</a>
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

    <main class="max-w-xl mx-auto mt-10 bg-white shadow rounded-lg">
        <h2 class="bg-blue-950 text-white text-center p-4 text-xl">Freelancer Registration</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="bg-red-100 text-red-700 p-3 m-4 rounded">
                <?= implode('<br>', $errors) ?>
            </div>
        <?php endif ?>

        <form class="p-6" method="POST" onsubmit="validateAll(event)">
            <?php foreach (['name','email','phone','quali','uni','uid'] as $field): ?>
                <div class="mb-4">
                <label class="block font-bold mb-2">
                    <!-- lable values -->
                    <?= match($field) {
                        'name' => 'Full Name',
                        'email' => 'Email Address',
                        'phone' => 'Mobile Number',
                        'quali' => 'Educational Qualification',
                        'uni' => 'University/Board',
                        'uid' => 'Username',
                        default => ucfirst($field)
                    } ?>
                </label>
                    <input class="w-full p-2 border rounded" id="<?= $field ?>" name="<?= $field ?>"
                           value="<?= $form[$field] ?>" <?= $field === 'phone' ? 'type="tel"' : 'type="text"' ?>>
                    <span class="text-red-500 text-sm" id="<?= $field ?>-error" hidden></span>
                </div>
            <?php endforeach ?>

            <div class="mb-4">
                <label class="block font-bold mb-2">Password</label>
                <div class="relative">
                    <input type="password" id="pwd" name="pwd" class="w-full p-2 border rounded pr-10">
                    <button type="button" id="toggle-pwd" class="absolute inset-y-0 right-0 px-3 py-2 text-sm" 
                            onclick="togglePasswordVisibility('pwd')">Show</button>
                </div>
                <span class="text-red-500 text-sm" id="pwd-error" hidden></span>
                <div class="text-sm text-gray-600 mt-2">
                    <div class="mb-1">Password must contain:</div>
                    <div id="password-requirements" class="space-y-1">
                        <div id="req-length" class="flex items-center gap-2">
                            <span class="text-red-500">✗</span>
                            <span>Minimum 6 characters (<span id="length-counter">0/6</span>)</span>
                        </div>
                        <div id="req-uppercase" class="flex items-center gap-2">
                            <span class="text-red-500">✗</span>
                            <span>At least one uppercase letter (A-Z)</span>
                        </div>
                        <div id="req-lowercase" class="flex items-center gap-2">
                            <span class="text-red-500">✗</span>
                            <span>At least one lowercase letter (a-z)</span>
                        </div>
                        <div id="req-number" class="flex items-center gap-2">
                            <span class="text-red-500">✗</span>
                            <span>At least one number (0-9)</span>
                        </div>
                        <div id="req-symbol" class="flex items-center gap-2">
                            <span class="text-red-500">✗</span>
                            <span>At least one symbol (!@#$%^&* etc.)</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-6">
                <label class="block font-bold mb-2">Confirm Password</label>
                <div class="relative">
                    <input type="password" id="con_pwd" name="con_pwd" class="w-full p-2 border rounded pr-10">
                    <button type="button" id="toggle-con_pwd" class="absolute inset-y-0 right-0 px-3 py-2 text-sm" 
                            onclick="togglePasswordVisibility('con_pwd')">Show</button>
                </div>
                <span class="text-red-500 text-sm" id="con_pwd-error" hidden></span>
            </div>

            <button class="w-full bg-blue-950 text-white p-2 rounded hover:bg-blue-900">
                Register
            </button>
        </form>
    </main>
</body>
</html>