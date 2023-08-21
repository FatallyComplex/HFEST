<?php
// Connect to the database and fetch all facilities
require_once '../database.php';
date_default_timezone_set('America/New_York');

// Get the medical card number from the url
$medical_card_number = isset($_GET['medical_card_number']) ? $_GET['medical_card_number'] : '';

// Get all facilities in the same city

$query = "SELECT FID, Name
FROM EMPLOYEE e
JOIN LOCATION el ON e.PostalCode = el.PostalCode
JOIN FACILITIES f ON el.City = (
	SELECT City FROM LOCATION WHERE PostalCode = f.PostalCode
)
WHERE MedCardNumber = :MedCardNumber";
$stmt = $conn -> prepare($query);
$stmt->bindParam("MedCardNumber", $_GET['medical_card_number']);
$stmt -> execute();
$facilities = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get the last vaccine

$stmt = $conn -> prepare("SELECT DoseNumber, VDate
FROM VACCINES
WHERE MedCardNumber = :MedCardNumber
ORDER BY DoseNumber
DESC
LIMIT 1");
$stmt->bindParam("MedCardNumber", $_GET['medical_card_number']);
$stmt->execute();
$currentVaccine = $stmt->fetch(PDO::FETCH_ASSOC);

if(!(empty($currentVaccine))){
    $currentNumber = $currentVaccine['DoseNumber'] + 1;
    $latestDate = $currentVaccine['VDate'];
}
else{
    $currentNumber = 1;
    $latestDate = date("2020-01-01");
}

// Get all vaccine types
$stmt = $conn -> prepare("SELECT DISTINCT VType
FROM VACCINES");
$stmt->execute();
$VType = $stmt->fetchAll(PDO::FETCH_ASSOC);


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
    <h2>Add Vaccine Details for: <?php echo $medical_card_number; ?></h2>

    <?php if ($currentNumber): ?>
        <h2>Vaccine Number: <?php echo $currentNumber; ?>

        <form action="create-vaccine-action.php" method="post">
            <label for="VType">Vaccine Type</label><br>
            <select name="VType" id="VType">
                <option value=""></option>
                <?php foreach ($VType as $type): ?>
                <option value="<?php echo $type['VType']; ?>"><?php echo $type['VType']; ?></option>
                <?php endforeach; ?>
            </select>
            <label for="facility">Vaccination Facility</label><br>
            <select name="facility" id="facility">
                <option value=""></option>
                <?php foreach ($facilities as $facility): ?>
                <option value="<?php echo $facility['FID']; ?>"><?php echo $facility['Name']; ?></option>
                <?php endforeach; ?>
            </select>
            <label for="VDate">Date of Vaccination</label><br>
            <input type="date" name="VDate" id="VDate" min="<?php echo $latestDate?>" max="<?php echo date('Y-m-d')?>"><br>
            
            <input type="hidden" name = "DoseNumber" id="DoseNumber" value="<?= $currentNumber ?>">
            <input type="hidden" name = "MedCardNumber" id="MedCardNumber" value="<?= $medical_card_number ?>">
            <button type="submit">Create</button>
    </form>
    <?php else: ?>
        <p>Something went wrong retrieving this Employee's Dose history.</p>
    <?php endif; ?>
</body>
</html>