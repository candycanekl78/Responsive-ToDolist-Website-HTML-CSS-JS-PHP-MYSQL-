<?php
session_start();

// Database connection
$db = mysqli_connect('localhost', 'root', '', 'todolist');
if (!$db) {
    die("Database connection failed: " . mysqli_connect_error());
}

$errors = [];
$successMessage = '';
$step = isset($_GET['step']) ? $_GET['step'] : 1;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['verify_email'])) {
        // Step 1: Verify email
        $email = mysqli_real_escape_string($db, $_POST['email']);
        
        if (empty($email)) {
            $errors[] = "Email is required";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Invalid email format";
        } else {
            $query = "SELECT id, email FROM users WHERE email='$email' LIMIT 1";
            $result = mysqli_query($db, $query);
            
            if (mysqli_num_rows($result) === 1) {
                $_SESSION['reset_email'] = $email;
                $_SESSION['reset_user_id'] = mysqli_fetch_assoc($result)['id'];
                header('Location: forgot_password.php?step=2');
                exit();
            } else {
                $errors[] = "Email not found in our system";
            }
        }
    } 
    elseif (isset($_POST['reset_password'])) {
        // Step 2: Reset password
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (empty($password)) {
            $errors[] = "Password is required";
        }
        if (empty($confirm_password)) {
            $errors[] = "Confirm password is required";
        }
        if ($password !== $confirm_password) {
            $errors[] = "Passwords do not match";
        }
        
        if (empty($errors) && isset($_SESSION['reset_user_id'])) {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $user_id = $_SESSION['reset_user_id'];
            
            $query = "UPDATE users SET password='$password_hash' WHERE id='$user_id'";
            if (mysqli_query($db, $query)) {
                $successMessage = "Password updated successfully!";
                unset($_SESSION['reset_email']);
                unset($_SESSION['reset_user_id']);
                $step = 3; // Show success message
            } else {
                $errors[] = "Database error: " . mysqli_error($db);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | To-Do List</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="container">
        <!-- Left Section -->
        <div class="login-box">
            <h1>Reset Your Password</h1>
            
            <?php if (!empty($successMessage)): ?>
                <p style="color: green; font-weight: bold; text-align: center;"><?php echo $successMessage; ?></p>
                <p class="signup-text"><a href="login.php">Back to login</a></p>
            <?php elseif ($step == 1): ?>
                <p>Enter your email to verify your account.</p>
                
                <?php if (!empty($errors)): ?>
                    <div class="error">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo $error; ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <form method="post" action="forgot_password.php">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" placeholder="Enter your registered email" required>
                    
                    <button type="submit" name="verify_email" class="login-btn">Continue</button>
                    
                    <p class="signup-text">Remember your password? <a href="login.php">Sign in</a></p>
                </form>
                
            <?php elseif ($step == 2): ?>
                <p>Enter your new password below.</p>
                
                <?php if (!empty($errors)): ?>
                    <div class="error">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo $error; ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <form method="post" action="forgot_password.php?step=2">
                    <label for="password">New Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter new password" required>
                    
                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" required>
                    
                    <button type="submit" name="reset_password" class="login-btn">Reset Password</button>
                    
                    <p class="signup-text"><a href="login.php">Back to login</a></p>
                </form>
            <?php endif; ?>
        </div>
        
        <!-- Right Section (Illustration) -->
        <div class="illustration">
            <img src="todo-illustration1.jpg" alt="To-Do List">
        </div>
    </div>
</body>
</html>