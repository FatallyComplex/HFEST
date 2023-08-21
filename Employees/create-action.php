<?php
// Connect to the database and fetch all facilities
require_once '../database.php';
date_default_timezone_set('America/New_York');

$MedCardNumber = $_POST['MedCardNumber'];
$Role = $_POST['Role'];
$FName = $_POST['FName'];
$LName = $_POST['LName'];
$DOB = $_POST['DOB'];
$phone = $_POST['phone'];
$Email = $_POST['Email'];
$Address = $_POST['Address'];
$PostalCode = $_POST['PostalCode'];
$City = $_POST['City'];
$Province = $_POST['Province'];
$Citizenship = $_POST['Citizenship'];
$FacilityId = $_POST['FacilityId'];
$StartDate = $_POST['StartDate'];

$medicareExists = true;

$stmt = $conn->prepare("SELECT MedCardNumber FROM EMPLOYEE WHERE MedCardNumber = ?");
$stmt->execute([$MedCardNumber]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$result) {
    // Medicare number is unique, set flag to exit loop
    $medicareExists = false;
}

if ($medicareExists) {
    // Notify the user that the medical number already exists
    echo "<script>alert('The medical number already exists. Please try again.');</script>";
    // Redirect the user back to the previous page
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}
$query = 'INSERT INTO LOCATION VALUES(?, ?, ?) ON DUPLICATE KEY UPDATE City=City';
$stmt = $conn->prepare($query);
$stmt->execute([$PostalCode, $City, $Province]);

$query = 'INSERT INTO EMPLOYEE VALUES (:medcardnumber, :role, :Fname, :Lname, :DOB, :PhoneNumber, :Address, :PostalCode, :Citizenship, :email)';
$stmt = $conn->prepare($query);
$stmt->bindValue(":medcardnumber", $MedCardNumber);
$stmt->bindValue(":role", $Role);
$stmt->bindValue(":Fname", $FName);
$stmt->bindValue(":Lname", $LName);
$stmt->bindValue(":DOB", $DOB);
$stmt->bindValue(":PhoneNumber", $phone);
$stmt->bindValue(":Address", $Address);
$stmt->bindValue(":PostalCode", $PostalCode);
$stmt->bindValue(":Citizenship", $Citizenship);
$stmt->bindValue(":email", $Email);
$stmt->execute();



$facilityId = isset($_POST['FacilityId']) && !empty($_POST['FacilityId']) ? $_POST['FacilityId'] : null;
$startDate = isset($_POST['StartDate']) && !empty($_POST['StartDate']) ? $_POST['StartDate'] : null;

if($FacilityId != null && $StartDate != null){
    $query = "INSERT INTO EMPLOYS(MedCardNumber, FacilityId, StartDate) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->execute([$medicareNumber, $FacilityId, $StartDate]);
}

header('Location: ./');
exit;

?>