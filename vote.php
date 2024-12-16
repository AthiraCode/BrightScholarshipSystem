<?php
require 'database_config.php';
require 'vendor/autoload.php'; // Include PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['vote'])) {
        $votedStudentNumber = $_POST['vote'];

        try {
            // Update votes in the database
            $updateVoteSql = "UPDATE applicantsDataStore 
                              SET Votes = Votes + 1 
                              WHERE StudentNumber = :studentNumber";
            $stmt = $pdo->prepare($updateVoteSql);
            $stmt->execute(['studentNumber' => $votedStudentNumber]);

            // Fetch updated tied candidates
            $fetchTiedCandidatesSql = "SELECT StudentNumber, FirstName, LastName, Votes 
                                       FROM applicantsDataStore 
                                       WHERE Votes > 0 
                                       ORDER BY Votes DESC";
            $stmt = $pdo->query($fetchTiedCandidatesSql);
            $tiedCandidates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error during voting: " . $e->getMessage());
        }
    } elseif (isset($_POST['voting_done'])) {
        try {
            // Fetch the student with the highest votes
            $fetchWinnerSql = "SELECT StudentNumber, FirstName, LastName, Votes, EmailAddress 
                               FROM applicantsDataStore 
                               WHERE Votes = (SELECT MAX(Votes) FROM applicantsDataStore)";
            $stmt = $pdo->query($fetchWinnerSql);
            $winner = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($winner) {
                // Insert the winner into AwardedDataStore
                $insertAwardeeSql = "INSERT INTO AwardedDataStore (StudentNumber, FirstName, LastName)
                                     VALUES (:studentNumber, :firstName, :lastName)";
                $stmt = $pdo->prepare($insertAwardeeSql);
                $stmt->execute([
                    'studentNumber' => $winner['StudentNumber'],
                    'firstName' => $winner['FirstName'],
                    'lastName' => $winner['LastName']
                ]);

                // Send email to the winner
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'abcd@gmail.com';
                    $mail->Password = 'abcd efgh ijkl nops';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    // Email settings
                    $mail->setFrom('abcd@gmail.com', 'Bright Scholarship Program');
                    $mail->addAddress($winner['EmailAddress']);
                    $mail->Subject = 'Congratulations on Receiving the Bright Scholarship!';
                    $mail->Body = "Dear {$winner['FirstName']},\n\nCongratulations! You have been awarded the Bright Scholarship. Your hard work and dedication have truly paid off.\n\nBest regards,\nBright Scholarship Committee";

                    $mail->send();
                    $message = "Winner has been awarded and notified via email!";
                } catch (Exception $e) {
                    $message = "Could not send email. Error: {$mail->ErrorInfo}";
                }
            } else {
                $message = "No winner found.";
            }
        } catch (PDOException $e) {
            die("Error finalizing the award: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voting Results</title>
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
        main {
            max-width: 800px;
            margin: 20px auto;
            background-color: white;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
    </style>
</head>
<body>
    <header>
        <h1>Voting Results</h1>
    </header>
    <main>
        <?php if (isset($message)): ?>
            <p><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <?php if (!empty($tiedCandidates)): ?>
            <h2>Tied Candidates</h2>
            <form method="POST">
                <table>
                    <tr>
                        <th>Student Number</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Votes</th>
                        <th>Action</th>
                    </tr>
                    <?php foreach ($tiedCandidates as $candidate): ?>
                        <tr>
                            <td><?= htmlspecialchars($candidate['StudentNumber']) ?></td>
                            <td><?= htmlspecialchars($candidate['FirstName']) ?></td>
                            <td><?= htmlspecialchars($candidate['LastName']) ?></td>
                            <td><?= htmlspecialchars($candidate['Votes']) ?></td>
                            <td>
                                <button type="submit" name="vote" value="<?= htmlspecialchars($candidate['StudentNumber']) ?>">
                                    Vote
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </form>
        <?php endif; ?>

        <form method="POST">
            <button type="submit" name="voting_done">Voting Done</button>
        </form>
    </main>
</body>
</html>
