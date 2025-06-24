<?php
session_start();
include('server.php');

if (!isset($_SESSION['reset_user_id']) || !isset($_POST['security_answer'])) {
    $_SESSION['error'] = "Invalid request";
    header('Location: forgot_password.php');
    exit();
}

$answer = mysqli_real_escape_string($conn, $_POST['security_answer']);
$user_id = $_SESSION['reset_user_id'];

$query = "SELECT id FROM users WHERE id=? AND security_answer=?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "is", $user_id, $answer);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 1) {
    header('Location: reset_password_form.php');
    exit();
} else {
    $_SESSION['error'] = "Incorrect security answer";
    header('Location: verify_security_question.php');
    exit();
}
?>