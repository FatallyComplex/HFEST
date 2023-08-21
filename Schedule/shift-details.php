<?php
require_once '../database.php';

// Get the medical card number and date from the url
$medical_card_number = isset($_GET['medical_card_number']) ? $_GET['medical_card_number'] : '';
$date = isset($_GET['date']) ? $_GET['date'] : '';

$popup_message ='';

if (isset($_GET['error'])) {
    // Get the error type from the URL
    $error = $_GET['error'];

    // Display an error message based on the error type
    if ($error == 'conflict') {
        $popup_message = 'There is a conflict with another shift for this date and time. Please select a different date or time.';
    } elseif ($error == 'infection') {
        $popup_message = 'The selected date is within 14 days of an infection. Please select a different date.';
    } elseif ($error == 'buffer') {
        $popup_message = 'You did not give a sufficient buffer of 1 hour. Please select a different date.';
    }

}

// Check if there is a success message
if (isset($_GET['success'])) {
    $success_message = '';

    // Set the success message based on the type of success
    switch ($_GET['success']) {
        case 'update':
            $popup_message = 'Shift details have been updated successfully!';
            break;
        // Add more cases for different success types if needed
    }
}

// Fetch shifts for the user on the specified date
$query = "SELECT SDate, Name, Address, City, Province, Type, StartTime, EndTime, s.FID FROM SCHEDULES s, FACILITIES f, LOCATION l WHERE s.FID = f.FID AND f.PostalCode = l.PostalCode AND MedCardNumber = ? AND SDate = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$medical_card_number, $date]);
$shifts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$six_months_ago = new DateTime('-6 months');
$query = "SELECT * FROM VACCINES WHERE MedCardNumber = :medical_card_number AND VDate >= :six_months_ago";
$stmt = $conn->prepare($query);
$stmt->bindValue(':medical_card_number', $medical_card_number);
$stmt->bindValue(':six_months_ago', $six_months_ago->format('Y-m-d'));
$stmt->execute();
$vaccinations = $stmt->fetchAll(PDO::FETCH_ASSOC);

$has_vaccine = true;

if(!$vaccinations){
    $has_vaccine = false;
}


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

        /* Modal styles */
        #popup {
        display: none; /* Hidden by default */
        position: fixed; /* Stay in place */
        z-index: 9999; /* Sit on top */
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto; /* Enable scroll if needed */
        background-color: rgba(0, 0, 0, 0.5); /* Black w/ opacity */
        }

        /* Modal content */
        .popup-content {
        background-color: #fefefe;
        margin: 15% auto; /* 15% from the top and centered */
        padding: 20px;
        border: 1px solid #888;
        width: 30%; /* Could be more or less, depending on screen size */
        text-align: center;
        }

        /* Close button */
        .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        }

        .close:hover,
        .close:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
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
        <li><a href="./?medical_card_number=<?php echo $medical_card_number;?>">Back</a></li>
    </ul>
</nav>

<h2>Shift Details for <?php echo $date; ?></h2>

    <?php if ($shifts): ?>
        <table>
            <thead>
                <tr>
                    <th>Shift Date</th>
                    <th>Facility Name</th>
                    <th>Facility Type</th>
                    <th>Facility Address</th>
                    <th>Facility City</th>
                    <th>Facility Province</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($shifts as $shift): ?>
                    <tr>
                        <td><?php echo $shift['SDate']; ?></td>
                        <td><?php echo $shift['Name']; ?></td>
                        <td><?php echo $shift['Type']; ?></td>
                        <td><?php echo $shift['Address']; ?></td>
                        <td><?php echo $shift['City']; ?></td>
                        <td><?php echo $shift['Province']; ?></td>
                        <td><?php echo $shift['StartTime']; ?></td>
                        <td><?php echo $shift['EndTime']; ?></td>
                        <td>
                            <form action="update-shift.php" method="POST">
                                <input type="hidden" name="medical_card_number" value="<?php echo $medical_card_number; ?>">
                                <input type="hidden" name="date" value="<?php echo $date; ?>">
                                <input type="hidden" name="start_time" value="<?php echo $shift['StartTime']; ?>">
                                <input type="hidden" name="end_time" value="<?php echo $shift['EndTime']; ?>">
                                <input type="hidden" name="FID" value="<?php echo $shift['FID']; ?>">
                                <?php if ($has_vaccine): ?>
                                    <button type="submit">
                                        Update
                                    </button>
                                <?php endif; ?>
                            </form>
                            <form action="delete-shift.php" method="POST">
                                <input type="hidden" name="medical_card_number" value="<?php echo $medical_card_number; ?>">
                                <input type="hidden" name="date" value="<?php echo $date; ?>">
                                <input type="hidden" name="start_time" value="<?php echo $shift['StartTime']; ?>">
                                <button type="submit" onclick="return confirm('Are you sure you want to delete this shift?')">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No shifts found on this date.</p>
    <?php endif; ?>
    <div id="popup" style="display:none;">
    <div class="popup-content">
        <span class="close">&times;</span>
        <p id="popup-message"></p>
    </div>
</div>


</body>
<script>
    // Get the error message from the PHP code
    var popupMessage = '<?php echo isset($popup_message) ? $popup_message : ''; ?>';

    // If there is an error message, show the popup
    if (popupMessage !== '') {
        // Set the popup message
        document.getElementById('popup-message').innerHTML = popupMessage;

        // Show the popup
        document.getElementById('popup').style.display = 'block';
    }

    // Get the close button element
    var closeButton = document.getElementsByClassName("close")[0];

    // Hide the popup when the user clicks on the close button
    closeButton.onclick = function() {
        document.getElementById('popup').style.display = "none";
    }
</script>

</html>
