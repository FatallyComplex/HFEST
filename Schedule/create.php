<?php
require_once '../database.php';
date_default_timezone_set('America/New_York');

// Get the medical card number from the url
$medical_card_number = isset($_GET['medical_card_number']) ? $_GET['medical_card_number'] : '';

// Get the facilities currently employed at
$query = "SELECT * FROM FACILITIES WHERE FID IN 
          (SELECT FacilityId FROM FACILITIES f, EMPLOYS e WHERE f.FID = e.FacilityId AND MedCardNumber = :medical_card_number)";
$stmt = $conn->prepare($query);
$stmt->bindValue(':medical_card_number', $medical_card_number);
$stmt->execute();
$facilities = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get the COVID-19 infection dates for the employee
$query = "SELECT IDate FROM INFECTIONS WHERE MedCardNumber = :medical_card_number AND INature LIKE '%COVID-19%' ORDER BY IDate DESC";
$stmt = $conn->prepare($query);
$stmt->bindValue(':medical_card_number', $medical_card_number);
$stmt->execute();
$infection_dates = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Get the current date
$current_date = new DateTime();
$current_date_str = $current_date->format('Y-m-d');

// Calculate the max date for the date picker
$max_date = clone $current_date;
$max_date->modify('+4 weeks');




// If there are any infection dates, calculate the max infection date
if (!empty($infection_dates)) {
    $max_infection_date = new DateTime($infection_dates[0]['IDate']);
    $max_infection_date->modify('+2 weeks');
    if ($max_infection_date > $max_date) {
        $max_date = $max_infection_date;
    }
} else {
    // If there is no infection date, show up to 4 weeks in the future
    $max_date->modify('+4 weeks');
}

// Generate the dates array up to the max date
$dates = array();
$current_date = new DateTime();
$interval = new DateInterval('P1D');
$daterange = new DatePeriod($current_date, $interval, $max_date);
foreach ($daterange as $date) {
    $dates[] = $date->format('Y-m-d');
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
            margin-top: 10px;
            margin-bottom: 10px;
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.1/themes/smoothness/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.13.1/jquery-ui.min.js"></script>
    <script>
    $(function() {
        $( ".datepicker" ).datepicker({
        minDate: 0,
        maxDate: "+4w",
        dateFormat: "yy-mm-dd",
        beforeShowDay: function(date) {
            // Convert the date to a string in YYYY-MM-DD format
            var dateString = $.datepicker.formatDate('yy-mm-dd', date);

            <?php if (!empty($infection_dates)): ?>
            // Check if the date is within 2 weeks of the latest infection date
            var latestInfectionDate = '<?php echo $infection_dates[0]['IDate'] ?>';
            var latestInfectionDateObj = new Date(latestInfectionDate);
            var twoWeeksFromLatestInfectionDate = new Date(latestInfectionDateObj.getTime() + 14 * 24 * 60 * 60 * 1000);
            var dateObj = new Date(dateString);
            if (dateObj >= latestInfectionDateObj && dateObj <= twoWeeksFromLatestInfectionDate) {
                return [false];
            }
            <?php endif; ?>

            <?php if (empty($infection_dates)): ?>
            // Check if the date is more than 4 weeks in the future
            var maxDate = new Date();
            maxDate.setDate(maxDate.getDate() + 28);
            var dateObj = new Date(dateString);
            if (dateObj > maxDate) {
                return [false];
            }
            <?php endif; ?>

            // Enable all other dates
            return [true];
        }
    });
    $('#facility').on('change', function() {
        // Get the selected facility and medical card number
        var facilityId = $(this).val();
        var medicalCardNumber = $('#medcardnumber').val();
        
        // Send an AJAX request to the PHP script to get the available dates
        $.ajax({
            url: 'get-available-dates.php',
            method: 'POST',
            data: {
                facilityId: facilityId,
                medicalCardNumber: medicalCardNumber
            },
            success: function(result) {
                // Update the options in the date select element with the available dates
                var dateSelect = $('#date');
                dateSelect.html('<option value="">Select...</option>');
                var dates = JSON.parse(result);
                for (var i = 0; i < dates.length; i++) {
                    var date = dates[i];
                    dateSelect.append('<option value="' + date + '">' + date + '</option>');
                }
            }
        });
    });
    function checkConflicts(shifts, selectedStartTime, selectedEndTime) {
        console.log('checkConflicts called with', shifts, selectedStartTime, selectedEndTime);
        for (var i = 0; i < shifts.length; i++) {
            var shift = shifts[i];
            var shiftStartTime = shift['StartTime'];
            var shiftEndTime = shift['EndTime'];
            
              // Create new Date objects with the current date and shift start/end times
            var currentDateString = new Date().toDateString();
            var shiftStartDateTime = new Date(currentDateString + ' ' + shiftStartTime);
            var shiftEndDateTime = new Date(currentDateString + ' ' + shiftEndTime);
            var selectedStartDateTime = new Date(currentDateString + ' ' + selectedStartTime);
            var selectedEndDateTime = new Date(currentDateString + ' ' + selectedEndTime);

            // Calculate the time difference in minutes between the selected shift and existing shifts
            var timeDiffStart = (selectedStartDateTime.getTime() - shiftEndDateTime.getTime()) / (1000 * 60);
            var timeDiffEnd = (shiftStartDateTime.getTime() - selectedEndDateTime.getTime()) / (1000 * 60);

            console.log("time diff", timeDiffStart, timeDiffEnd);

            // Check if there is a conflict
            if (timeDiffStart < 60 && timeDiffEnd < 60) {
                return true;
            }
            
            if(selectedStartDateTime > selectedEndTime){
                return true;
            }

            if ((selectedStartTime > shiftStartTime && selectedStartTime < shiftEndTime) ||
                (selectedEndTime > shiftStartTime && selectedEndTime < shiftEndTime) ||
                (selectedStartTime < shiftStartTime && selectedEndTime > shiftEndTime)) {
                return true;
            }
        }
        return false;
    }

    $('#date, #start_time, #end_time').on('change', function() {
        

        var date = $('#date').val();
        var facilityId = $('#facility').val();
        var medicalCardNumber = $('#medcardnumber').val();
        var startTime = $('#start_time').val();
        var endTime = $('#end_time').val();

        if (date && startTime && endTime) {
            $.ajax({
                url: 'check-shift-conflicts.php',
                method: 'POST',
                data: {
                    medicalCardNumber: medicalCardNumber,
                    Sdate: date,
                },
                success: function(result) {
                    var shifts = JSON.parse(result);
                    var hasConflict = checkConflicts(shifts, startTime, endTime);
                    
                    if (hasConflict) {
                        alert('The selected shift cannot work. Please choose a different time.');
                        $('#start_time, #end_time').val('');
                    }
                }
            });
        }

        if(date &&startTime &&endTime && (endTime < startTime)){
            alert('This shift cannot exist. Please choose a different time.')
            $('#end_time').val('');
        }

    });
});

    </script>
</head>
<body>
<nav>
    <ul>
        <li><a href="../">Home</a></li>
        <li><a href="../Employees">Search Employees</a></li>
        <li><a href="../Facilities">Search Facilities</a></li>
        <li><a href="../Schedule/?medical_card_number=<?php echo $medical_card_number?>">Back</a></li>
    </ul>
</nav>
<div class="container">
    <h1>Create New Shift</h1>
    <form action="create-shift.php" method="post">
        <label for="medcardnumber">Medical Card Number:</label>
        <input type="text" id="medcardnumber" name="medcardnumber" value="<?php echo $medical_card_number ?>" readonly>
        <label for="facility">Facility:</label>
        <select name="facility" id="facility" name="facility">
            <option value="">Select...</option>
            <?php foreach ($facilities as $facility): ?>
                <option value="<?php echo $facility['FID']; ?>"><?php echo $facility['Name']; ?></option>
            <?php endforeach; ?>
        </select>
    
        <label for="date">Date:</label>
        <input type="text" id="date" name="date" class="datepicker" required>
    
        <label for="start_time">Start Time:</label>
        <input type="time" id="start_time" name="start_time" required>
    
        <label for="end_time">End Time:</label>
        <input type="time" id="end_time" name="end_time" required>
    
        <button type="submit">Create Shift</button>
    </form>
</div>
</body>
</html>