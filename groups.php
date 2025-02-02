<?php
require 'auth.php';
require 'config.php';

$assessment_id = $_GET['assessment_id'] ?? null;

if (!$assessment_id) {
    echo "Invalid assessment ID.";
    exit();
}

// Check if the user is authorized (e.g., teacher or admin)
if ($_SESSION['role'] != 1) { // Assuming role 1 is for teachers
    echo "Access denied.";
    exit();
}

// Fetch all room_id(s) for the given assessment_id
$stmt = $pdo->prepare("SELECT DISTINCT room_id FROM room_ready_tbl WHERE assessment_id = ?");
$stmt->execute([$assessment_id]);
$rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$rooms) {
    echo "No groups found for this assessment.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Group View for Assessment</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f4f4f4;
        }
    </style>
</head>
<body>
    <h1>Group View for Assessment ID: <?php echo htmlspecialchars($assessment_id); ?></h1>

    <?php foreach ($rooms as $room): ?>
        <?php
        $room_id = $room['room_id'];

        // Fetch users in the current room_id with status "started"
        $stmt = $pdo->prepare(
            "SELECT u.student_id, u.username 
             FROM room_ready_tbl r
             JOIN student_tbl u ON r.student_id = u.student_id
             WHERE r.room_id = ? AND r.status = 'started'"
        );
        $stmt->execute([$room_id]);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        ?>

        <h2>Room ID: <?php echo htmlspecialchars($room_id); ?></h2>
        <?php if (!empty($students)): ?>
            <p>Total Students in the Room: <?php echo count($students); ?></p>
            <table>
                <thead>
                    <tr>
                        
                        <th>Name</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            
                            <td><?php echo htmlspecialchars($student['username']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No students found in this room with status "started".</p>
        <?php endif; ?>
    <?php endforeach; ?>
    <button onclick="history.back()">Go Back</button>
</body>
</html>
