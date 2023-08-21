<?php
require_once '../database.php';

// Get the medical card number, date, and start time of the shift to be updated
$medical_card_number = $_POST['medical_card_number'];
$date = $_POST['date'];
$start_time = $_POST['start_time'];
$end_time = $_POST['end_time'];
$FID = $_POST['FID'];

// Redirect to the update-shift-details.php page with the shift details
header("Location: update-shift-details.php?medical_card_number=$medical_card_number&date=$date&start_time=$start_time&end_time=$end_time&FID=$FID");
exit;
?>