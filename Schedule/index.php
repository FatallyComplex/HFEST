<?php
require_once '../database.php';

date_default_timezone_set('America/New_York');

// Get the medical card number from the url
$medical_card_number = isset($_GET['medical_card_number']) ? $_GET['medical_card_number'] : '';

// Fetch shifts for the user
$query = "SELECT SDate, FID, StartTime, EndTime FROM SCHEDULES WHERE MedCardNumber = ? AND SDate <= DATE_ADD(CURDATE(), INTERVAL 4 WEEK)";
$stmt = $conn->prepare($query);
$stmt->execute([$medical_card_number]);
$shifts = $stmt->fetchAll(PDO::FETCH_ASSOC);

$query = "SELECT IDate FROM INFECTIONS WHERE MedCardNumber = ? AND INature LIKE '%COVID-19%'";
$stmt = $conn->prepare($query);
$stmt->execute([$medical_card_number]);
$infections = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organize shifts by date
$shifts_by_date = [];
foreach ($shifts as $shift) {
    $date = $shift['SDate'];
    if (!isset($shifts_by_date[$date])) {
        $shifts_by_date[$date] = [];
    }
    $shifts_by_date[$date][] = $shift;
}

function is_within_14_days_of_infection($date, $infections) {
    foreach ($infections as $infection) {
        $infection_date = $infection['IDate'];
        $infection_start = $infection_date;
        $infection_end = date('Y-m-d', strtotime($infection_date . ' +14 days'));

        if ($date >= $infection_start && $date <= $infection_end) {
            return true;
        }
    }
    return false;
}

function generate_calendar($year, $month, $shifts_by_date, $medical_card_number, $infections) {
    $first_day_of_month = mktime(0, 0, 0, $month, 1, $year);
    $days_in_month = date('t', $first_day_of_month);
    $month_name = date('F', $first_day_of_month);
    $day_of_week = date('w', $first_day_of_month);

    $prev_month = $month - 1;
    $prev_year = $year;
    if ($prev_month < 1) {
        $prev_month = 12;
        $prev_year -= 1;
    }
    $next_month = $month + 1;
    $next_year = $year;
    if ($next_month > 12) {
        $next_month = 1;
        $next_year += 1;
    }

    $calendar = "";

    // Start generating the calendar table
    $calendar .= "<table>";

    

    $calendar .= "<caption><button onclick=\"window.location.href='?medical_card_number={$medical_card_number}&year={$prev_year}&month={$prev_month}'\">&laquo; Prev</button> $month_name $year <button onclick=\"window.location.href='?medical_card_number={$medical_card_number}&year={$next_year}&month={$next_month}'\">Next &raquo;</button></caption>";
    $calendar .= "<tr><th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th></tr>";
    $calendar .= "<tr>";

    // Pad the table with empty cells for days of the previous month
    for ($i = 0; $i < $day_of_week; $i++) {
        $calendar .= "<td></td>";
    }

    // Fill in the calendar with dates and shift icons
    for ($day = 1; $day <= $days_in_month; $day++) {
        if (($day + $day_of_week - 1) % 7 == 0 && $day != 1) {
            $calendar .= "</tr><tr>";
        }
        $date = date('Y-m-d', mktime(0, 0, 0, $month, $day, $year));
        $has_shift = isset($shifts_by_date[$date]);
        
        
        $is_today = ($date == date('Y-m-d'));
        $icon_color = $is_today ? 'white' : 'black';
        $link_color = $is_today ? 'white' : 'black';

        //$shift_icon = $has_shift ? '<i class="fas fa-user-clock" style="color: '.$icon_color.';"></i>' : '';

        if ($is_today) {
            $calendar .= "<td style=\"background-color: #912338; color: white;\">"; // Set the background color and text color
        } else {
            $calendar .= "<td>";
        }
        // Inside the for loop for the days in the month
        $within_infection_period = is_within_14_days_of_infection($date, $infections);
        $infection_icon = $within_infection_period ? '<span style="color: red;">X</span>' : '';

        if ($has_shift) {
            // Replace 'shift-details.php' with the name of the page where the shift details are displayed
            $calendar .= "<a href='shift-details.php?medical_card_number={$medical_card_number}&date=$date' style=\"color: $link_color;\">$day $infection_icon <i class=\"fas fa-user-clock\" style=\"color: $icon_color;\"></i></a></td>";
        } else {
            $calendar .= "$day $infection_icon</td>";
        }
    }

    // Pad the table with empty cells for days of the next month
    while (($day + $day_of_week - 1) % 7 != 0) {
        $calendar .= "<td></td>";
        $day++;
    }

    $calendar .= "</tr>";
    $calendar .= "</table>";
    $calendar .= "<div style=\"text-align: center; margin-top: 20px;\"><button onclick=\"window.location.href='?medical_card_number={$medical_card_number}'\">Current Month</button></div>";

    return $calendar;
}

$selected_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$selected_month = isset($_GET['month']) ? intval($_GET['month']) : date('n');

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
            margin-top: 20px;
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
<script>
    function validateForm() {
        const startYear = document.getElementById("start_date").value;
        const startMonth = document.getElementById("end_date").value;

        if (startYear === "yyyy-mm-dd" || startMonth === "yyyy-mm-dd") {
            alert("Please select a valid date range.");
            return false;
        }

        if (startMonth <= startYear) {
            alert("End date must be after the start date.");
            return false;
        }

        return true;
    }
    function validateGo(){
        if(year === "" || month === ""){
            alert("Please select a valid date.")
            return false;
        }
        return true;
    }
</script>
</head>
<body>
<nav>
    <ul>
        <li><a href="../">Home</a></li>
        <li><a href="../Employees">Search Employees</a></li>
        <li><a href="../Facilities">Search Facilities</a></li>
        <li><a href="../Employees/details.php?medical_card_number=<?php echo $medical_card_number?>">Back</a></li>
    </ul>
</nav>
<h1>Schedule for employee <?php echo $medical_card_number?></h1>

<div style="display: flex; justify-content: space-evenly; padding: 20px;">
    <div>
        <h2>Specific month display on calendar</h2>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="get" onsubmit="return validateGo()" style="text-align: center; margin-top: 20px;">
                <input type="hidden" name="medical_card_number" value="<?php echo $medical_card_number ?>">
                <div style="display: inline-block; text-align: left;">
                    <label for="year">Year:</label>
                    <select name="year" id="year">
                        <option value="">Select...</option>
                        <?php for ($i = -1; $i <= 1; $i++): ?>
                            <?php $year = date('Y') + $i; ?>
                            <option value="<?php echo $year ?>" <?php if ($year == $selected_year) echo 'selected' ?>><?php echo $year ?></option>
                        <?php endfor; ?>
                    </select>

                    <label for="month">Month:</label>
                    <select name="month" id="month">
                        <option value="">Select...</option>
                        <?php for ($month = 1; $month <= 12; $month++): ?>
                        <option value="<?php echo $month ?>"<?php if ($month == $selected_month) echo ' selected' ?>><?php echo date('F', mktime(0, 0, 0, $month, 10)) ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div style="margin-top: 10px;">
                    <input type="submit" value="Go" style="font-size: 18px; color: white; background-color: #3f3f3f; padding: 5px 10px; border: none; border-radius: 5px; cursor: pointer; transition: background-color 0.3s ease-in-out;" onmouseover="this.style.backgroundColor='#1c1c1c';" onmouseout="this.style.backgroundColor='#3f3f3f';">
                </div>
        </form>
    </div>
    <div>
        <h2>Specific range display on calendar</h2>
        <form action="schedule-range.php" method="get" onsubmit="return validateForm()" style="text-align: center;">
            <input type="hidden" name="medical_card_number" value="<?php echo $medical_card_number ?>">
            
            <div style="display: inline-block; text-align: left;">
                <label for="start_date">Start Date:</label>
                <input type="date" id="start_date" name="start_date" min="<?php echo date('Y-m-d', strtotime('-2 years')); ?>" max="<?php echo date('Y-m-d', strtotime('+4 weeks')); ?>" value="<?php echo $_GET['start_date'] ?? ''; ?>">
            </div>

            <div style="display: inline-block; text-align: left;">
                <label for="end_date">End Date:</label>
                <input type="date" id="end_date" name="end_date" min="<?php echo date('Y-m-d', strtotime('-2 years')); ?>" max="<?php echo date('Y-m-d', strtotime('+4 weeks')); ?>" value="<?php echo $_GET['end_date'] ?? ''; ?>">
            </div>
            <div style="margin-top: 10px;">
            <input type="submit" value="Go" style="font-size: 18px; color: white; background-color: #3f3f3f; padding: 5px 10px; border: none; border-radius: 5px; cursor: pointer; transition: background-color 0.3s ease-in-out;" onmouseover="this.style.backgroundColor='#1c1c1c';" onmouseout="this.style.backgroundColor='#3f3f3f';">
            </div>
        </form>
    </div>
</div>

<?php
$year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$month = isset($_GET['month']) ? intval($_GET['month']) : date('m');
// Display the calendar for the specified month and year
echo generate_calendar($year, $month, $shifts_by_date, $medical_card_number, $infections);

?>

<h2>Create shift</h2>
<div style="text-align: center;">
  <?php 
    // Check if the employee has had a COVID-19 vaccine in the past 6 months
    $six_months_ago = new DateTime('-6 months');
    $query = "SELECT * FROM VACCINES WHERE MedCardNumber = :medical_card_number AND VDate >= :six_months_ago";
    $stmt = $conn->prepare($query);
    $stmt->bindValue(':medical_card_number', $medical_card_number);
    $stmt->bindValue(':six_months_ago', $six_months_ago->format('Y-m-d'));
    $stmt->execute();
    $vaccinations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($vaccinations)) {
      // If the employee has had a COVID-19 vaccine in the past 6 months, allow them to create a new shift
      echo '<button onclick="location.href=\'create.php?medical_card_number='.$medical_card_number.'\'">Create New Shift</button>';
    } else {
      // If the employee has not had a COVID-19 vaccine in the past 6 months, show a message and do not allow them to create a new shift
      echo '<p>You must have had at least one COVID-19 vaccine in the past 6 months to create a new shift.</p>';
    }
  ?>
</div>

</body>
</html>
