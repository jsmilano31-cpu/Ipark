<?php
require_once 'auth.php';
require_once 'db_connect.php';

// Require user authentication
requireUser();

// Get last message ID from query parameter
$lastId = filter_input(INPUT_GET, 'last_id', FILTER_VALIDATE_INT);
if ($lastId === false) {
    $lastId = 0;
}

try {
    // Fetch new messages
    $stmt = $conn->prepare("
        SELECT m.*, 
               CASE 
                   WHEN m.is_from_user THEN u.first_name 
                   ELSE a.username 
               END as sender_name,
               CASE 
                   WHEN m.is_from_user THEN u.profile_picture 
                   ELSE NULL 
               END as sender_avatar,
               CASE 
                   WHEN m.is_from_user THEN CONCAT(UPPER(LEFT(u.first_name, 1)), UPPER(LEFT(u.last_name, 1)))
                   ELSE NULL 
               END as sender_initials
        FROM messages m
        LEFT JOIN users u ON m.user_id = u.id
        LEFT JOIN admins a ON m.admin_id = a.id
        WHERE m.user_id = ? AND m.id > ?
        ORDER BY m.created_at ASC
    ");
    $stmt->execute([$_SESSION['user_id'], $lastId]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Format messages for response
    $formattedMessages = array_map(function($message) {
        return [
            'id' => $message['id'],
            'message' => $message['message'],
            'is_from_user' => (bool)$message['is_from_user'],
            'sender_name' => $message['sender_name'],
            'sender_avatar' => $message['sender_avatar'],
            'sender_initials' => $message['sender_initials'],
            'created_at' => date('M d, Y h:i A', strtotime($message['created_at']))
        ];
    }, $messages);
    
    // Mark unread admin messages as read
    if (!empty($messages)) {
        $stmt = $conn->prepare("
            UPDATE messages 
            SET is_read = TRUE 
            WHERE user_id = ? AND is_from_user = FALSE AND is_read = FALSE
        ");
        $stmt->execute([$_SESSION['user_id']]);
    }
    
    echo json_encode(['success' => true, 'messages' => $formattedMessages]);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error fetching messages']);
}
?> 