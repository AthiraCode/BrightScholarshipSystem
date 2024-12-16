<?php
require 'database_config.php';

try {
    // Fetch eligible students
    $sql = "SELECT StudentNumber, FirstName, LastName, CumulativeGPA, Status, DateOfBirth, Gender
            FROM applicantsDataStore
            WHERE EligibilityStatus = 'Eligible'";
    $stmt = $pdo->query($sql);
    $eligibleStudents = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // If no eligible students, show a message
    if (empty($eligibleStudents)) {
        $message = "No eligible students found.";
    } else {
        // Sort by GPA descending, then by status (Junior first), then by gender (Female first), and finally by Date of Birth (youngest first)
        usort($eligibleStudents, function ($a, $b) {
            if ($a['CumulativeGPA'] != $b['CumulativeGPA']) {
                return $b['CumulativeGPA'] <=> $a['CumulativeGPA'];
            }
            if ($a['Status'] != $b['Status']) {
                return $a['Status'] == 'Junior' ? -1 : 1;
            }
            if ($a['Gender'] != $b['Gender']) {
                return $a['Gender'] == 'Female' ? -1 : 1;
            }
            return strtotime($b['DateOfBirth']) <=> strtotime($a['DateOfBirth']);
        });

        // Identify the winner or determine ties
        $highestGPA = $eligibleStudents[0]['CumulativeGPA'];
        $topCandidates = array_filter($eligibleStudents, function ($student) use ($highestGPA) {
            return $student['CumulativeGPA'] == $highestGPA;
        });

        if (count($topCandidates) == 1) {
            $winner = reset($topCandidates); // Single winner
        } else {
            // Handle tie-breaking logic
            $message = "Tie detected among candidates. Admin needs to cast votes.";
            $tiedCandidates = array_slice($topCandidates, 0, 2); // Take the youngest two for simplicity
        }
    }
} catch (PDOException $e) {
    die("Error determining awardee: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Awardee</title>
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
        button {
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
        <h1>Find Awardee</h1>
    </header>
    <main>
        <?php if (isset($message)): ?>
            <p><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <?php if (isset($winner)): ?>
            <h2>Awardee</h2>
            <table>
                <tr>
                    <th>Student Number</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Cumulative GPA</th>
                    <th>Status</th>
                    <th>Gender</th>
                </tr>
                <tr>
                    <td><?= htmlspecialchars($winner['StudentNumber']) ?></td>
                    <td><?= htmlspecialchars($winner['FirstName']) ?></td>
                    <td><?= htmlspecialchars($winner['LastName']) ?></td>
                    <td><?= htmlspecialchars($winner['CumulativeGPA']) ?></td>
                    <td><?= htmlspecialchars($winner['Status']) ?></td>
                    <td><?= htmlspecialchars($winner['Gender']) ?></td>
                </tr>
            </table>
        <?php elseif (isset($tiedCandidates)): ?>
            <h2>Tied Candidates</h2>
            <form method="POST" action="vote.php">
                <table>
                    <tr>
                        <th>Student Number</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Cumulative GPA</th>
                        <th>Status</th>
                        <th>Gender</th>
                        <th>Vote</th>
                    </tr>
                    <?php foreach ($tiedCandidates as $candidate): ?>
                        <tr>
                            <td><?= htmlspecialchars($candidate['StudentNumber']) ?></td>
                            <td><?= htmlspecialchars($candidate['FirstName']) ?></td>
                            <td><?= htmlspecialchars($candidate['LastName']) ?></td>
                            <td><?= htmlspecialchars($candidate['CumulativeGPA']) ?></td>
                            <td><?= htmlspecialchars($candidate['Status']) ?></td>
                            <td><?= htmlspecialchars($candidate['Gender']) ?></td>
                            <td>
                                <button type="submit" name="vote" value="<?= htmlspecialchars($candidate['StudentNumber']) ?>">Vote</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </form>
        <?php endif; ?>
    </main>
</body>
</html>
