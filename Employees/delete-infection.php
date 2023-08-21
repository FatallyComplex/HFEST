<?php
require_once '../database.php';

// Get the medical card number, date, and start time of the shift to be deleted
$medical_card_number = $_POST['medical_card_number'];
$date = $_POST['IDate'];
$nature = $_POST['INature'];

// Delete the shift from the database
$query = "DELETE FROM INFECTIONS WHERE MedCardNumber = ? AND IDate = ? AND INature = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$medical_card_number, $date, $nature]);

// Redirect back to the shift-details.php page
header("Location: details.php?medical_card_number=$medical_card_number");
exit;
?>