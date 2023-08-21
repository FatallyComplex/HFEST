<?php
require_once '../database.php';

// Get the request data
$medicalCardNumber = $_POST['medicalCardNumber'];
$Sdate = $_POST['Sdate'];

// Prepare the query to get the shifts on the selected date for the given employee
$query = "SELECT * FROM SCHEDULES WHERE SDate = :Sdate AND MedCardNumber = :medicalCardNumber";
$stmt = $conn->prepare($query);
$stmt->bindValue(':Sdate', $Sdate);
$stmt->bindValue(':medicalCardNumber', $medicalCardNumber);
$stmt->execute();
$shifts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Return the shift conflicts as a JSON-encoded array
echo json_encode($shifts);
?>

