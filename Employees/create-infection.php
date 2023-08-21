<?php
// Connect to the database and fetch all facilities
require_once '../database.php';
date_default_timezone_set('America/New_York');

// Get the medical card number from the url
$medical_card_number = isset($_GET['medical_card_number']) ? $_GET['medical_card_number'] : '';


$query = "SELECT DISTINCT INature FROM INFECTIONS";
$stmt = $conn -> prepare($query);
$stmt -> execute();
$natures = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html>
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
            margin: 5px;
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
<div class="container">
    <h1>Create New infection</h1>
    <form action="create-infection-action.php" method="post">
        <label for="medcardnumber">Medical Card Number:</label>
        <input type="text" id="medcardnumber" name="medcardnumber" value="<?php echo $medical_card_number ?>" readonly>
        <label for="nature" style="margin-top:25px;">Nature:</label>
        <select name="nature" id="nature" name="nature">
            <option value=""></option>
            <?php foreach ($natures as $facility): ?>
                <option value="<?php echo $facility['INature']; ?>"><?php echo $facility['INature']; ?></option>
            <?php endforeach; ?>
            <option value="variant">COVID-19 VARIANT</option>
            <option value="other">Other (Please specify)</option>
        </select>
        
        <div id="other-nature" style="display:none;">
            <label for="other-nature-input">Please specify:</label>
            <input type="text" name="other-nature-input" id="other-nature-input">
        </div>
        <div id="other-variant" style="display:none;">
            <label for="other-variant-input">Please specify:</label>
            <input type="text" name="other-variant-input" id="other-variant-input">
        </div>

        <label for="date">Date:</label>
        <input type="date" id="date" name="date" max="<?php echo date('Y-m-d'); ?>" required>

    
        <button type="submit" style="margin-top:50px;">Create Infection</button>
    </form>
</div>
<script>
    // Show the text input field when the "Other" option is selected
    document.getElementById("nature").addEventListener("change", function() {
        if (this.value === "other") {
            document.getElementById("other-nature").style.display = "block";
        }else if(this.value === "variant"){
            document.getElementById("other-variant").style.display = "block";
        }
         else {
            document.getElementById("other-nature").style.display = "none";
        }
    });
</script>
</body>
</html>