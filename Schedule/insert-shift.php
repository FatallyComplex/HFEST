<?php
require_once '../database.php';

// Get the form data
$medicalCardNumber = $_POST['medcardnumber'];
$facilityId = $_POST['facility'];
$date = $_POST['date'];
$startTime = $_POST['start_time'];
$endTime = $_POST['end_time'];

// Insert the shift into the database
$query = "INSERT INTO SHIFTS (MedicalCardNumber, FacilityId, Date, StartTime, EndTime) 
          VALUES (:medicalCardNumber, :facilityId, :date, :startTime, :endTime)";
$stmt = $conn->prepare($query);
$stmt->bindValue(':medicalCardNumber', $medicalCardNumber);
$stmt->bindValue(':facilityId', $facilityId);
$stmt->bindValue(':date', $date);
$stmt->bindValue(':startTime', $startTime);
$stmt->bindValue(':endTime', $endTime);
$result = $stmt->execute();

if ($result) {
    echo "Shift added successfully.";
} else {
    echo "Error adding shift.";
}
?>