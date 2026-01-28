<?php
require_once '../auth.php';
require_once '../db_connect.php';

// Require admin authentication
requireAdmin();

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get and validate inputs
$userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
$message = trim($_POST['message'] ?? '');

if (!$userId || empty($message)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input']);
    exit;
}

try {
    // Verify user exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit;
    }

    // Insert message into database
    $stmt = $conn->prepare("
        INSERT INTO messages (user_id, admin_id, message, is_from_user)
        VALUES (?, ?, ?, FALSE)
    ");
    $stmt->execute([$userId, $_SESSION['admin_id'], $message]);
    
    // Get the inserted message ID
    $messageId = $conn->lastInsertId();
    
    // Fetch admin data for response
    $stmt = $conn->prepare("
        SELECT username 
        FROM admins 
        WHERE id = ?
    ");
    $stmt->execute([$_SESSION['admin_id']]);
    $admin = $stmt->fetch();
    
    // Prepare response data
    $response = [
        'success' => true,
        'message_id' => $messageId,
        'message' => $message,
        'sender_name' => $admin['username'],
        'created_at' => date('M d, Y h:i A')
    ];
    
    echo json_encode($response);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error sending message']);
}
?> 