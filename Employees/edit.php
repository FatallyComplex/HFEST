<?php
// Connect to the database and fetch all facilities
require_once '../database.php';

// Get the medical card number from the url
$medical_card_number = isset($_GET['medical_card_number']) ? $_GET['medical_card_number'] : '';

$stmt = $conn -> prepare("SELECT * FROM EMPLOYEE WHERE MedCardNumber = :MedCardNumber");
$stmt->bindParam("MedCardNumber", $_GET['medical_card_number']);
$stmt->execute();
$employeeInfo = $stmt->fetch(PDO::FETCH_ASSOC);

if(isset($_POST["Role"]) && isset($_POST["FName"]) && isset($_POST["LName"]) && isset($_POST["DOB"]) && isset($_POST["PhoneNumber"]) &&
    isset($_POST["Address"]) && isset($_POST["PostalCode"]) && isset($_POST["Citizenship"]) && isset($_POST["Email"])){

        $inputPhoneNum = $_POST["PhoneNumber"];
        $inputPostalCode = $_POST["PostalCode"];
        $inputEmail = $_POST["Email"];

        if(!(preg_match("/[0-9][0-9][0-9]-[0-9][0-9][0-9]-[0-9][0-9][0-9][0-9]/", $inputPhoneNum))){
            echo '<script>alert("Telephone Number is invalid. Please enter with the format: xxx-xxx-xxxx.")</script>';
        }
        elseif(!(preg_match("/[A-Z][0-9][A-Z] [0-9][A-Z][0-9]/", $inputPostalCode)))
        {
            echo '<script>alert("Postal Code is invalid.")</script>';
        }
        elseif(!(preg_match("/.@[a-z]+\.[a-z]+/", $inputEmail))){
            echo '<script>alert("Email is invalid.")</script>';
        }
        else{    
        $employee = $conn->prepare("UPDATE EMPLOYEE SET Role = :Role, FName = :FName, LName = :LName, DOB = :DOB, PhoneNumber = :PhoneNumber,
                    Address = :Address, PostalCode = :PostalCode, Citizenship = :Citizenship, Email = :Email WHERE MedCardNumber = :MedCardNumber ");

        $employee->bindParam(':MedCardNumber', $_POST["MedCardNumber"]);
        $employee->bindParam(':Role', $_POST["Role"]);
        $employee->bindParam(':FName', $_POST["FName"]);
        $employee->bindParam(':LName', $_POST["LName"]);
        $employee->bindParam(':DOB', $_POST["DOB"]);
        $employee->bindParam(':PhoneNumber', $_POST["PhoneNumber"]);
        $employee->bindParam(':Address', $_POST["Address"]);
        $employee->bindParam(':PostalCode', $_POST["PostalCode"]);
        $employee->bindParam(':Citizenship', $_POST["Citizenship"]);
        $employee->bindParam(':Email', $_POST["Email"]);

        if($employee->execute()){
            $stmt = $conn->prepare("SELECT COUNT(*) FROM LOCATION WHERE PostalCode = :PostalCode");
            $stmt->bindParam(':PostalCode', $_POST["PostalCode"]);
            $stmt->execute();
            $LocExists = $stmt->fetchColumn();
            //If Postal Code is not recorded in Locations table, Add a new one
            if($LocExists != 1){
                $FirstPostalLetter = $inputPostalCode[0];
                if($FirstPostalLetter == 'H'){
                    $location = $conn->prepare("INSERT INTO LOCATION (PostalCode, City, Province)
                    VALUES (:PostalCode, 'Montreal', 'Quebec')");
                    $location->bindParam(':PostalCode', $_POST["PostalCode"]);
                    $location->execute();
                }
                elseif($FirstPostalLetter == 'K' || $FirstPostalLetter == 'L'){
                    $location = $conn->prepare("INSERT INTO LOCATION (PostalCode, City, Province)
                    VALUES (:PostalCode, 'Ottawa', 'Ontario')");
                    $location->bindParam(':PostalCode', $_POST["PostalCode"]);
                    $location->execute();
                }
                elseif($FirstPostalLetter == 'M' || $FirstPostalLetter == 'N'){
                    $location = $conn->prepare("INSERT INTO LOCATION (PostalCode, City, Province)
                    VALUES (:PostalCode, 'Toronto', 'Ontario')");
                    $location->bindParam(':PostalCode', $_POST["PostalCode"]);
                    $location->execute();
                }
                elseif($FirstPostalLetter == 'V'){
                    $location = $conn->prepare("INSERT INTO LOCATION (PostalCode, City, Province)
                    VALUES (:PostalCode, 'Vancouver', 'British Columbia')");
                    $location->bindParam(':PostalCode', $_POST["PostalCode"]);
                    $location->execute();
                }
                else{
                    $location = $conn->prepare("INSERT INTO LOCATION (PostalCode, City, Province)
                    VALUES (:PostalCode, 'Quebec', 'Quebec')");
                    $location->bindParam(':PostalCode', $_POST["PostalCode"]);
                    $location->execute();
                }
            }
            header("Location: .");
        }
        else{
            header("Location: ./edit.php?medical_card_number= $medical_card_number; ?>");
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>HFESTS - <?php echo $medical_card_number; ?> Schedule</title>
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
        .option {
                text-align: center;
                margin-top: 50px;
            }
        .option a {
            display: inline-block;
            font-size: 24px;
            color: white;
            background-color: #0077cc;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease-in-out;
            text-decoration: none;
            margin: 0 20px;
        }
        .option a:hover {
            background-color: #005fa3;
        }
    </style>
    <a href="/HFESTS/">
        <img src="https://img.freepik.com/premium-vector/modern-letter-h-f-elegant-abstract-logo_649646-350.jpg?w=996" alt="HFESTS logo" style="position: absolute; top: 10px; left: 10px; width: 150px; height: auto;">
</a>

</head>
<body>
    <h1>Update Employee Information</h1>
    <h2>Medical Card Number: <?php echo $medical_card_number; ?></h2>
    <div class="option">
        <a href="./addEmploy.php?medical_card_number=<?php echo $medical_card_number; ?>">Add New Employment</a>
        <a href="./endEmploy.php?medical_card_number=<?php echo $medical_card_number; ?>">Terminate an Employment</a>
    </div>

    <form action="./edit.php?medical_card_number=<?php echo $medical_card_number; ?>" method="post"> <b>Replace Information Please:</b>
            <br>
            <label for="Role">Role</label><br>
            <input type="text" name="Role" id="Role" value="<?= $employeeInfo["Role"] ?>"><br>
            <label for="FName">First Name</label><br>
            <input type="text" name="FName" id="FName" value="<?= $employeeInfo["Fname"] ?>"><br>
            <label for="LName">Last Name</label><br>
            <input type="text" name="LName" id="LName" value="<?= $employeeInfo["Lname"] ?>"><br>
            <label for="DOB">Date of Birth</label><br>
            <input type="date" name="DOB" id="DOB" value="<?= $employeeInfo["DOB"] ?>"><br>
            <label for="PhoneNumber">Telephone Number</label><br>
            <input type="text" name="PhoneNumber" id="PhoneNumber" value="<?= $employeeInfo["PhoneNumber"] ?>"><br>
            <label for="Address">Address</label><br>
            <input type="text" name="Address" id="Address" value="<?= $employeeInfo["Address"] ?>"><br>
            <label for="PostalCode">Postal Code</label><br>
            <input type="text" name="PostalCode" id="PostalCode" value="<?= $employeeInfo["PostalCode"] ?>"><br>
            <label for="Citizenship">Citizenship</label><br>
            <input type="text" name="Citizenship" id="Citizenship" value="<?= $employeeInfo["Citizenship"] ?>"><br>
            <label for="Email">Email</label><br>
            <input type="text" name="Email" id="Email" value="<?= $employeeInfo["Email"] ?>"><br>
            <input type="hidden" name="MedCardNumber" id="MedCardNumber" value="<?= $employeeInfo["MedCardNumber"] ?>">
            <button type="submit">Update</button>
        </form>

    <a href="./">Back</a>
</body>
</html>