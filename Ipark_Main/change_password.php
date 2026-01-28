<?php
require_once 'auth.php';
require_once 'db_connect.php';
requireUser();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: profile.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$current_password = $_POST['current_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if ($new_password !== $confirm_password) {
    $_SESSION['error'] = 'New passwords do not match.';
    header('Location: profile.php');
    exit;
}
if (strlen($new_password) < 6) {
    $_SESSION['error'] = 'Password must be at least 6 characters.';
    header('Location: profile.php');
    exit;
}

try {
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    if (!$user || !password_verify($current_password, $user['password'])) {
        $_SESSION['error'] = 'Current password is incorrect.';
        header('Location: profile.php');
        exit;
    }
    $hashed = password_hash($new_password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hashed, $user_id]);
    $_SESSION['success'] = 'Password changed successfully!';
} catch(PDOException $e) {
    $_SESSION['error'] = 'Error changing password.';
}
header('Location: profile.php');
exit; 