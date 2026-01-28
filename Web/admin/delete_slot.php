<?php
session_start();
require_once '../auth.php';
require_once '../db_connect.php';

// Require admin authentication
requireAdmin();

if (isset($_GET['id'])) {
    $slot_id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

    try {
        // Check if the slot exists
        $stmt = $conn->prepare("SELECT status FROM parking_slots WHERE id = ?");
        $stmt->execute([$slot_id]);
        $slot = $stmt->fetch();

        if (!$slot) {
            $_SESSION['error'] = 'Parking slot not found.';
            header('Location: parking_slots.php');
            exit();
        }

        // Check if the slot has any active reservations
        $stmt = $conn->prepare("
            SELECT COUNT(*) as active_count 
            FROM reservations 
            WHERE parking_slot_id = ? 
            AND status IN ('Confirmed', 'Pending')
        ");
        $stmt->execute([$slot_id]);
        $result = $stmt->fetch();

        if ($result['active_count'] > 0) {
            $_SESSION['error'] = 'Cannot delete: This slot has active or pending reservations.';
        } else {
            // Begin transaction
            $conn->beginTransaction();

            // Delete any past reservations associated with this slot
            $stmt = $conn->prepare("DELETE FROM reservations WHERE parking_slot_id = ?");
            $stmt->execute([$slot_id]);

            // Delete the parking slot
            $stmt = $conn->prepare("DELETE FROM parking_slots WHERE id = ?");
            $stmt->execute([$slot_id]);

            // Commit transaction
            $conn->commit();
            $_SESSION['success'] = 'Parking slot deleted successfully.';
        }
    } catch(PDOException $e) {
        // Rollback transaction on error
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        $_SESSION['error'] = 'Error deleting parking slot. Please try again.';
    }
} else {
    $_SESSION['error'] = 'Invalid request.';
}

// Redirect back to parking slots page
header('Location: parking_slots.php');
exit();
?> 