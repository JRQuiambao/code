<?php
require 'auth.php';
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit("Invalid request method.");
}

$assessment_id = $_POST['assessment_id'] ?? null;
$room_id = $_POST['room_id'] ?? null;
$student_id = $_SESSION['student_id'] ?? null;
$answers = $_POST['answers'] ?? [];

if (!$assessment_id || !$room_id || !$student_id || empty($answers)) {
    exit("Missing required data.");
}

// Fetch questions from questions_esy_tbl to ensure they exist
try {
    $stmt = $pdo->prepare("SELECT * FROM questions_esy_tbl WHERE assessment_id = ?");
    $stmt->execute([$assessment_id]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($questions)) {
        exit("Error: No questions found for this assessment.");
    }
} catch (PDOException $e) {
    exit("Error fetching questions: " . $e->getMessage());
}

try {
    $pdo->beginTransaction();

    foreach ($answers as $question_id => $answer_text) {
        $stmt = $pdo->prepare("INSERT INTO answers_esy_collab_tbl (assessment_id, room_id, student_id, question_id, answer_text, submitted_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$assessment_id, $room_id, $student_id, $question_id, $answer_text]);
    }

    $pdo->commit();
    echo "Assessment submitted successfully.";
} catch (PDOException $e) {
    $pdo->rollBack();
    exit("Error submitting assessment: " . $e->getMessage());
}