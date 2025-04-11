<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Trainer') {
    header("Location: login page modified.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $trainer_id = $_SESSION['user_id'];
    $slot_id = $_POST['slot_id'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // Get slot details first
        $get_slot = $conn->prepare("SELECT available_date, slot_time FROM trainer_slots WHERE id = ? AND trainer_id = ?");
        $get_slot->bind_param("ii", $slot_id, $trainer_id);
        $get_slot->execute();
        $slot = $get_slot->get_result()->fetch_assoc();

        // Update trainer slot status
        $update_trainer = $conn->prepare("UPDATE trainer_slots SET status = 'Cancelled' WHERE id = ? AND trainer_id = ?");
        $update_trainer->bind_param("ii", $slot_id, $trainer_id);
        $update_trainer->execute();

        // Update corresponding gym slots
        $update_gym = $conn->prepare("UPDATE gym_slots SET status = 'Cancelled' 
                                    WHERE booking_date = ? AND slot_time = ? AND trainer_id = ?");
        $update_gym->bind_param("ssi", $slot['available_date'], $slot['slot_time'], $trainer_id);
        $update_gym->execute();

        $conn->commit();
        $_SESSION['slot_success'] = "Slot cancelled successfully!";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['slot_error'] = "Error cancelling slot. Please try again.";
    }

    header("Location: trainer_page.php#availability");
    exit();
}
?>