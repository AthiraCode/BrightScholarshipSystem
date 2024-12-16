<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
require 'database_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize input
    $studentNumber = htmlspecialchars(trim($_POST['student-number']));
    $firstName = htmlspecialchars(trim($_POST['first-name']));
    $lastName = htmlspecialchars(trim($_POST['last-name']));
    $phoneNumber = htmlspecialchars(trim($_POST['phone-number']));
    $email = htmlspecialchars(trim($_POST['email']));
    $gender = htmlspecialchars(trim($_POST['gender']));
    $dob = htmlspecialchars(trim($_POST['dob']));
    $status = htmlspecialchars(trim($_POST['status']));
    $gpa = (float) $_POST['gpa'];
    $creditHours = (int) $_POST['credit-hours'];

    // Validate required fields
    if (empty($studentNumber) || empty($firstName) || empty($lastName) || empty($email) || empty($gpa) || empty($creditHours)) {
        echo "<h2>Error</h2><p>All fields are required.</p>";
        exit;
    }

    // Validate GPA and Credit Hours
    if ($gpa < 0 || $gpa > 4) {
        echo "<h2>Error</h2><p>GPA must be between 0 and 4.</p>";
        exit;
    }
    if ($creditHours < 1) {
        echo "<h2>Error</h2><p>Credit Hours must be 1 or more.</p>";
        exit;
    }

    try {
        // Check eligibility
        $isEligible = ($gpa >= 3.5 && $creditHours >= 12);
        $eligibilityStatus = $isEligible ? 'Eligible' : 'Not Eligible';
        $reason = $isEligible ? 'Meets the criteria' : 'Does not meet the GPA or Credit Hour requirements';

        // Save the data into the database
        $sql = "INSERT INTO applicantsDataStore 
                (StudentNumber, FirstName, LastName, PhoneNumber, EmailAddress, Gender, DateOfBirth, Status, CumulativeGPA, CreditHours, EligibilityStatus, Reason)
                VALUES (:studentNumber, :firstName, :lastName, :phoneNumber, :email, :gender, :dob, :status, :gpa, :creditHours, :eligibilityStatus, :reason)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':studentNumber' => $studentNumber,
            ':firstName' => $firstName,
            ':lastName' => $lastName,
            ':phoneNumber' => $phoneNumber,
            ':email' => $email,
            ':gender' => $gender,
            ':dob' => $dob,
            ':status' => $status,
            ':gpa' => $gpa,
            ':creditHours' => $creditHours,
            ':eligibilityStatus' => $eligibilityStatus,
            ':reason' => $reason
        ]);

        // Prepare eligibility message
        $message = $isEligible
            ? "Congratulations, $firstName! You are eligible for the Bright Scholarship."
            : "Thank you for your application, $firstName. Unfortunately, you do not meet the eligibility criteria for the Bright Scholarship.";

        // Send email
        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'abcd@gmail.com'; // Your Gmail address
            $mail->Password = 'abcd efgh ijkl nops'; // Your app-specific password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('abcd@gmail.com', 'Bright Scholarship');
            $mail->addAddress($email);

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Bright Scholarship Application Status';
            $mail->Body = $message;

            $mail->send();
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }

        // Return styled HTML
        echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eligibility Result</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #4CAF50;
            color: white;
            text-align: center;
            padding: 1em 0;
        }
        footer {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 1em 0;
            position: fixed;
            width: 100%;
            bottom: 0;
        }
        main {
            max-width: 600px;
            margin: 30px auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .result-box {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        button {
            margin-top: 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 4px;
            font-size: 16px;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <header>
        <h1>Eligibility Result</h1>
    </header>
    <main>
        <div id="eligibility-result" class="result-box">
            $message
        </div>
        <button onclick="window.location.href='index.html'">Go Back</button>
    </main>
    <footer>
        <p>&copy; 2024 Bright Scholarship Program</p>
    </footer>
</body>
</html>
HTML;

    } catch (PDOException $e) {
        echo "<h2>Error</h2><p>Database error: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<h2>Error</h2><p>Invalid request method.</p>";
}
?>
