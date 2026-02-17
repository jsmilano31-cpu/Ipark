<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = $_POST['password'];

    try {
        $stmt = $conn->prepare("SELECT id, username, password FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['is_admin'] = true;
            header('Location: admin/dashboard.php');
            exit();
        } else {
            $_SESSION['admin_error'] = 'Invalid username or password';
            header('Location: admin_login.php');
            exit();
        }
    } catch(PDOException $e) {
        $_SESSION['admin_error'] = 'Login failed. Please try again.';
        header('Location: admin_login.php');
        exit();
    }
} else {
    header('Location: admin_login.php');
    exit();
}
?> 