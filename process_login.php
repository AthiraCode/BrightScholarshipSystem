<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = ""; // Default password for XAMPP
$dbname = "bright_scholarship";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $inputUsername = $_POST['username'];
    $inputPassword = $_POST['password'];

    // Fetch user details
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $inputUsername);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Verify the password (for now, using plaintext comparison)
        if ($inputPassword === $user['password']) {
            // Successful login
            session_start();
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header("Location: application_form.html"); // Redirect to the application form
            exit();
        } else {
            echo "Invalid password.";
        }
    } else {
        echo "No user found with that username.";
    }
}
?>
