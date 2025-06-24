<?php
session_start();
include('server.php');

if (!isset($_SESSION['reset_user_id'])) {
    header('Location: forgot_password.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['reset_password'])) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    if ($password !== $confirm_password) {
        $_SESSION['error'] = "Passwords don't match";
        header('Location: reset_password_form.php');
        exit();
    }
    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $user_id = $_SESSION['reset_user_id'];
    
    $query = "UPDATE users SET password=? WHERE id=?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "si", $hashed_password, $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['success'] = "Password updated successfully!";
        unset($_SESSION['reset_user_id']);
        unset($_SESSION['security_question']);
        header('Location: login.php');
        exit();
    } else {
        $_SESSION['error'] = "Error updating password";
        header('Location: reset_password_form.php');
        exit();
    }
}

header('Location: forgot_password.php');
exit();
?>