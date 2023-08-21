<?php
require_once '../database.php';

$medcardnumber = $_POST['medcardnumber'];
$nature = $_POST['nature'];
$variant = $_POST['other-variant-input'];
$other_nature = $_POST['other-nature-input'];
$date = $_POST['date'];

$nature_explode = explode(' ', $nature);
$first_word = $nature_explode[0];
$num_words = count($nature_explode);

if($nature === 'variant'){
    $query = "CALL delete_infected_shifts(:employee_id, :infection_date)";
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':employee_id', $medcardnumber);
    $stmt->bindValue(':infection_date', $date);
    $stmt->execute();

    $nature = 'COVID-19' . ' variant ' . $variant;

}else if($nature === 'other'){
    $nature = $other_nature;
}


$query = "INSERT INTO INFECTIONS (MedCardNumber, IDate, INature)
          VALUES (:medcardnumber, :IDate, :INature) ON DUPLICATE KEY UPDATE IDate = :IDate";
$stmt = $conn->prepare($query);
$stmt->bindValue(':medcardnumber', $medcardnumber);
$stmt->bindValue(':IDate', $date);
$stmt->bindValue(':INature', $nature);
$stmt->execute();

$num_rows_affected = $stmt->rowCount();

echo "Entering emails with nature : " . $nature;

if(strpos($nature, 'COVID-19') !== false && $num_rows_affected == 1){
    echo "Entered emails";
    $query = "SELECT DISTINCT e.MedCardNumber, s1.FID, s1.SDate
      FROM EMPLOYEE e
      JOIN SCHEDULES s1 ON e.MedCardNumber = s1.MedCardNumber
      INNER JOIN SCHEDULES s2 ON s1.FID = s2.FID AND s1.SDate = s2.SDate AND s1.StartTime < s2.EndTime AND s1.EndTime > s2.StartTime
      WHERE s2.MedCardNumber = ?
        AND s1.MedCardNumber <> ?
        AND s1.SDate = ?";
    $stmt = $conn -> prepare($query);
    $stmt -> execute([$medcardnumber, $medcardnumber, $date]);
    $infections = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach($infections as $infection){
        $query = "SELECT Fname, Lname
        FROM EMPLOYEE
        WHERE MedCardNumber = ?";
        $stmt = $conn -> prepare($query);
        $stmt -> execute([$infection['MedCardNumber']]);
        $name = $stmt->fetch(PDO::FETCH_ASSOC);
        $employee_name = $name['Fname'] . ' ' . $name['Lname'];

        $query = "SELECT Name FROM FACILITIES WHERE FID = ?";
        $stmt = $conn -> prepare($query);
        $stmt -> execute([$infection['FID']]);
        $facilityname = $stmt->fetch(PDO::FETCH_ASSOC);
        $facility = $facilityname['Name'];

        $query = "INSERT INTO EMAILS (EmployeeName, FacilityName, Date, EmailSubject, Message)
        VALUES (:employee_name, :facility_name, :email_date, 'Warning', 'One of your colleagues that you have worked with in the past two weeks have been')";
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':employee_name', $employee_name);
        $stmt->bindValue(':facility_name', $facility);
        $stmt->bindValue(':email_date', $date);
        $stmt->execute();

        $email_id = $conn->lastInsertId();

        echo $email_id;

        $query = "INSERT INTO RECEIVES (EmID, MedCardNumber)
        VALUES (:email_id, :employee_id)";
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':employee_id', $infection['MedCardNumber']);
        $stmt->bindValue(':email_id', $email_id);
        $stmt->execute();

        $query = "INSERT INTO SENDS (EmID, FID)
        VALUES (:email_id, :facility_id)";
        $stmt = $conn->prepare($query);
        $stmt->bindValue(':facility_id', $infection['FID']);
        $stmt->bindValue(':email_id', $email_id);
        $stmt->execute();
    


    }
}


header('Location: details.php?medical_card_number=' . $medcardnumber);
exit;

?>