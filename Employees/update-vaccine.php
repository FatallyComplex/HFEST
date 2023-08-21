<?php
// Connect to the database and fetch all facilities
require_once '../database.php';
date_default_timezone_set('America/New_York');

// Get info from Form
$medical_card_number = $_POST["medical_card_number"];
$currentNumber = $_POST["DoseNumber"];
$currentType = $_POST["VType"];
$currentVDate = $_POST["VDate"];
$currentFacility = $_POST["FacilityName"];
$currentFacilityId = $_POST["FacilityID"];


// Get all OTHER facilities in the same city

$query = "SELECT FID, Name
FROM EMPLOYEE e
JOIN LOCATION el ON e.PostalCode = el.PostalCode
JOIN FACILITIES f ON el.City = (
	SELECT City FROM LOCATION WHERE PostalCode = f.PostalCode
)
WHERE MedCardNumber = :MedCardNumber AND f.Name <> :FacilityName";
$stmt = $conn -> prepare($query);
$stmt->bindParam("MedCardNumber", $_POST["medical_card_number"]);
$stmt->bindParam("FacilityName", $_POST["FacilityName"]);
$stmt -> execute();
$facilities = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get all OTHER vaccine types
$stmt = $conn -> prepare("SELECT DISTINCT VType
FROM VACCINES WHERE VType <> :VType");
$stmt->bindParam("VType", $_POST["VType"]);
$stmt->execute();
$VType = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get the PREVIOUS vaccine
$stmt = $conn -> prepare("SELECT DoseNumber, VDate
FROM VACCINES
WHERE MedCardNumber = :MedCardNumber AND DoseNumber = :DoseNumber - 1;");
$stmt->bindParam("MedCardNumber", $medical_card_number);
$stmt->bindParam("DoseNumber", $currentNumber);
$stmt->execute();
$priorVaccine = $stmt->fetch(PDO::FETCH_ASSOC);

$minDate = '';

if(empty($priorVaccine)){
    $minDate = date("2020-01-01");
}
else{
    $minDate = $priorVaccine['VDate'];
}

// Get the FOLLOWING vaccine
$stmt = $conn -> prepare("SELECT DoseNumber, VDate
FROM VACCINES
WHERE MedCardNumber = :MedCardNumber AND DoseNumber = :DoseNumber + 1;");
$stmt->bindParam("MedCardNumber", $_POST["medical_card_number"]);
$stmt->bindParam("DoseNumber", $_POST["DoseNumber"]);
$stmt->execute();
$followingVaccine = $stmt->fetch(PDO::FETCH_ASSOC);
if(empty($followingVaccine)){
    $furthestDate = date('Y-m-d');
}
else{
    $furthestDate = $followingVaccine['VDate'];
}

if(isset($_POST["newType"]) && isset($_POST["newDate"]) && isset($_POST["newFacility"])){
    $stmt = $conn->prepare("UPDATE VACCINES SET VType = :VType, VDate = :VDate, FacilityID = :FacilityID WHERE MedCardNumber = :MedCardNumber AND DoseNumber = :DoseNumber");
    $stmt->bindParam(':VType', $_POST["VType"]);
    $stmt->bindParam(':VDate', $_POST["VDate"]);
    $stmt->bindParam(':FacilityID', $_POST["facility"]);
    $stmt->bindParam(':MedCardNumber', $_POST["medical_card_number"]);
    $stmt->bindParam(':DoseNumber', $_POST["DoseNumber"]);
    if($stmt->execute()){   
        header("Location: ./details.php?medical_card_number={$medical_card_number}"); 
}

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HFESTS - <?php echo $medical_card_number; ?> Vaccination</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat|Open+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.1/css/all.css" integrity="sha384-vp86vTRFVJgpjF9jiIGPEEqYqlDwgyBgEF109VFjmqGmIY/Y4HV4d3Gp2irVfcrp" crossorigin="anonymous">
    <style>
        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
        }
        h1 {
            color: #333333;
            text-align: center;
            margin-top: 50px;
        }
        caption{
            font-size: 24px;
        }
        form {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 50px;
        }
        label {
            font-size: 18px;
            color: #666666;
            margin-bottom: 10px;
        }
        select {
            font-size: 16px;
            padding: 10px;
            border: none;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        button {
            font-size: 18px;
            color: white;
            background-color: #3f3f3f;
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease-in-out;
        }
        button:hover {
            background-color: #1c1c1c;
        }
        h2 {
            color: #444444;
            text-align: center;
            margin-top: 50px;
        }
        table {
            border-collapse: collapse;
            margin: 0 auto;
            max-width: 2400px;
            margin-top: 50px;
        }
        th, td {
            font-size: 24px;
            color: #666666;
            padding: 20px;
            border: 1px solid #cccccc;
            border-radius: 5px;
            text-align: left;
        }
        
        th {
            background-color: #912338;
            font-weight: bold;
            color: white;
        }
        nav {
            display: flex;
            justify-content: flex-end;
            background-color: #912338;
            padding: 20px 10px;
            color: white;
        }
        nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        nav li {
            display: inline;
            margin-left: 20px;
        }
        nav a {
            color: white;
            text-decoration: none;
        }
        nav a:hover {
            text-decoration: underline;
        }
        .centered-link{
            padding-top: 20px;
            text-align: center;
        }
        .last-table{
            margin-bottom: 50px;
        }

        .table-wrapper-scroll-y > div {
        display: flex; justify-content: center;
        flex: 1;
        overflow-x: auto;
        }

        .table-wrapper-scroll-y {
        max-height: 300px; /* set a fixed height for the table body */
        overflow-y: auto; /* enable vertical scroll bar */
        overflow-x: hidden; /* hide horizontal scroll bar */
        margin: 0 auto;
        }

        .table-wrapper-scroll-y::-webkit-scrollbar {
        width: 8px;
        }

        .table-wrapper-scroll-y::-webkit-scrollbar-track {
        background-color: #f1f1f1;
        }

        .table-wrapper-scroll-y::-webkit-scrollbar-thumb {
        background-color: #912338;
        }
    </style>
    <a href="/HFESTS/">
        <img src="https://img.freepik.com/premium-vector/modern-letter-h-f-elegant-abstract-logo_649646-350.jpg?w=996" alt="HFESTS logo" style="position: absolute; top: 10px; left: 10px; width: 150px; height: auto;">
</a>
</head>
<body>
<nav>
    <ul>
        <li><a href="../">Home</a></li>
        <li><a href="../Employees">Search Employees</a></li>
        <li><a href="../Facilities">Search Facilities</a></li>
        <li><a href="./details.php?medical_card_number=<?php echo $medical_card_number;?>">Back</a></li>
    </ul>
</nav>
    <h1>Employee Information</h1>
    <h2>Edit Vaccine Details for: <?php echo $medical_card_number; ?></h2>

    <?php if ($currentNumber): ?>
        <h2>Vaccine Number: <?php echo $currentNumber; ?>

        <form action="update-vaccine-action.php" method="post">
            <label for="newType">Vaccine Type</label><br>
            <select name="newType" id="newType">
                <option value="<?php echo $currentType; ?>"><?php echo $currentType; ?></option>
                <?php foreach ($VType as $type): ?>
                <option value="<?php echo $type['VType']; ?>"><?php echo $type['VType']; ?></option>
                <?php endforeach; ?>
            </select>
            <label for="newFacility">Vaccination Facility</label><br>
            <select name="newFacility" id="newFacility">
                <option value="<?php echo $currentFacilityId; ?>"><?php echo $currentFacility; ?></option>
                <?php foreach ($facilities as $facility): ?>
                <option value="<?php echo $facility['FID']; ?>"><?php echo $facility['Name']; ?></option>
                <?php endforeach; ?>
            </select>
            <label for="newDate">Date of Vaccination</label><br>
            <input type="date" name="newDate" id="newDate" min="<?php echo $minDate?>" max="<?php echo $furthestDate?>" value="<?php echo $currentVDate; ?>">
            <br>
            <input type="hidden" name = "DoseNumber" id="DoseNumber" value="<?= $currentNumber ?>">
            <input type="hidden" name = "MedCardNumber" id="MedCardNumber" value="<?= $medical_card_number ?>">
            <button type="submit">Update</button>
    </form>
    <?php else: ?>
        <p>Something went wrong retrieving this Employee's Dose history.</p>
    <?php endif; ?>

</body>
</html>