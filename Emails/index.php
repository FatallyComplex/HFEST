
<?php
ini_set('max_execution_time', 300); // set the maximum execution time to 300 seconds (5 minutes)
require_once './database.php';

////////////////////////////////////////////////////////////////////
// Query to create Facilities drop down menu
////////////////////////////////////////////////////////////////////
function getFacilities($conn) {
    $query = "SELECT FID, name FROM FACILITIES";
    $result = $conn->prepare($query);
    $result->execute();
    return $result->fetchAll(PDO::FETCH_ASSOC);
}

$facilities = getFacilities($conn);

// Loop through the list of facilities and create an option for each one
$options = '<option value="See All">See All</option>';
foreach ($facilities as $facility) {
    $options .= '<option value="' . $facility['FID'] . '">' . $facility['name'] . '</option>';
}

////////////////////////////////////////////////////////////////////
// Query 1 to read the email table
////////////////////////////////////////////////////////////////////
    $query1 = " SELECT m.Date, m.EmID, s.FID, r.MedCardNumber, e.Fname, e.Lname, e.Email, f.Name, f.Address, m.EmailSubject, m.Message
                FROM EMAILS m
                JOIN SENDS s ON m.emID = s.emID
                JOIN RECEIVES r ON m.emID = r.emID
                JOIN EMPLOYEE e ON r.MedCardNumber = e.MedCardNumber
                JOIN FACILITIES f ON f.FID = s.FID
                ORDER BY m.Date ASC;";

                // Check if the form has been submitted
                if (isset($_POST['FacilityID'])) {
                    $FacilityID = $_POST['FacilityID'];

                    // Update the query with the search filters
                    if ($FacilityID !== 'See All') {
                        $query1 = "SELECT m.Date, m.EmID, s.FID, r.MedCardNumber, e.Fname, e.Lname, e.Email, f.Name, f.Address, m.EmailSubject, m.Message
                        FROM EMAILS m
                        JOIN SENDS s ON m.emID = s.emID
                        JOIN RECEIVES r ON m.emID = r.emID
                        JOIN EMPLOYEE e ON r.MedCardNumber = e.MedCardNumber
                        JOIN FACILITIES f ON f.FID = s.FID
                        WHERE s.FID = $FacilityID
                        ORDER BY m.Date ASC;";
                    }
                }

    $result1 = $conn->prepare($query1);
    $result1->execute();
    $emails = $result1->fetchAll();

?>

<!DOCTYPE html>
<html>
<head>
    <title>HFESTS - Emails</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat|Open+Sans&display=swap" rel="stylesheet">
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
            margin-top: 25px;
        }
        h2 {
            color: #444444;
            text-align: center;
            margin-top: 20px;
            font-size: 20px;
        }
        form {
            display: flex;
            flex-direction: row;
            align-items: center;
            margin-top: 25px;
            max-width: 1000px;
            margin: 0 auto;
        }
        div {
        display: flex;
        justify-content: center;
        align-items: center;
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
        table {
            border-collapse: collapse;
            margin: 0 auto;
            max-width: 2400px;
            margin-top: 20px;
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
            background-color: #912338;
            font-weight: bold;
            color: white;
        }
        nav {
            display: flex;
            justify-content: flex-end;
            background-color: #912338;
            padding: 20px 10px;
            color: black;
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
            <li><a href="../Employees/">Search Employee</a></li>
            <li><a href="../Facilities/">Search Facility</a></li>
            <li><a href="./">Back</a></li>
        </ul>
    </nav>
    <h1>Emails</h1>

    <div>
    <form method="POST" action="">
        <label for="FacilityID">Select a Facility:</label>
 
        <?php
        // Initialize the $selected_FID variable to an empty string
        $selected_FID = '';

        // Check if the form has been submitted and the FacilityID field is set
        if (isset($_POST['FacilityID'])) {
            $selected_FID = $_POST['FacilityID'];
        }
        ?>

        <select id="FacilityID" name="FacilityID">       
            <?php echo $options; ?>        
        </select>
        <button type="submit">Search</button>
    </form>
    </div>
    <?php if ($emails): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Facility</th>
                    <th>Medical Card Number</th>
                    <th>Date</th>
                    <th>Subject</th>
                    <th>Message</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($emails as $email) :?>
                <?php
////////////////////////////////////////////////////////////////////
// Query 2 to find the schedule of the employee in the email
////////////////////////////////////////////////////////////////////
        $query2 = " SELECT MedCardNumber, FID, SDate, StartTime, EndTime
                    FROM SCHEDULES
                    WHERE MedCardNumber = '{$email['MedCardNumber']}'
                    AND SDate BETWEEN '{$email['Date']}' AND DATE_ADD('{$email['Date']}', INTERVAL 1 WEEK)";
        $result2 = $conn->prepare($query2);
        $result2->execute();
        $schedules = $result2->fetchAll();
 
        $start_date = new DateTime($email['Date']);
        $end_date = new DateTime($email['Date']);
        $end_date->modify('+1 week');
        $start = $start_date->format('Y-m-d');
        $end = $end_date->format('Y-m-d');

        $employee_name = "{$email['Fname']}"." {$email['Lname']}";
        $employee_email = "{$email['Email']}";
        $facility_name = "{$email['Name']}";
        $facility_address = "{$email['Address']}";
        $employee_message = "";
        $employee_message = $facility_name . " " . $facility_address . " Schedule for: " . $employee_name . " " . $employee_email;
        
        $Default_subject = "";
        $Default_subject .= $facility_name . " Schedule for " . $start . " to " . $end;
        
        $interval = new DateInterval('P1D');
        $date_range = new DatePeriod($start_date, $interval, $end_date);
        
        foreach ($date_range as $date) {
            // initialize the schedule information
            $schedule_found = false;
            $start_time = '';
            $end_time = '';
            
            // loop through the schedules and check if there is a schedule for the current date
            foreach ($schedules as $schedule) {
                if ($schedule['SDate'] == $date->format('Y-m-d')) {
                    $schedule_found = true;
                    $start_time = $schedule['StartTime'];
                    $end_time = $schedule['EndTime'];
                    break;
                }
            }
            
            // add the schedule information to the message
            $employee_message .= $date->format('Y-m-d') . " : ";
            if (!$schedule_found) {
                $employee_message .= "No Assignment.";
            } else {
                $employee_message .= $start_time . " to " . $end_time . ".";
            }
        }

        $employee_message_end = mb_substr($employee_message, 79, null, 'UTF-8');
        $Default_subject_end = mb_substr($Default_subject, 44, null, 'UTF-8');
      ?>
                
                    <tr>
                        <td><?php echo $email['EmID']; ?></td>
                        <td><?php echo $email['FID']; ?></td>
                        <td><?php echo $email['MedCardNumber']; ?></td>
                        <td><?php echo $email['Date']; ?></td>
                        <td>
                        <?php
                            if (strpos($email['EmailSubject'], 'Warning') === 0) {
                                echo $email['EmailSubject'];
                            } else {
                                echo $email['EmailSubject'] . $Default_subject_end;
                            }
                        ?>
                        </td>

                        <td>
                        <?php
                            if (strpos($email['Message'], 'One') === 0) {
                                echo $email['Message'];
                            } else {
                                echo $email['Message'] . $employee_message_end;
                            }
                        ?>
                    </td>

                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</body>
</html>
