<?php
session_start();

// Enable error reporting (for development only)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize variables
$fname = $lname = $email = "";
$errors = array();

// Connect to the database
$db = mysqli_connect('localhost', 'root', '', 'todolist'); // Update credentials if needed
if (!$db) {
    die("Database connection failed: " . mysqli_connect_error());
}

// If the register button is clicked
if (isset($_POST['reg_user'])) {
    // Sanitize inputs
    $fname = mysqli_real_escape_string($db, $_POST['fname']);
    $lname = mysqli_real_escape_string($db, $_POST['lname']);
    $email = mysqli_real_escape_string($db, $_POST['email']);
    $password = mysqli_real_escape_string($db, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($db, $_POST['confirm_password']);
    $security_question = mysqli_real_escape_string($db, $_POST['security_question']);
    $security_answer = mysqli_real_escape_string($db, $_POST['security_answer']);

    // Validation
    if (empty($fname)) array_push($errors, "First name is required");
    if (empty($lname)) array_push($errors, "Last name is required");
    if (empty($email)) array_push($errors, "Email is required");
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) array_push($errors, "Invalid email format");
    if (empty($password)) array_push($errors, "Password is required");
    if (empty($security_question)) $_SESSION['register_errors'][] = "Security question is required";
    if (empty($security_answer)) $_SESSION['register_errors'][] = "Security answer is required";
    if ($password != $confirm_password) array_push($errors, "Passwords do not match");

    // Check if user already exists
    $user_check_query = "SELECT * FROM users WHERE email='$email' LIMIT 1";
    $result = mysqli_query($db, $user_check_query);
    $user = mysqli_fetch_assoc($result);

    if ($user) {
        if ($user['email'] === $email) {
            array_push($errors, "Email already exists");
        }
    }

    // Register user if no errors
    if (count($errors) === 0) {
        $password_hashed = password_hash($password, PASSWORD_DEFAULT); // Secure password
        $query = "INSERT INTO users (first_name, last_name, email, password) 
                  VALUES('$fname', '$lname', '$email', '$password_hashed')";

        if (mysqli_query($db, $query)) {
            $_SESSION['email'] = $email;
            $_SESSION['success'] = "You are now registered!";
            header('Location: ../signin/login.php?message=registered');
            exit();
        } else {
            array_push($errors, "Error: " . mysqli_error($db));
        }
    }

    // Store errors in session so they can be shown in register.php
    $_SESSION['register_errors'] = $errors;
    header('Location: register.php');
    exit();
}
?>
