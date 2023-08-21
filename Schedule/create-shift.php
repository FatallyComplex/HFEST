<?php
require_once '../database.php';

$medcardnumber = $_POST['medcardnumber'];
$facility = $_POST['facility'];
$date = $_POST['date'];
$start_time = $_POST['start_time'];
$end_time = $_POST['end_time'];

$query = "INSERT INTO SCHEDULES (MedCardNumber, FID, Sdate, StartTime, EndTime)
          VALUES (:medcardnumber, :facility, :date, :start_time, :end_time)";
$stmt = $conn->prepare($query);
$stmt->bindValue(':medcardnumber', $medcardnumber);
$stmt->bindValue(':facility', $facility);
$stmt->bindValue(':date', $date);
$stmt->bindValue(':start_time', $start_time);
$stmt->bindValue(':end_time', $end_time);
$stmt->execute();

header('Location: index.php?medical_card_number=' . $medcardnumber);
exit;
?>