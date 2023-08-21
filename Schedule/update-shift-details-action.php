<?php
require_once '../database.php';

// Get the updated shift details from the form data
$medical_card_number = isset($_POST['medical_card_number']) ? $_POST['medical_card_number'] : '';
$date = isset($_POST['date']) ? $_POST['date'] : '';
$start_time = isset($_POST['start_time']) ? $_POST['start_time'] : '';
$end_time = isset($_POST['end_time']) ? $_POST['end_time'] : '';
$FID = isset($_POST['FID']) ? $_POST['FID'] : '';
$OldDate = isset($_POST['old_date']) ? $_POST['old_date'] : '';
$OldTime = isset($_POST['old_time']) ? $_POST['old_time'] :  '';

// Fetch the shift details from the database
$query = "SELECT * FROM SCHEDULES WHERE MedCardNumber = ? AND SDate = ? AND StartTime = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$medical_card_number, $date, $start_time]);
$shift = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if the new shift conflicts with any other shift
$query = "SELECT * FROM SCHEDULES WHERE MedCardNumber = ? AND SDate = ? AND ((StartTime <= ? AND EndTime > ?) OR (StartTime < ? AND EndTime >= ?) OR (StartTime >= ? AND EndTime <= ?))";
$stmt = $conn->prepare($query);
$stmt->execute([$medical_card_number, $date, $start_time, $start_time, $end_time, $end_time, $start_time, $end_time]);
$conflicting_shift = $stmt->fetch(PDO::FETCH_ASSOC);

if ($conflicting_shift) {
    // There is a conflict, so redirect back to the shift details page with an error message
    header("Location: shift-details.php?medical_card_number={$medical_card_number}&date={$OldDate}&error=conflict");
    exit();
}

// Check if the new date is within 14 days of an infection
$query = "SELECT * FROM INFECTIONS WHERE MedCardNumber = ? AND DATEDIFF(?, IDate) < 14";
$stmt = $conn->prepare($query);
$stmt->execute([$medical_card_number, $date]);
$infection = $stmt->fetch(PDO::FETCH_ASSOC);

if ($infection) {
    // The new date is within 14 days of an infection, so redirect back to the shift details page with an error message
    header("Location: shift-details.php?medical_card_number={$medical_card_number}&date={$OldDate}&error=infection");
    exit();
}

// Check if there is at least 1 hour between shifts
$query = "SELECT * FROM SCHEDULES WHERE MedCardNumber = ? AND ((DATE_ADD(?, INTERVAL 1 HOUR) BETWEEN SDate + INTERVAL StartTime HOUR_SECOND AND SDate + INTERVAL EndTime HOUR_SECOND) OR (DATE_ADD(?, INTERVAL -1 HOUR) BETWEEN SDate + INTERVAL StartTime HOUR_SECOND AND SDate + INTERVAL EndTime HOUR_SECOND))";
$stmt = $conn->prepare($query);
$stmt->execute([$medical_card_number, $start_time, $end_time]);
$buffer_conflict = $stmt->fetch(PDO::FETCH_ASSOC);

if ($buffer_conflict) {
    // There is a buffer conflict, so redirect back to the shift details page with an error message
    header("Location: shift-details.php?medical_card_number={$medical_card_number}&date={$OldDate}&error=buffer");
    exit();
}


// Update the shift details in the database
$query = "UPDATE SCHEDULES SET FID = ?, SDate = ?, StartTime = ?, EndTime = ? WHERE MedCardNumber = ? AND SDate = ? AND StartTime = ?";
$stmt = $conn->prepare($query);
$result = $stmt->execute([$FID, $date, $start_time, $end_time, $medical_card_number, $OldDate, $OldTime]);

// Redirect back to the shift details page with a success message
header("Location: shift-details.php?medical_card_number={$medical_card_number}&date={$date}&success=update");
exit();
?>
