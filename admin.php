<?php
require 'database_config.php';

// Fetch eligible students from the database
try {
    $sql = "SELECT StudentNumber, FirstName, LastName, CumulativeGPA, Status, DateOfBirth 
            FROM applicantsDataStore 
            WHERE EligibilityStatus = 'Eligible'";
    $stmt = $pdo->query($sql);
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching eligible students: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Eligible Students</title>
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
        <h1>Eligible Students</h1>
    </header>
    <main>
        <table>
            <thead>
                <tr>
                    <th>Student Number</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Cumulative GPA</th>
                    <th>Status</th>
                    <th>Date of Birth</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($students)): ?>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?= htmlspecialchars($student['StudentNumber']) ?></td>
                            <td><?= htmlspecialchars($student['FirstName']) ?></td>
                            <td><?= htmlspecialchars($student['LastName']) ?></td>
                            <td><?= htmlspecialchars($student['CumulativeGPA']) ?></td>
                            <td><?= htmlspecialchars($student['Status']) ?></td>
                            <td><?= htmlspecialchars($student['DateOfBirth']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">No eligible students found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <button onclick="window.location.href='find_awardee.php'">Find Awardee</button>
    </main>
</body>
</html>
