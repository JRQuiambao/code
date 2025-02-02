<?php
require 'config.php';

// Handle AJAX request to check username availability
if (isset($_POST['check_username'])) {
    $username = trim($_POST['username']);

    if (!preg_match('/^[a-zA-Z]+$/', $username)) {
        echo "invalid";
        exit();
    }

    $stmt = $pdo->prepare("SELECT * FROM teacher_tbl WHERE username = ?
                           UNION
                           SELECT * FROM student_tbl WHERE username = ?");
    $stmt->execute([$username, $username]);

    if ($stmt->rowCount() > 0) {
        echo "exists";
    } else {
        echo "available";
    }
    exit();
}

// Handle AJAX request to check email availability
if (isset($_POST['check_email'])) {
    $email = trim($_POST['email']);

    $stmt = $pdo->prepare("SELECT * FROM teacher_tbl WHERE teacher_email = ?
                           UNION
                           SELECT * FROM student_tbl WHERE student_email = ?");
    $stmt->execute([$email, $email]);

    if ($stmt->rowCount() > 0) {
        echo "exists";
    } else {
        echo "available";
    }
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $role = (int) $_POST['role'];


    // Validate first name and last name (only alphabets)
    if (!preg_match('/^[a-zA-Z]+$/', $first_name)) {
        die("First name must contain only alphabets.");
    }

    if (!preg_match('/^[a-zA-Z]+$/', $last_name)) {
        die("Last name must contain only alphabets.");
    }

    // Validate username (only alphabets)
    if (!preg_match('/^[a-zA-Z]+$/', $username)) {
        die("Username must contain only alphabets.");
    }

    // Check if passwords match
    if ($password !== $confirm_password) {
        die("Passwords do not match.");
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format.");
    }

    // Password hashing for security
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {
        // Insert based on role
        if ($role === 1) { // Role 1 = Teacher
            $stmt = $pdo->prepare("INSERT INTO teacher_tbl (teacher_first_name, teacher_last_name, username, teacher_email, teacher_password) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$first_name, $last_name, $username, $email, $hashed_password]);
        } elseif ($role === 2) { // Role 2 = Student
            $stmt = $pdo->prepare("INSERT INTO student_tbl (student_first_name, student_last_name, username, student_email, student_password) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$first_name, $last_name, $username, $email, $hashed_password]);
        } else {
            die("Invalid role selected.");
        }

        // Redirect to login page after successful signup
        header("Location: login.php");
        exit();
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
    <title>Create Account</title>
    <link rel="stylesheet" href="css/signup.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <div class="signup-card">
        <h2 class="signup-title">Create Account</h2>
        <form action="signup.php" method="POST">

        <div class="form-row">
            <div class="form-group">
                <label for="first_name">First Name</label>
                <input type="text" class="form-control" id="first_name" name="first_name" placeholder="Enter your first name" required>
            </div>

            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Enter your last name" required>
            </div>
        </div>

            
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username" required>
            </div>
            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Enter your e-mail" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <div class="password-container">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                    <i class="toggle-password fas fa-eye-slash" id="togglePassword1" onclick="togglePassword('password', 'togglePassword1')"></i>
                </div>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <div class="password-container">
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Re-enter your password" required>
                    <i class="toggle-password fas fa-eye-slash" id="togglePassword2" onclick="togglePassword('confirm_password', 'togglePassword2')"></i>
                </div>
            </div>




            <div class="form-group">
                <label for="role">Are you a teacher or a student?</label>
                <select class="form-control" id="role" name="role" required>
                    <option value="" disabled selected>Select your role</option>
                    <option value="1">I'm a teacher</option>
                    <option value="2">I'm a student</option>
                </select>
            </div>
            <button type="submit" class="btn-primary">Create Account</button>
        </form>
        <p class="register-link">Already have an account? <a href="login.php">Login here</a></p>
        </div>

   


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<script>

function togglePassword(fieldId, iconId) {
    var passwordField = document.getElementById(fieldId);
    var eyeIcon = document.getElementById(iconId);

    if (passwordField.type === "password") {
        passwordField.type = "text";
        eyeIcon.classList.remove("fa-eye-slash");
        eyeIcon.classList.add("fa-eye");
    } else {
        passwordField.type = "password";
        eyeIcon.classList.remove("fa-eye");
        eyeIcon.classList.add("fa-eye-slash");
    }
}



    // JavaScript function to check if inputs are valid
    function validateInputs() {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        if (password !== confirmPassword) {
            alert("Passwords do not match.");
            return false;
        }
        return true;
    }

    // Validate first name, last name, and username (only alphabets)
    const alphaRegex = /^[a-zA-Z]+$/;

    document.getElementById('first_name').addEventListener('input', function () {
        const firstName = this.value;
        const feedback = document.getElementById('first-name-feedback');
        if (!alphaRegex.test(firstName)) {
            feedback.textContent = "First name must contain only alphabets.";
        } else {
            feedback.textContent = "";
        }
    });

    document.getElementById('last_name').addEventListener('input', function () {
        const lastName = this.value;
        const feedback = document.getElementById('last-name-feedback');
        if (!alphaRegex.test(lastName)) {
            feedback.textContent = "Last name must contain only alphabets.";
        } else {
            feedback.textContent = "";
        }
    });

    document.getElementById('username').addEventListener('input', function () {
        const username = this.value;
        const feedback = document.getElementById('username-feedback');
        if (!alphaRegex.test(username)) {
            feedback.textContent = "Username must contain only alphabets.";
        } else {
            feedback.textContent = "";
        }
    });

    $(document).ready(function() {
        // Check username availability
        $('#username').on('input', function() {
            var username = $(this).val();
            checkUsernameAvailability(username);
        });

        // Check email availability
        $('#email').on('input', function() {
            var email = $(this).val();
            checkEmailAvailability(email);
        });

        // Function to check username availability
        function checkUsernameAvailability(username) {
            $.ajax({
                url: 'signup.php',
                type: 'POST',
                data: {
                    check_username: true,
                    username: username
                },
                success: function(response) {
                    if (response === "exists") {
                        $('#username-feedback').text("This username already exists.");
                    } else if (response === "invalid") {
                        $('#username-feedback').text("Username must contain only alphabets.");
                    } else {
                        $('#username-feedback').text("");
                    }
                }
            });
        }

        // Function to check email availability
        function checkEmailAvailability(email) {
            $.ajax({
                url: 'signup.php',
                type: 'POST',
                data: {
                    check_email: true,
                    email: email
                },
                success: function(response) {
                    if (response === "exists") {
                        $('#email-feedback').text("This email already exists.");
                    } else {
                        $('#email-feedback').text("");
                    }
                }
            });
        }
    });
    
</script>
</body>
</html>
