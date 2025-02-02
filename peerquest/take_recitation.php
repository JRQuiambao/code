<?php
require 'auth.php';
require 'config.php';

$assessment_id = $_GET['assessment_id'] ?? null;

// AJAX Request Handling
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['ajax']) && $_GET['ajax'] === 'true') {
    if (!$assessment_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid assessment ID.']);
        exit();
    }

    // Fetch the latest question and revealed student
    $stmt = $pdo->prepare("SELECT qr.question_text, s.student_first_name, s.student_last_name
        FROM questions_reci_tbl qr
        LEFT JOIN student_tbl s ON qr.revealed_student_id = s.student_id
        WHERE qr.assessment_id = ?
        ORDER BY qr.created_at DESC
        LIMIT 1");
    $stmt->execute([$assessment_id]);
    $latest_question = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($latest_question) {
        $response = [
            'success' => true,
            'question_text' => $latest_question['question_text'],
            'student_name' => isset($latest_question['student_first_name'], $latest_question['student_last_name']) 
                ? $latest_question['student_first_name'] . ' ' . $latest_question['student_last_name']
                : null
        ];
    } else {
        $response = [
            'success' => true,
            'question_text' => null,
            'student_name' => null
        ];
    }

    echo json_encode($response);
    exit();
}

// Fetch assessment details (initial load)
if (!$assessment_id) {
    echo "Invalid assessment ID.";
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM assessment_tbl WHERE assessment_id = ?");
$stmt->execute([$assessment_id]);
$assessment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$assessment) {
    echo "Done Recitation ";
    echo '<a href="student_dashboard.php">
    <button type="button">Go to Dashboard</button>
  </a>';
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Take Assessment</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* General Styles */
        body {
            background: linear-gradient(135deg, #f5f7fa, #c3cfe2); /* Soft gradient background */
            font-family: 'Arial', sans-serif;
            color: #333;
        }

        /* Question Display */
        .question-display {
            font-size: 1.5rem;
            font-weight: bold;
            color: #4a4a4a;
            background: #ffffff;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            margin-bottom: 20px;
        }

        .no-question {
            color: #a0a0a0;
            font-style: italic;
        }

        /* Student Name Display */
        .student-name {
            font-size: 1.25rem;
            color: #4a90e2; /* Soft blue */
            margin-top: 10px;
            background: #ffffff;
            padding: 15px;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        /* Balloon Container */
        .balloon-container {
            position: relative;
            height: 400px;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 20px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        /* Balloon Styles */
        .balloon {
            position: absolute;
            width: 100px;
            height: 120px;
            background: linear-gradient(135deg, #ff9a9e, #fad0c4); /* Pastel gradient */
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            animation: float 6s infinite ease-in-out;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .balloon:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
        }

        @keyframes float {
            0% { transform: translateY(100vh) rotate(0deg); }
            100% { transform: translateY(-100vh) rotate(360deg); }
        }

        .balloon.popped {
            animation: pop 0.5s forwards;
        }

        @keyframes pop {
            0% { transform: scale(1); }
            50% { transform: scale(1.2); }
            100% { transform: scale(0); }
        }

        /* Button Styles */
        .btn-primary {
            background: linear-gradient(135deg, #6a11cb, #2575fc); /* Vibrant gradient */
            border: none;
            border-radius: 25px;
            padding: 10px 20px;
            font-size: 1rem;
            color: white;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body class="container my-4">
    <!-- Navigation -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">Take Assessment</h1>
        <a href="student_dashboard.php" class="btn btn-primary">Home</a>
    </div>

    <!-- Assessment Details -->
    <div class="mb-4">
        <h2 class="h4">Assessment: <?php echo htmlspecialchars($assessment['name']); ?></h2>
        <p class="text-muted">Type: <?php echo htmlspecialchars($assessment['type']); ?></p>
    </div>

    <!-- Question Display -->
    <div class="alert alert-secondary question-display no-question" id="questionDisplay">
        Waiting for the teacher to show a question...
    </div>

    <!-- Revealed Student -->
    <div class="alert alert-info student-name" id="studentNameDisplay">
        No student revealed yet.
    </div>

    <!-- Balloon Animation -->
    <div class="balloon-container" id="balloonContainer">
        <!-- Balloons will be dynamically generated here -->
    </div>

    <script>
        // Function to fetch the latest question and revealed student
        function fetchLatestData() {
            $.ajax({
                url: 'take_recitation.php',
                type: 'GET',
                data: {
                    ajax: 'true',
                    assessment_id: <?php echo htmlspecialchars($assessment_id); ?>
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Update the question display
                        $('#questionDisplay').text(response.question_text || "Waiting for the teacher to show a question...");
                        $('#questionDisplay').toggleClass('no-question', !response.question_text);

                        // Update the revealed student display
                        if (response.student_name) {
                            $('#studentNameDisplay').text("It's your turn: " + response.student_name);
                        } else {
                            $('#studentNameDisplay').text("No student revealed yet.");
                        }

                        // Generate balloons with the fetched student name
                        generateBalloons(response.student_name);
                    }
                },
                error: function() {
                    console.error("Failed to fetch the latest recitation data.");
                }
            });
        }

        // Function to create a balloon with a student's name
        function createBalloon(studentName) {
            const balloon = document.createElement('div');
            balloon.className = 'balloon';
            balloon.textContent = studentName || "?";
            balloon.addEventListener('click', function() {
                balloon.classList.add('popped');
                setTimeout(() => {
                    balloon.remove();
                }, 500);
            });
            return balloon;
        }

        // Function to generate balloons
        function generateBalloons(studentName) {
            const balloonContainer = document.getElementById('balloonContainer');
            balloonContainer.innerHTML = ''; // Clear existing balloons

            if (studentName) {
                const balloon = createBalloon(studentName);
                balloon.style.left = `${Math.random() * 80}%`; // Random horizontal position
                balloon.style.animationDuration = `${Math.random() * 4 + 4}s`; // Random speed
                balloonContainer.appendChild(balloon);
            }
        }

        // Poll the server every 5 seconds
        setInterval(fetchLatestData, 5000);

        // Fetch data immediately on page load
        fetchLatestData();
    </script>
</body>
</html>