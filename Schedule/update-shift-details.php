<?php
require_once '../database.php';

// Get the medical card number, date, and start time of the shift to be updated
$medical_card_number = isset($_GET['medical_card_number']) ? $_GET['medical_card_number'] : '';
$date = isset($_GET['date']) ? $_GET['date'] : '';
$start_time = isset($_GET['start_time']) ? $_GET['start_time'] : '';
$end_time = isset($_GET['end_time']) ? $_GET['end_time'] : '';
$FID = isset($_GET['FID']) ? $_GET['FID'] : '';

// Fetch the shift details from the database
$query = "SELECT * FROM SCHEDULES WHERE MedCardNumber = ? AND SDate = ? AND StartTime = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$medical_card_number, $date, $start_time]);
$shift = $stmt->fetch(PDO::FETCH_ASSOC);

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
        <li><a href="./shift-details.php?medical_card_number=<?php echo $medical_card_number;?>&date=<?php echo $date;?>">Back</a></li>
    </ul>
</nav>

<h1>Update Shift Details</h1>
    <form action="update-shift-details-action.php" method="POST">
        <input type="hidden" name="medical_card_number" value="<?php echo $medical_card_number; ?>">
        <input type="hidden" name="old_date" value="<?php echo $date; ?>">
        <input type="hidden" name="old_time" value="<?php echo $start_time; ?>">
        <label>Facility:</label>
        <select name="FID" required>
            <?php
            // Fetch facilities from the database
            $query = "SELECT F.FID, F.Name, F.Type 
            FROM FACILITIES F 
            JOIN EMPLOYS E ON F.FID = E.FacilityId 
            WHERE E.MedCardNumber = ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([$medical_card_number]);
            while ($row = $stmt->fetch()) {
                $selected = ($row['FID'] == $shift['FID']) ? 'selected' : '';
                echo "<option value=\"{$row['FID']}\" $selected>{$row['Name']} ({$row['Type']})</option>";
            }
            ?>
        </select>
        <label>Date:</label>
        <input type="date" name="date" value="<?php echo $shift['SDate']; ?>" required min="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d', strtotime('+4 weeks')); ?>">
        <label>Start Time:</label>
        <input type="time" name="start_time" value="<?php echo $shift['StartTime']; ?>" required>
        <label>End Time:</label>
        <input type="time" name="end_time" value="<?php echo $shift['EndTime']; ?>" required>
        <button type="submit">Update</button>
    </form>
</body>
</html>