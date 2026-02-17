<?php
require_once '../auth.php';
require_once '../db_connect.php';

// Require admin authentication
requireAdmin();

// Check if required parameters are present
if (!isset($_GET['id']) || !isset($_GET['action'])) {
    $_SESSION['error'] = 'Invalid request parameters';
    header('Location: reservations.php');
    exit();
}

$reservation_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING);

// Validate action
if (!in_array($action, ['confirm', 'cancel'])) {
    $_SESSION['error'] = 'Invalid action';
    header('Location: reservations.php');
    exit();
}

try {
    // Start transaction
    $conn->beginTransaction();

    // Get current reservation status
    $stmt = $conn->prepare("SELECT status, parking_slot_id FROM reservations WHERE id = ?");
    $stmt->execute([$reservation_id]);
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reservation) {
        throw new Exception('Reservation not found');
    }

    if ($reservation['status'] !== 'Pending') {
        throw new Exception('Can only update pending reservations');
    }

    // Update reservation status
    $new_status = $action === 'confirm' ? 'Confirmed' : 'Cancelled';
    $stmt = $conn->prepare("UPDATE reservations SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $reservation_id]);

    // Update parking slot status
    $slot_status = $action === 'confirm' ? 'Reserved' : 'Vacant';
    $stmt = $conn->prepare("UPDATE parking_slots SET status = ? WHERE id = ?");
    $stmt->execute([$slot_status, $reservation['parking_slot_id']]);

    // Commit transaction
    $conn->commit();

    $_SESSION['success'] = 'Reservation ' . strtolower($new_status) . ' successfully';
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollBack();
    $_SESSION['error'] = $e->getMessage();
}

header('Location: reservations.php');
exit();
?> 