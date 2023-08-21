<?php
require_once '../database.php';

// Get the medical card number, date, and start time of the shift to be deleted
$medical_card_number = $_POST['medical_card_number'];
$date = $_POST['date'];
$start_time = $_POST['start_time'];

// Delete the shift from the database
$query = "DELETE FROM SCHEDULES WHERE MedCardNumber = ? AND SDate = ? AND StartTime = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$medical_card_number, $date, $start_time]);

// Redirect back to the shift-details.php page
header("Location: shift-details.php?medical_card_number=$medical_card_number&date=$date");
exit;
?>
