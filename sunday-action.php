
<?php
ini_set('max_execution_time', 300); // set the maximum execution time to 300 seconds (5 minutes)
require_once './database.php';

    $query1 = " SELECT EMPLOYS.MedCardNumber, FacilityID, StartDate, EndDate, Name, Fname, Lname, FACILITIES.Address, EMPLOYEE.Email
                FROM EMPLOYS
                JOIN FACILITIES ON EMPLOYS.FacilityID = FACILITIES.FID
                JOIN EMPLOYEE ON EMPLOYS.MedCardNumber = EMPLOYEE.MedCardNumber
                WHERE EndDate IS NULL";
    $result1 = $conn->prepare($query1);
    $result1->execute();
    $employees = $result1->fetchAll();

    // get the last inserted EmID from the EMAILS table
    $query_last_emid = "SELECT EmID 
                        FROM EMAILS 
                        ORDER BY EmID DESC 
                        LIMIT 1";
    $result_last_emid = $conn->prepare($query_last_emid);
    $result_last_emid->execute();
    $last_emid = $result_last_emid->fetchColumn();


    foreach ($employees as $row) {

        $query2 = " SELECT MedCardNumber, FID, SDate, StartTime, EndTime
                    FROM SCHEDULES
                    WHERE MedCardNumber = '{$row['MedCardNumber']}'
                    AND SDate BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 1 WEEK)";
        $result2 = $conn->prepare($query2);
        $result2->execute();
        $schedules = $result2->fetchAll();
 
        $start_date = new DateTime();
        $end_date = new DateTime();
        $end_date->modify('+1 week');

        $employee_name = "{$row['Fname']}"." {$row['Lname']}";
        $employee_email = "{$row['Email']}";
        $facility_name = "{$row['Name']}";
        $facility_address = "{$row['Address']}";
        $employee_message = $facility_name . " " . $facility_address . " Schedule for: " . $employee_name . " " . $employee_email;

        $start = $start_date->format('Y-m-d');
        $end = $end_date->format('Y-m-d');
        
        $Default_subject = " ";
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
        
        // increment EmID for each record
        $emid = $last_emid + 1;

        // insert the email into EMAILS
        $query3 = "INSERT INTO EMAILS (EmID, EmployeeName, FacilityName, Date, EmailSubject, Message) 
        VALUES ($emid, '{$employee_name}', '{$facility_name}', CURDATE(), :subject, :message)";

        $result3 = $conn->prepare($query3);
        $result3->bindParam(':message', $employee_message, PDO::PARAM_STR);
        $result3->bindParam(':subject', $Default_subject, PDO::PARAM_STR);
        $result3->execute();

        // insert the SENDS relationship
        $query4 = "INSERT INTO SENDS (EmID, FID) 
                VALUES ($emid, '{$row['FacilityID']}')";
        $result4 = $conn->prepare($query4);
        $result4->execute();

        // insert the RECEIVES relationship
        $query5 = "INSERT INTO RECEIVES (EmID, MedCardNumber) 
                VALUES ($emid, '{$row['MedCardNumber']}')";
        $result5 = $conn->prepare($query5);
        $result5->execute();

        // set last_emid to the current emid for the next iteration
        $last_emid = $emid;
    }

?>
