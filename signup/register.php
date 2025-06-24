<?php include('server.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Sign Up | To-Do List</title>
  <link rel="stylesheet" href="signup.css"/>
</head>
<body>
  <div class="container">
    <!-- Left Section -->
    <div class="login-box">
      <br>
      <h1>Create Account</h1>
      <p>Register to manage your tasks efficiently.</p>

      <!-- ✅ Include error messages -->
      <?php include('errors.php'); ?>

      <!-- ✅ Make sure action is this file itself -->
      <form method="post" action="register.php">
        <div class="input-row">
          <div class="input-group">
            <label for="fname">First Name</label>
            <input type="text" name="fname" placeholder="Enter your First Name" value="<?php echo isset($_POST['fname']) ? htmlspecialchars($_POST['fname']) : ''; ?>" required>
          </div>

          <div class="input-group">
            <label for="lname">Last Name</label>
            <input type="text" name="lname" placeholder="Enter your Last Name" value="<?php echo isset($_POST['lname']) ? htmlspecialchars($_POST['lname']) : ''; ?>" required>
          </div>
        </div>

        <div class="input-row">
          <div class="input-group">
            <label for="email">Email Address</label>
            <input type="email" name="email" placeholder="Enter your Email Address" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
          </div>

          <div class="input-group">
            <label for="password">Password</label>
            <input type="password" name="password" placeholder="Enter your Password" required>
          </div>
        </div>

        <div class="input-row">
          <div class="input-group">
            <label for="confirm_password">Confirm Your Password</label>
            <input type="password" name="confirm_password" placeholder="Enter your Password Again" required>
          </div>
        </div>
        
        <div class="input-group">
    <label for="security_question">Security Question</label>
    <select id="security_question" name="security_question" required>
        <option value="">Select a security question</option>
        <option value="What was your first pet's name?">What was your first pet's name?</option>
        <option value="What city were you born in?">What city were you born in?</option>
        <option value="What is your mother's maiden name?">What is your mother's maiden name?</option>
        <option value="What was the name of your first school?">What was the name of your first school?</option>
    </select>
</div>

<div class="input-group">
    <label for="security_answer">Security Answer</label>
    <input type="text" id="security_answer" name="security_answer" placeholder="Enter your answer" required>
</div>

        <div class="remember-container">
          <div class="left-section">
            <input type="checkbox" id="remember">
            <label for="remember">Remember Me</label>
          </div>
          <a href="#" class="forgot-password">Forgot password</a>
        </div>

        <button type="submit" name="reg_user" class="login-btn">Sign Up</button>

       
      </form>

      <p class="signup-text">Already have an account? <a href="../signin/login.php">Sign In</a></p>
      <br>
    </div>

    <!-- Right Section -->
    <div class="illustration">
      <img src="todo-illustration1.jpg" alt="To-Do List">
    </div>
  </div>
</body>
</html>
