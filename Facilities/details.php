<?php
// Connect to the database and fetch facility info
require_once '../database.php';
date_default_timezone_set('America/New_York');

// Get the facility ID from the URL
$FID = isset($_GET['FID']) ? $_GET['FID'] : '';

$query = 'SELECT f.FID, f.Name, f.Address, l.City, l.Province, f.PostalCode, f.PhoneNumber, f.WebAddress, f.Type, f.Capacity, m.MedCardNumber, e.Fname, e.Lname, 
(SELECT COUNT(*) FROM EMPLOYS WHERE EndDate IS NULL AND FacilityID = f.FID) AS NumEmployees 
FROM FACILITIES f
JOIN LOCATION l ON f.PostalCode = l.PostalCode
JOIN MANAGES m ON f.FID = m.FID
JOIN EMPLOYEE e ON m.MedCardNumber = e.MedCardNumber 
JOIN EMPLOYS z ON f.FID = z.FacilityID
WHERE f.FID = ?
GROUP BY f.FID
ORDER BY l.Province ASC, l.City ASC, f.Type ASC, NumEmployees ASC';

$stmt = $conn->prepare($query);
$stmt->execute([$FID]);
$facilityInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);

/////////////////////////////////////////////////////////
// Second Query
/////////////////////////////////////////////////////////
$query2 = 'SELECT s.FID, s.SDate, s.MedCardNumber, e.Fname AS FirstName, e.Lname AS LastName, e.Role 
FROM SCHEDULES s
JOIN EMPLOYEE e ON s.MedCardNumber = e.MedCardNumber
WHERE s.FID = ? AND (e.Role = "Nurse" OR e.Role = "Doctor") AND s.SDate BETWEEN DATE_SUB(NOW(), INTERVAL 2 WEEK) AND NOW()
GROUP BY s.MedCardNumber
ORDER BY e.Role ASC, FirstName ASC';

$stmt2 = $conn->prepare($query2);
$stmt2->execute([$FID]);
$facilityInfo2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);

/////////////////////////////////////////////////////////
// Third Query
/////////////////////////////////////////////////////////
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
$results3 ='';
if(isset($_GET['start_date']) && isset($_GET['end_date'])){
$query3 = 'SELECT Role, FID, SUM(TIME_TO_SEC(TIMEDIFF(EndTime, StartTime))) / 3600 AS TotalHoursScheduled
FROM SCHEDULES
JOIN EMPLOYEE ON SCHEDULES.MedCardNumber = EMPLOYEE.MedCardNumber
WHERE FID = ? AND SDate BETWEEN ? AND ?
GROUP BY FID, Role
ORDER BY Role ASC';
$stmt3 = $conn->prepare($query3);
$stmt3->execute([$FID, $start_date, $end_date]);
$results3 = $stmt3->fetchAll(PDO::FETCH_ASSOC);
}
/////////////////////////////////////////////////////////
// Fourth Query
/////////////////////////////////////////////////////////
$query4 = 'SELECT e.MedCardNumber, e.Fname, e.Lname, e.Role, z.StartDate, e.DOB, e.Email, e.PhoneNumber, e.Address, l.Province, l.City, e.PostalCode, e.Citizenship
FROM EMPLOYEE e
JOIN EMPLOYS z ON e.MedCardNumber = z.MedCardNumber
JOIN LOCATION l on e.PostalCode = l.PostalCode
WHERE z.FacilityID = ? AND z.EndDate IS NULL
GROUP BY e.MedCardNumber
ORDER BY e.Role ASC, e.Fname ASC, e.Lname ASC;';

$employees = $conn->prepare($query4);
$employees->execute([$FID]);

?>

<!DOCTYPE html>
<html>
<head>
    <title>HFESTS - Facility Details</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat|Open+Sans&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #f7f7f7;
        }
        h1 {
            color: #333333;
            text-align: center;
            margin-top: 50px;
        }
        h2 {
            color: #444444;
            text-align: center;
            margin-top: 20px;
            font-size: 20px;
        }
        table {
            border-collapse: collapse;
            margin: 0 auto;
            max-width: 2400px;
            margin-top: 10px;
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
        button {
            background-color: #f2f2f2;
            border: none;
            border-radius: 5px;
            color: #666666;
            cursor: pointer;
            font-size: 16px;
            margin: 20px;
            padding: 10px 20px;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #dddddd;
        }
        p{
            text-align: center;  
            margin-top: 20px;
            font-size: 20px;
            color: red;
        }

    </style>
    <a href="/HFESTS/">
        <img src="https://img.freepik.com/premium-vector/modern-letter-h-f-elegant-abstract-logo_649646-350.jpg?w=996" alt="HFESTS logo" style="position: absolute; top: 10px; left: 10px; width: 150px; height: auto;">
    </a>
</head>

<!-- ///////////////////////////////////////////////////////// -->
<!-- BODY                                                      -->
<!-- ///////////////////////////////////////////////////////// -->
<body>
<nav>
        <ul>
            <li><a href="../">Home</a></li>
            <li><a href="../Employees/">Search Employee</a></li>
            <li><a href="../Emails/">Search Email</a></li>
            <li><a href="./">Back</a></li>
        </ul>
    </nav>
<h1>Facility Information</h1>
    <h2>Facility ID: <?php echo $FID; ?></h2>

    <?php if ($facilityInfo): ?>

        <table>
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Address</th>
                    <th>City</th>
                    <th>Province</th>
                    <th>Postal Code</th>
                    <th>Phone Number</th>
                    <th>Web Address</th>
                    <th>Type</th>
                    <th>Capacity</th>
                    <th>Manager</th>
                    <th>Number of Employees</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($facilityInfo as $finfo): ?>
                <tr>
                    <td><?php echo $finfo['Name']; ?></td>
                    <td><?php echo $finfo['Address']; ?></td>
                    <td><?php echo $finfo['City']; ?></td>
                    <td><?php echo $finfo['Province']; ?></td>
                    <td><?php echo $finfo['PostalCode']; ?></td>
                    <td><?php echo $finfo['PhoneNumber']; ?></td>
                    <td><?php echo $finfo['WebAddress']; ?></td>
                    <td><?php echo $finfo['Type']; ?></td>
                    <td><?php echo $finfo['Capacity']; ?></td>
                    <td><?php echo $finfo['Fname'] . ' ' . $finfo['Lname']; ?></td>
                    <td><?php echo $finfo['NumEmployees']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <?php else: ?>
            <p>No facility found for this ID.</p>
            <?php endif; ?>
        </table>
    <div style="display: flex; justify-content: space-evenly;">
    <div style="display: inline;">
    <!-- ///////////////////////////////////////////////////////// -->
    <!-- Second Query                                              -->
    <h2>Doctors and nurses who have been on schedule to work in the last two weeks</h2>
    <!-- ///////////////////////////////////////////////////////// -->

        <table>
            <thead>
                <tr>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Role</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($facilityInfo2): ?>
                    <?php foreach ($facilityInfo2 as $row2): ?>
                        <tr>
                            <td><?php echo $row2['FirstName']; ?></td>
                            <td><?php echo $row2['LastName']; ?></td>
                            <td><?php echo $row2['Role']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No nurse or doctor worked here in the last 2 weeks.</p>
                <?php endif; ?>
            </tbody>
        </table>
    

                </div>
            <div style="display: inline;">
    <!-- ///////////////////////////////////////////////////////// -->
    <!-- Third Query                                               -->
    <h2>Select a date range to display all scheduled shifts between those dates</h2>
    <!-- ///////////////////////////////////////////////////////// -->

        <form action="" method="get" onsubmit="return validateForm()" style="text-align: center;">
            <input type="hidden" name="FID" value="<?php echo $FID ?>">

            <div style="display: inline-block; text-align: left;">
                <label for="start_date">Start:</label>
                <input type="date" name="start_date" id="start_date" value="<?php echo date('Y-m-d', strtotime($start_date)); ?>"  max="<?php echo date('Y-m-d', strtotime(date('Y-m-d') . '+4 weeks')); ?>">
            </div>

            <div style="display: inline-block; text-align: left;">
                <label for="end_date">End:</label>
                <input type="date" name="end_date" id="end_date" value="<?php echo date('Y-m-d', strtotime($end_date)); ?>"  max="<?php echo date('Y-m-d', strtotime(date('Y-m-d') . '+4 weeks')); ?>">
            </div>

            <div style="margin-top: 10px;">
            <input type="submit" value="Go" style="font-size: 18px; color: white; background-color: #3f3f3f; padding: 5px 10px; border: none; border-radius: 5px; cursor: pointer; transition: background-color 0.3s ease-in-out;" onmouseover="this.style.backgroundColor='#1c1c1c';" onmouseout="this.style.backgroundColor='#3f3f3f';">
            </div>
        </form>

        <table>
        <thead>
            <tr>
                <th>Role</th>
                <th>Total Hours Scheduled</th>
            </tr>
        </thead>
            <tbody>
                <?php if($results3):?>
                <?php foreach ($results3 as $result): ?>

                    <tr>
                        <td><?php echo $result['Role']; ?></td>
                        <td><?php echo round($result['TotalHoursScheduled']); ?> hours</td>
                    </tr>

                <?php endforeach; ?>
                <?php endif;?>
                <?php if (!$results3): ?>
                <tr>
                <td colspan="2">No shifts were scheduled between those dates.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
                </div>
                </div>
    <!-- ///////////////////////////////////////////////////////// -->
    <!-- Fourth Query                                              -->
    <h2>Employee(s) working there</h2>
    <!-- ///////////////////////////////////////////////////////// -->

    <?php if ($employees): ?>
        <table>
            <thead>
                <tr>
                    <th>Medicare Card Number</th>
                    <th>First Name</th>    
                    <th>Last Name</th>
                    <th>Start Date Of Work</th>
                    <th>Date Of Birth</th>
                    <th>Telephone-Number</th>
                    <th>Address</th>
                    <th>City</th>
                    <th>Province</th>
                    <th>Postal-Code</th>
                    <th>Citizenship</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($employees as $row): ?>
                    <tr>
                        <td><?php echo $row['MedCardNumber']; ?></td>
                        <td><?php echo $row['Fname']; ?></td>
                        <td><?php echo $row['Lname']; ?></td>
                        <td><?php echo $row['StartDate']; ?></td>
                        <td><?php echo $row['DOB']; ?></td>
                        <td><?php echo $row['PhoneNumber']; ?></td>
                        <td><?php echo $row['Address']; ?></td>
                        <td><?php echo $row['City']; ?></td>
                        <td><?php echo $row['Province']; ?></td>
                        <td><?php echo $row['PostalCode']; ?></td>
                        <td><?php echo $row['Citizenship']; ?></td>
                        <td><?php echo $row['Email']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No results found.</p>
    <?php endif; ?>
    
</body>
</html>
