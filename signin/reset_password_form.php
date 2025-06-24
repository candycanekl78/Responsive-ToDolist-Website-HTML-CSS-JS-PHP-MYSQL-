<?php
session_start();
include('server.php');

if (!isset($_SESSION['reset_user_id'])) {
    header('Location: forgot_password.php');
    exit();
}

$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<!-- [Keep the rest of your HTML exactly as is] -->