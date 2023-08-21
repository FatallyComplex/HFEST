<?php
// Connect to the database and fetch all facilities
require_once '../database.php';

// Get the medical card number from the url
$FID = isset($_GET['FID']) ? $_GET['FID'] : '';

$stmt = $conn->prepare("SELECT * FROM FACILITIES f JOIN MANAGES m ON f.FID = m.FID WHERE f.FID = :FID");
$stmt->bindParam("FID", $_GET['FID']);
$stmt->execute();
$facilityInfo = $stmt->fetch(PDO::FETCH_ASSOC);

//Query returns null because no manager is assigned
if(!($facilityInfo))
{
    $stmt = $conn->prepare("SELECT * FROM FACILITIES WHERE FID = :FID");
    $stmt->bindParam("FID", $_GET['FID']);
    $stmt->execute();
    $facilityInfo = $stmt->fetch(PDO::FETCH_ASSOC);
    $facilityInfo["MedCardNumber"] = "";
}

//Query finds all Admin Personnel in City
$query = "SELECT e.MedCardNumber, e.Fname, e.Lname
FROM FACILITIES f
JOIN LOCATION l ON f.PostalCode = l.PostalCode
JOIN EMPLOYEE e ON l.City = (
	SELECT City FROM LOCATION WHERE PostalCode = e.PostalCode
)
WHERE f.FID = :FID AND e.Role = \"Administrative Personnel\"";
$stmt = $conn -> prepare($query);
$stmt->bindParam("FID", $_GET['FID']);
$stmt->execute();
$potentialManagers = $stmt->fetchAll(PDO::FETCH_ASSOC);


if(isset($_POST["Name"]) && isset($_POST["Address"]) && isset($_POST["PostalCode"]) && isset($_POST["PhoneNumber"]) &&
     isset($_POST["WebAddress"]) && isset($_POST["Type"]) && isset($_POST["Capacity"])){
        
        $inputPostalCode = $_POST["PostalCode"];
        $inputWeb = $_POST["WebAddress"];
        
        $stmt = $conn->prepare("SELECT COUNT(*) FROM EMPLOYS WHERE EndDate IS NULL AND FacilityID = :FID");
        $stmt->bindParam("FID", $_GET['FID']);
        $stmt->execute();
        $employeeCount = $stmt->fetchColumn();

        if(!(preg_match("/[A-Z][0-9][A-Z] [0-9][A-Z][0-9]/", $inputPostalCode))){
            echo '<script>alert("Postal Code is invalid.")</script>';
        }
        elseif(!(preg_match("/([a-z]+|[0-9]+)\.[a-z]+/", $inputWeb))){
            echo '<script>alert("Website Address is invalid.")</script>';
        }
        elseif($employeeCount > $_POST["Capacity"]){
            echo '<script>alert("This Capacity is too low for the current number of Employees.")</script>';
        }
        else{
            $facility = $conn->prepare("UPDATE FACILITIES SET Name = :Name, Address = :Address, PostalCode = :PostalCode, PhoneNumber = :PhoneNumber,
                      WebAddress = :WebAddress, Type = :Type, Capacity = :Capacity WHERE FID = :FID ");

            $facility->bindParam(':FID', $_POST["FID"]);
            $facility->bindParam(':Name', $_POST["Name"]);
            $facility->bindParam(':Address', $_POST["Address"]);
            $facility->bindParam(':PostalCode', $_POST["PostalCode"]);
            $facility->bindParam(':PhoneNumber', $_POST["PhoneNumber"]);
            $facility->bindParam(':WebAddress', $_POST["WebAddress"]);
            $facility->bindParam(':Type', $_POST["Type"]);
            $facility->bindParam(':Capacity', $_POST["Capacity"]);

            if($facility->execute()){
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
                //If Manager ID is given:
                if(isset($_POST["MedCardNumber"])){
                    $stmt = $conn->prepare("SELECT COUNT(*) FROM EMPLOYEE WHERE MedCardNumber = :MedCardNumber");
                    $stmt->bindParam(':MedCardNumber', $_POST["MedCardNumber"]);
                    $stmt->execute();
                    $employeeExists = $stmt->fetchColumn();
                    if($employeeExists == 1){
                        $stmt = $conn->prepare("SELECT Role FROM EMPLOYEE WHERE MedCardNumber = :MedCardNumber");
                        $stmt->bindParam(':MedCardNumber', $_POST["MedCardNumber"]);
                        $stmt->execute();
                        $managerRole = $stmt->fetchColumn();
                        if($managerRole == "Administrative Personnel"){
                            $stmt = $conn->prepare("SELECT COUNT(*) FROM MANAGES WHERE FID = :FID");
                            $stmt->bindParam(':FID', $_POST["FID"]);
                            $stmt->execute();
                            $managerExists= $stmt->fetchColumn();
                            if($managerExists == 0){
                                $stmt = $conn->prepare("INSERT INTO MANAGES (MedCardNumber, FID) VALUES (:MedCardNumber, :FID)");
                                $stmt->bindParam(':MedCardNumber', $_POST["MedCardNumber"]);
                                $stmt->bindParam(':FID', $_POST["FID"]);
                                $stmt->execute();

                                $employs = $conn->prepare("INSERT INTO EMPLOYS (MedCardNumber, FacilityId, StartDate)
                                    VALUES (:MedCardNumber, :FID, (SELECT CAST( curdate() AS Date )))");
                                $employs->bindParam(':MedCardNumber', $_POST["MedCardNumber"]);
                                $employs->bindParam(':FID', $_POST["FID"]);
                                $employs->execute();

                                header("Location: .");
                            }
                            else{ //There is already a Manager for this Facility so We Update the Table

                                $stmt = $conn->prepare("UPDATE MANAGES SET MedCardNumber = :MedCardNumber WHERE FID = :FID ");
                                $stmt->bindParam(':MedCardNumber', $_POST["MedCardNumber"]);
                                $stmt->bindParam(':FID', $_POST["FID"]);
                                $stmt->execute();

                                $stmt = $conn->prepare("SELECT COUNT(*) FROM EMPLOYS WHERE FacilityId = :FID AND MedCardNumber = :MedCardNumber AND EndDate IS NULL;");
                                $stmt->bindParam(':FID', $_POST["FID"]);
                                $stmt->bindParam(':MedCardNumber', $_POST["MedCardNumber"]);
                                $stmt->execute();
                                $alreadyEmployed= $stmt->fetchColumn();
                                
                                if($alreadyEmployed != 1){
                                    $employs = $conn->prepare("INSERT INTO EMPLOYS (MedCardNumber, FacilityId, StartDate)
                                        VALUES (:MedCardNumber, :FID, (SELECT CAST( curdate() AS Date )))");
                                    $employs->bindParam(':MedCardNumber', $_POST["MedCardNumber"]);
                                    $employs->bindParam(':FID', $_POST["FID"]);
                                    $employs->execute();
                                }
                                
                                header("Location: .");
                            }

                        }
                        else{
                            echo '<script>alert("This Employee is not an Administrative Personnel.")</script>';
                        }
                    }
                    else{
                        echo '<script>alert("This Employee was not found.")</script>';
                    }
                }
            header("Location: .");
            }
          
        }  
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
<title>HFESTS - Search Facility</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat|Open+Sans&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f7f7f7;
        }
        h1 {
            color: #444444;
            text-align: center;
            margin-top: 50px;
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
            max-width: 600px;
            margin-top: 50px;
        }
        th, td {
            font-size: 16px;
            color: #666666;
            padding: 10px;
            border: 1px solid #cccccc;
            border-radius: 5px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            color: #444444;
        }
        nav {
            text-align: right;
        }
        td:nth-of-type(4) {
        max-width: 90px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        }
        td:nth-of-type(8) {
        max-width: 90px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        }
        td:nth-of-type(9) {
        max-width: 100px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: normal;
        }
        .add-facility-button {
            float: left;
            margin-left: 40px;
            margin-top: 20px;
            margin-bottom: 20px;
        }
    </style>
    <a href="/HFESTS/">
        <img src="https://img.freepik.com/premium-vector/modern-letter-h-f-elegant-abstract-logo_649646-350.jpg?w=996" alt="HFESTS logo" style="position: absolute; top: 10px; left: 10px; width: 150px; height: auto;">
    </a>
</head>
<body>
    <h1>Update Facility Information</h1>
    <h2>Facility ID Number: <?php echo $FID; ?></h2>

    <form action="./edit.php?FID=<?php echo $FID; ?>" method="post"> Replace Information Please:
            <label for="Name">Name</label><br>
            <input type="text" name="Name" id="Name" value="<?= $facilityInfo["Name"] ?>"><br>
            <label for="Address">Address</label><br>
            <input type="text" name="Address" id="Address" value="<?= $facilityInfo["Address"] ?>"><br>
            <label for="PostalCode">Postal Code</label><br>
            <input type="text" name="PostalCode" id="PostalCode" value="<?= $facilityInfo["PostalCode"] ?>"><br>
            <label for="PhoneNumber">Telephone Number</label><br>
            <input type="text" name="PhoneNumber" id="PhoneNumber" value="<?= $facilityInfo["PhoneNumber"] ?>"><br>
            <label for="WebAddress">Website Address</label><br>
            <input type="text" name="WebAddress" id="WebAddress" value="<?= $facilityInfo["WebAddress"] ?>"><br>
            <label for="Type">Type of Facility</label><br>
            <input type="text" name="Type" id="Type" value="<?= $facilityInfo["Type"] ?>"><br>
            <label for="Capacity">Capacity</label><br>
            <input type="number" name="Capacity" id="Capacity" value="<?= $facilityInfo["Capacity"] ?>"><br>
            
            <label for="MedCardNumber">Manager's Medical Card Number</label><br>
            <select name="MedCardNumber" id="MedCardNumber">
                <option value="<?= $facilityInfo["MedCardNumber"] ?>"><?= $facilityInfo["MedCardNumber"] ?></option>
                <?php foreach($potentialManagers as $manages): ?>
                    <option value="<?php echo $manages['MedCardNumber']; ?>"><?php echo $manages['Fname']; ?> <?php echo $manages['Lname']; ?></option>
                <?php endforeach; ?>
            </select>
            <br>
            <input type="hidden" name="FID" id="FID" value="<?= $facilityInfo["FID"] ?>">
            <button type="submit">Update</button>
    </form>

    <a href="./">Back</a>
</body>
</html>