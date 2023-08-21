<?php
require_once '../database.php';

$facilityId = isset($_POST['facilityId']) ? $_POST['facilityId'] : '';
$medicalCardNumber = isset($_POST['medicalCardNumber']) ? $_POST['medicalCardNumber'] : '';

// Get the dates that are available for the selected facility and medical card number
$query = "SELECT DISTINCT SDate FROM SCHEDULES WHERE FID = :facilityId AND MedCardNumber = :MedCardNumber1 AND NOT EXISTS (SELECT * FROM INFECTIONS WHERE MedCardNumber = :MedCardNumber AND DATEDIFF(SDate, IDate) <= 14)";
$stmt = $conn->prepare($query);
$stmt->bindValue(':facilityId', $facilityId);
$stmt->bindValue(':medicalCardNumber1', $medicalCardNumber);
$stmt->bindValue(':medicalCardNumber', $medicalCardNumber);
$stmt->execute();
$availableDates = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Exclude any dates that fall within 14 days of the employee being infected by COVID-19
$infectedDate = date('Y-m-d', strtotime('-14 days'));
$availableDates = array_filter($availableDates, function($date) use ($infectedDate) {
  return $date > $infectedDate;
});

// Return the available dates as a JSON-encoded array
header('Content-Type: application/json');
echo json_encode($availableDates);
?>
