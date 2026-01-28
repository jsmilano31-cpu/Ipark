<?php
require_once 'auth.php';
require_once 'db_connect.php';

// Require user authentication
requireUser();

// Set JSON response header
header('Content-Type: application/json');

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get and validate reservation ID
$reservation_id = filter_input(INPUT_POST, 'reservation_id', FILTER_VALIDATE_INT);
if (!$reservation_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid reservation ID']);
    exit;
}

try {
    // Start transaction
    $conn->beginTransaction();

    // Check if reservation exists and belongs to the user
    $stmt = $conn->prepare("
        SELECT r.*, p.id as slot_id 
        FROM reservations r 
        JOIN parking_slots p ON r.parking_slot_id = p.id 
        WHERE r.id = ? AND r.user_id = ? AND r.status = 'Pending'
    ");
    $stmt->execute([$reservation_id, $_SESSION['user_id']]);
    $reservation = $stmt->fetch();

    if (!$reservation) {
        throw new Exception('Reservation not found or cannot be cancelled');
    }

    // Update reservation status
    $stmt = $conn->prepare("UPDATE reservations SET status = 'Cancelled' WHERE id = ?");
    $stmt->execute([$reservation_id]);

    // Update parking slot status back to Vacant
    $stmt = $conn->prepare("UPDATE parking_slots SET status = 'Vacant' WHERE id = ?");
    $stmt->execute([$reservation['slot_id']]);

    // Commit transaction
    $conn->commit();

    echo json_encode(['success' => true, 'message' => 'Reservation cancelled successfully']);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollBack();
    
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?> 