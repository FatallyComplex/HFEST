<?php
require_once '../database.php';
date_default_timezone_set('America/New_York');

$medical_card_number = $_POST['MedCardNumber'];
$currentNumber = $_POST["DoseNumber"];
$newType = $_POST["newType"];
$newVDate = $_POST["newDate"];
$newFacilityID = $_POST["newFacility"];

$stmt = $conn->prepare("UPDATE VACCINES SET VType = :VType, VDate = :VDate, FacilityID = :FacilityID WHERE MedCardNumber = :MedCardNumber AND DoseNumber = :DoseNumber");
$stmt->bindParam(':VType', $newType);
$stmt->bindParam(':VDate', $newVDate);
$stmt->bindParam(':FacilityID', $newFacilityID);
$stmt->bindParam(':MedCardNumber', $medical_card_number);
$stmt->bindParam(':DoseNumber', $currentNumber);
$stmt->execute();



header("Location: ./details.php?medical_card_number={$medical_card_number}");
exit();

?>