<?php
require 'config.php';

session_start(); // Start the session

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    try {
        // Check the username in both teacher_tbl and student_tbl
        $teacherStmt = $pdo->prepare("SELECT teacher_id, username, teacher_password FROM teacher_tbl WHERE username = ?");
        $studentStmt = $pdo->prepare("SELECT student_id, username, student_password, ach_last_login, ach_streak FROM student_tbl WHERE username = ?");

        // Execute both queries
        $teacherStmt->execute([$username]);
        $studentStmt->execute([$username]);

        $teacher = $teacherStmt->fetch(PDO::FETCH_ASSOC);
        $student = $studentStmt->fetch(PDO::FETCH_ASSOC);

        // Check if the user exists in either table and verify the password
        if ($teacher && password_verify($password, $teacher['teacher_password'])) {
            // Teacher login
            $_SESSION['username'] = $teacher['username'];
            $_SESSION['role'] = 1; // Role 1 for Teacher
            $_SESSION['loggedin'] = true;
            $_SESSION['teacher_id'] = $teacher['teacher_id']; // Store teacher_id for later use
            header("Location: teacher_dashboard.php");
            exit();
        } elseif ($student && password_verify($password, $student['student_password'])) {
            // Student login
            // Check the streak
            $lastLogin = $student['ach_last_login'];
            $currentStreak = $student['ach_streak'];

            $today = new DateTime(); // Current date
            $yesterday = new DateTime('-1 day'); // Yesterday's date

            if ($lastLogin) {
                $lastLoginDate = new DateTime($lastLogin);
                if ($lastLoginDate->format('Y-m-d') == $yesterday->format('Y-m-d')) {
                    // Increase the streak by 1
                    $currentStreak++;
                } else if ($lastLoginDate->format('Y-m-d') != $today->format('Y-m-d')) {
                    // Reset the streak if not logged in yesterday
                    $currentStreak = 0;
                }
            } else {
                // First login or no previous date
                $currentStreak = 1;
            }

            // Update last login and streak in the database
            $updateStmt = $pdo->prepare("UPDATE student_tbl SET ach_last_login = ?, ach_streak = ? WHERE student_id = ?");
            $updateStmt->execute([$today->format('Y-m-d'), $currentStreak, $student['student_id']]);

            $_SESSION['username'] = $student['username'];
            $_SESSION['role'] = 2; // Role 2 for Student
            $_SESSION['loggedin'] = true;
            $_SESSION['student_id'] = $student['student_id']; // Store student_id for later use
            header("Location: student_dashboard.php");
            exit();
        } else {
            $_SESSION['login_error'] = "Invalid username or password.";
            header("Location: login.php");
            exit();
        }
    } catch (PDOException $e) {
        die("ERROR: Could not connect. " . $e->getMessage());
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="css/login.css" rel="stylesheet">
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <div class="login-container">
        <div class="logo-section">
            <img src="images/logo/pq_logo.webp" id="pq_logo" alt="PeerQuest Logo">
            <img src="images/logo/pq_logo_txt.webp" id="pq_logo_txt" alt="PeerQuest Logo Text">
        </div>

        <div class="login-card">
            <h2 class="login-title">Login</h2>
            <form method="post" action="login.php">
                <div class="form-group">
                    <label for="username">E-MAIL OR USERNAME</label>
                    <input type="text" class="form-control" id="username" name="username" placeholder="Enter your e-mail or username" required>
                </div>

                <div class="form-group">
                    <label for="password">PASSWORD</label>
                    <div class="password-container">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                        <i class="toggle-password fas fa-eye-slash" id="togglePassword"></i>
                    </div>
                </div>


                <div class="form-options">
                    <label>
                        <input type="checkbox" name="remember"> Remember me
                    </label>
                    <a href="forgot_password.php" class="forgot-password">FORGOT PASSWORD?</a>
                </div>

                <div id="error-message" class="error-message hidden">
                    <?php 
                        if (isset($_SESSION['login_error'])) {
                            echo $_SESSION['login_error'];
                            unset($_SESSION['login_error']); // Clear the error after displaying
                        }
                    ?>
                </div>


                <p></p>
                <div class="form-group">
                    <button type="submit" class="btn-primary">Login</button>
                </div>
                <p class="register-link">Don't have an account? <a href="signup.php">Register here</a>.</p>
            </form>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const passwordField = document.getElementById('password');
        const togglePassword = document.getElementById('togglePassword');
        const errorMessageDiv = document.getElementById('error-message');
        const form = document.querySelector('form');

        // Toggle password visibility
        togglePassword.addEventListener('click', function () {
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                togglePassword.classList.remove('fa-eye-slash');
                togglePassword.classList.add('fa-eye');
            } else {
                passwordField.type = 'password';
                togglePassword.classList.remove('fa-eye');
                togglePassword.classList.add('fa-eye-slash');
            }
        });

        // Show error message with animation if it exists
        if (errorMessageDiv && errorMessageDiv.innerText.trim() !== "") {
            errorMessageDiv.classList.remove('hidden');
            errorMessageDiv.classList.add('shake');
            setTimeout(() => {
                errorMessageDiv.classList.remove('shake');
            }, 500);
        }

        // Client-side form validation
        form.addEventListener('submit', function(event) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value.trim();

            if (username === '' || password === '') {
                event.preventDefault();
                showError('Please fill in all required fields.');
            }
        });

        function showError(message) {
            errorMessageDiv.textContent = message;
            errorMessageDiv.classList.remove('hidden');
            errorMessageDiv.classList.add('shake');
            setTimeout(() => {
                errorMessageDiv.classList.remove('shake');
            }, 500);
        }
    });
</script>


</body>
</html>
