<?php
session_start();

// Database connection
$db = mysqli_connect('localhost', 'root', '', 'todolist'); // Make sure 'login' is your database name

$errors = array();

// Check if form is submitted
if (isset($_POST['login_user'])) {
    $email = mysqli_real_escape_string($db, $_POST['email']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE email='$email'";
    $results = mysqli_query($db, $query);

    if (mysqli_num_rows($results) == 1) {
        $user = mysqli_fetch_assoc($results);
        if (password_verify($password, $user['password'])) {
            // Store user info in session
            $_SESSION['user_id'] = $user['id'];  // Store the user id in the session (new addition)
            $_SESSION['username'] = $user['username']; // assuming 'username' exists in DB
            $_SESSION['email'] = $user['email'];
            $_SESSION['success'] = "You are now logged in";
            header('location: ../dashboard/dashboard.php');
            exit();
        } else {
            array_push($errors, "Wrong email or password");
        }
    } else {
        array_push($errors, "Wrong email or password");
    }
}
?>
