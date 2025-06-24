<?php include('server.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Password Recovery</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>
    <div class="container">
        <div class="login-box">
            <h1>Password Recovery</h1>
            
            <?php if (!isset($_POST['verify_answer'])): ?>
            <!-- STEP 1: Email Verification -->
            <form method="post">
                <div class="input-group">
                    <label>Enter Your Email</label>
                    <input type="email" name="email" required>
                </div>
                <button type="submit" name="find_user" class="login-btn">Continue</button>
            </form>
            
            <?php else: ?>
            <!-- STEP 2: Security Question -->
            <form method="post">
                <input type="hidden" name="email" value="<?= htmlspecialchars($_POST['email']) ?>">
                
                <div class="input-group">
                    <label>Security Question</label>
                    <p><strong><?= htmlspecialchars($_SESSION['security_question']) ?></strong></p>
                </div>
                
                <div class="input-group">
                    <label>Your Answer</label>
                    <input type="text" name="user_answer" required>
                </div>
                
                <?php if (isset($_SESSION['attempt_failed'])): ?>
                    <p class="error"><?= $_SESSION['attempt_failed'] ?></p>
                    <?php unset($_SESSION['attempt_failed']); ?>
                <?php endif; ?>
                
                <button type="submit" name="verify_answer" class="login-btn">Verify</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>