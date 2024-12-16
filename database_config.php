<?php
// Database configuration
$host = 'localhost'; // Change this to your database host
$dbname = 'bright_scholarship'; // Change this to your database name
$username = 'phpuser'; // Change this to your database username
$password = 'pa55word'; // Change this to your database password, if any

try {
    // Set up the PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    // If there's an error, display it
    echo "Connection failed: " . $e->getMessage();
    die();
}
?>
