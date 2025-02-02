<?php
require 'auth.php';
require 'config.php';



// Get `assessment_id` and `class_id` from query parameters
$assessment_id = $_GET['assessment_id'] ?? null;
$class_id = $_GET['class_id'] ?? null;

if (!$assessment_id || !$class_id) {
    echo "Invalid or missing assessment ID or class ID.";
    exit();
}

// Fetch all students in the same class
$stmt = $pdo->prepare("SELECT s.student_id, s.student_first_name, s.student_last_name
                       FROM student_classes sc
                       JOIN student_tbl s ON sc.student_id = s.student_id
                       WHERE sc.class_id = ?");
$stmt->execute([$class_id]);
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Initialize leaderboard data
$leaderboard = [];

foreach ($students as $student) {
    $student_id = $student['student_id'];
    $total_grade = 0;

    // Fetch grades for Essay
    $stmt = $pdo->prepare("SELECT SUM(grade) AS total_grade FROM answers_esy_tbl 
                           WHERE assessment_id = ? AND student_id = ?");
    $stmt->execute([$assessment_id, $student_id]);
    $essay_grade = $stmt->fetchColumn();
    $total_grade += $essay_grade ?: 0;

    // Fetch grades for Multiple Choice (Individual)
    $stmt = $pdo->prepare("SELECT SUM(correct_answer) AS total_grade FROM answers_mcq_tbl 
                           WHERE assessment_id = ? AND student_id = ?");
    $stmt->execute([$assessment_id, $student_id]);
    $mcq_grade = $stmt->fetchColumn();
    $total_grade += $mcq_grade ?: 0;

    // Fetch grades for True/False
    $stmt = $pdo->prepare("SELECT SUM(correct_answer) AS total_grade FROM answers_tf_tbl 
                           WHERE assessment_id = ? AND student_id = ?");
    $stmt->execute([$assessment_id, $student_id]);
    $tf_grade = $stmt->fetchColumn();
    $total_grade += $tf_grade ?: 0;

    // Collaborative Multiple Choice grades (optional, if applicable)
    $stmt = $pdo->prepare("SELECT SUM(grades) AS total_grade FROM answers_mcq_collab_tbl 
                           WHERE assessment_id = ? AND submitted_by = ?");
    $stmt->execute([$assessment_id, $student_id]);
    $mcq_collab_grade = $stmt->fetchColumn();
    $total_grade += $mcq_collab_grade ?: 0;

    // Add student data to the leaderboard
    $leaderboard[] = [
        'student_id' => $student_id,
        'name' => $student['student_first_name'] . ' ' . $student['student_last_name'],
        'total_grade' => $total_grade
    ];
}

// Sort leaderboard by total grades in descending order
usort($leaderboard, function ($a, $b) {
    return $b['total_grade'] <=> $a['total_grade'];
});
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard</title>
    <link rel="stylesheet" href="css/leaderboard.css">
</head>
<body>
    <div class="leaderboard-container">
        <h1>🏆 Leaderboard 🏆</h1>
        <h3>Assessment ID: <?php echo htmlspecialchars($assessment_id); ?></h3>
        <h3>Class ID: <?php echo htmlspecialchars($class_id); ?></h3>

        <table class="leaderboard-table">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Student ID</th>
                    <th>Name</th>
                    <th>Total Grade</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($leaderboard)): ?>
                    <?php foreach ($leaderboard as $rank => $student): ?>
                        <tr class="rank-<?php echo $rank + 1; ?>">
                            <td>
                                <?php if ($rank == 0): ?>
                                    <img src="images/icons/crown_gold.webp" alt="Gold Crown" class="crown-icon">
                                <?php elseif ($rank == 1): ?>
                                    <img src="images/icons/crown_silver.webp" alt="Silver Crown" class="crown-icon">
                                <?php elseif ($rank == 2): ?>
                                    <img src="images/icons/crown_bronze.webp" alt="Bronze Crown" class="crown-icon">
                                <?php endif; ?>
                                <span><?php echo $rank + 1; ?></span>
                            </td>
                            <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                            <td class="name-cell"><?php echo htmlspecialchars($student['name']); ?></td>
                            <td><?php echo htmlspecialchars($student['total_grade']); ?> pts</td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">No data available.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="buttons">
            <button onclick="history.back()">Go Back</button>
            <a href="student_dashboard.php" class="btn">Dashboard</a>
        </div>
    </div>
</body>
</html>
