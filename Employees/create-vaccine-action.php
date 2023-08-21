<?php
// Connect to the database and fetch all facilities
require_once '../database.php';
date_default_timezone_set('America/New_York');

$VType = $_POST['VType'];
$facility = $_POST['facility'];
$VDate = $_POST['VDate'];
$DoseNumber = $_POST['DoseNumber'];
$MedCardNumber = $_POST['MedCardNumber'];


$query = "INSERT INTO VACCINES (DoseNumber, VType, VDate, MedCardNumber, FacilityID)
          VALUES (:DoseNumber, :VType, :VDate, :MedCardNumber, :FacilityID)";
$stmt = $conn->prepare($query);
$stmt->bindValue(':DoseNumber', $DoseNumber);
$stmt->bindValue(':VType', $VType);
$stmt->bindValue(':VDate', $VDate);
$stmt->bindValue(':MedCardNumber', $MedCardNumber);
$stmt->bindValue(':FacilityID', $facility);
$stmt->execute();

header('Location: details.php?medical_card_number=' . $MedCardNumber);
exit;

?>
