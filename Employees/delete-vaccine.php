<?php
require_once '../database.php';

// Get the medical card number, date, and start time of the shift to be deleted
$medical_card_number = $_POST['medical_card_number'];
$dose = $_POST['DoseNumber'];
$fid = $_POST['FacilityId'];

// Delete the vaccine from the database
$query = "DELETE FROM VACCINES WHERE MedCardNumber = ? AND DoseNumber = ? AND FacilityId = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$medical_card_number, $dose, $fid]);

$query = "SELECT DoseNumber FROM VACCINES WHERE MedCardNumber = ? ORDER BY DoseNumber ASC";
$stmt = $conn->prepare($query);
$stmt->execute([$medical_card_number]);
$remainingVaccs = $stmt->fetchAll();
echo '<script>alert("Email is invalid.")</script>';
$newDoseNumb = 1;
foreach($remainingVaccs as $rows){
    $stmt = $conn->prepare("UPDATE VACCINES SET DoseNumber = :NewNumber WHERE MedCardNumber = :MedCardNumber AND DoseNumber = :DoseNumber");
    $stmt->bindParam(':NewNumber', $newDoseNumb);
    $stmt->bindParam(':MedCardNumber', $medical_card_number);
    $stmt->bindParam(':DoseNumber', $rows['DoseNumber']);
    $stmt->execute();

    $newDoseNumb += 1;
}

// Redirect back to the details.php page
header("Location: details.php?medical_card_number=$medical_card_number");
exit;
?>