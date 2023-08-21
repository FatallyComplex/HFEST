<?php
require_once '../database.php';
date_default_timezone_set('America/New_York');

$medical_card_number = $_POST['medical_card_number'];
$old_date = $_POST['old_date'];
$old_nature = $_POST['old_nature'];
$new_date = $_POST['date'];
$new_nature = $_POST['nature'];

// Update the infection details in the database
$query = "UPDATE INFECTIONS SET IDate = ?, INature = ? WHERE MedCardNumber = ? AND IDate = ? AND INature = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$new_date, $new_nature, $medical_card_number, $old_date, $old_nature]);

header('Location: ./details.php?medical_card_number=' . $medical_card_number);
exit();
?>