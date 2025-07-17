<?php
session_start();
// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "freelance";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  
    // Validate session data
    if (isset($_SESSION['username']) || isset($_SESSION['email'])) {
        $uname = htmlspecialchars($_SESSION['username']);
        $email = htmlspecialchars($_SESSION['email']);
    } elseif (isset($_SESSION['institute_name']) && isset($_SESSION['institute_email'])) {
        $uname = $_SESSION['institute_name'];
        $email = $_SESSION['institute_email'];
    } else {
        $_SESSION['feedback_error'] = "Please log in to submit feedback.";
        header("Location: feedback_form.php");
        exit();
    }

    // Validate input data
    if (!isset($_POST['feedback-type']) || empty($_POST['feedback-type'])) {
        $_SESSION['feedback_error'] = "Please select a rating.";
        header("Location: feedback_form.php");
        exit();
    }

    // Sanitize inputs
    $rating = htmlspecialchars(trim($_POST['feedback-type']));
    $feedback_text = isset($_POST['feedback-text']) ? htmlspecialchars(trim($_POST['feedback-text'])) : "";
    $created_at = date('Y-m-d H:i:s');

    // Prepare and bind the SQL statement
    $stmt = $conn->prepare("INSERT INTO feedback (username, email, rating, feedback_text, created_at) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $uname, $email, $rating, $feedback_text, $created_at);

    // Execute the statement
    if ($stmt->execute()) {
        $_SESSION['feedback_message'] = "Feedback submitted successfully!";
    } else {
        $_SESSION['feedback_error'] = "Error submitting feedback: " . $stmt->error;
    }

    // Close connections and redirect
    $stmt->close();
    $conn->close();
    header("Location: feedback_form.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Feedback Form</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-cyan-50">
<header class="w-full  ">
        <div class="mx-auto flex justify-between h-16 bg-white w-full max-w-full">
            <a rel="noopener noreferrer" href="#" aria-label="Back to homepage" class="flex items-center p-2">
                <h1 class="font-serif text-3xl text-sky-700">FreelancingGurus</h1>
            </a>
            <ul class="items-stretch hidden space-x-3 mr-5 md:flex ">
            <li class="flex">
        <?php if(isset($_SESSION['username'])): ?>
            <!-- Freelancer Profile -->
            <a rel="noopener noreferrer" href="./freelancer/FreelancerAccount.php" 
               class="flex items-center px-4 -mb-1 border-b-2 border-transparent hover:border-blue-500 transition duration-300 ease-in-out font-semibold">
               Dashboard
            </a>
        <?php elseif(isset($_SESSION['institute_name'])): ?>
            <!-- Institute Profile -->
            <a rel="noopener noreferrer" href="./institute/instituteAccount.php" 
               class="flex items-center px-4 -mb-1 border-b-2 border-transparent hover:border-blue-500 transition duration-300 ease-in-out font-semibold">
                Dashboard
            </a>
        <?php endif; ?>
    </li>
          
                <li class="flex">
                    <a rel="noopener noreferrer" href="aboutus.html"
                        class="flex items-center px-4 -mb-1 border-b-2 border-transparent hover:border-blue-500 transition duration-300 ease-in-out font-semibold">About
                        Us</a>
                </li>
            </ul>
        </div>
    </header>

 
  <!-- Feedback Messages -->
  <div class="container mx-auto px-4 max-w-3xl">
    <?php if (isset($_SESSION['feedback_message'])): ?>
      <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4 rounded-lg shadow-md" role="alert">
        <div class="flex justify-between items-center">
          <p><?= htmlspecialchars($_SESSION['feedback_message']) ?></p>
          <button onclick="this.parentElement.parentElement.remove()" class="text-green-700 hover:text-green-900">
            <span class="text-2xl">&times;</span>
          </button>
        </div>
      </div>
      <?php unset($_SESSION['feedback_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['feedback_error'])): ?>
      <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4 rounded-lg shadow-md" role="alert">
        <div class="flex justify-between items-center">
          <p><?= htmlspecialchars($_SESSION['feedback_error']) ?></p>
          <button onclick="this.parentElement.parentElement.remove()" class="text-red-700 hover:text-red-900">
            <span class="text-2xl">&times;</span>
          </button>
        </div>
      </div>
      <?php unset($_SESSION['feedback_error']); ?>
    <?php endif; ?>
  </div>
  <!-- Feedback Form Section -->
  <div class="bg-white shadow-xl p-6 max-w-3xl mx-auto mt-8 rounded-3xl border border-gray-200">
    <h2 class="text-3xl font-semibold text-gray-800 mb-4">We Value Your Feedback</h2>
    <p class="text-lg text-gray-600 mb-4">Please provide your feedback to help us improve. Your opinion matters!</p>

    <form method="post">
      <!-- Radio Buttons: Feedback Type -->
      <div class="mb-6">
        <p class="text-gray-700 text-lg font-semibold mb-3">How would you rate your experience?</p>
        <div class="flex space-x-6">
          <div class="flex items-center space-x-3">
            <input type="radio" id="positive" name="feedback-type" value="positive" class="h-5 w-5 text-blue-500 focus:ring-0" required>
            <label for="positive" class="text-gray-700 text-sm">Positive</label>
          </div>
          <div class="flex items-center space-x-3">
            <input type="radio" id="neutral" name="feedback-type" value="neutral" class="h-5 w-5 text-yellow-500 focus:ring-0" required>
            <label for="neutral" class="text-gray-700 text-sm">Neutral</label>
          </div>
          <div class="flex items-center space-x-3">
            <input type="radio" id="negative" name="feedback-type" value="negative" class="h-5 w-5 text-red-500 focus:ring-0" required>
            <label for="negative" class="text-gray-700 text-sm">Negative</label>
          </div>
        </div>
      </div>

      <!-- Feedback Text -->
      <div class="mb-6">
        <label for="feedback-text" class="block text-gray-700 text-lg font-semibold mb-2">Your Feedback (Optional)</label>
        <textarea id="feedback-text" name="feedback-text" rows="6" class="w-full p-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" placeholder="Please provide your feedback here..."></textarea>
      </div>

      <!-- Submit Button -->
      <div class="text-center">
        <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-full hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-300 transition-all ease-in-out duration-300">Submit Feedback</button>
      </div>
    </form>
  </div>
   <!-- Optional Alpine.js for better message dismissal -->
   <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
