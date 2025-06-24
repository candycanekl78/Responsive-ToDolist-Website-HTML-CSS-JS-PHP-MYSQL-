<?php include('server.php'); ?>
<?php
session_start();

$successMessage = '';
if (isset($_SESSION['success'])) {
    $successMessage = $_SESSION['success'];
    unset($_SESSION['success']); // Clear it after displaying
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | To-Do List</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="container">
        <!-- Left Section -->
        <div class="login-box">
            <h1>Welcome Back</h1>
            <p>Log in to manage your tasks efficiently.</p>

            <?php if (!empty($successMessage)): ?>
                <p style="color: green; font-weight: bold; text-align: center;"><?php echo $successMessage; ?></p>
            <?php endif; ?>

            <form method="post" action="login.php">
                <?php include('errors.php'); ?>

                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" placeholder="Enter your email" required>

                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>

                <div class="remember-container">
                    <div class="left-section">
                        <input type="checkbox" id="remember" name="remember">
                        <label for="remember">Remember Me</label>
                    </div>
                    <a href="forgot_password.php" class="forgot-password">Forgot password</a>
                </div>

                <button type="submit" name="login_user" class="login-btn">Sign In</button>

                <div class="or">OR</div>

            </form>

            <p class="signup-text">Don’t have an account? <a href="../signup/register.php">Sign up</a></p>
        </div>

        <!-- Right Section (Illustration) -->
        <div class="illustration">
            <img src="todo-illustration1.jpg" alt="To-Do List">
        </div>
    </div>
</body>
</html>
