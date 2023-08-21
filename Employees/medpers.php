<?php
// Connect to the database and fetch all facilities
require_once '../database.php';

// Set up the initial query
$query = 'SELECT e.MedCardNumber, e.Fname, e.Lname, e.Role, e.DOB, e.Email, MIN(s.SDate) AS FirstDay, 
SUM(TIME_TO_SEC(TIMEDIFF(s.EndTime, s.StartTime))) / 3600 AS TotalHours, 
(SELECT COUNT(*) FROM INFECTIONS WHERE MedCardNumber = e.MedCardNumber AND INature = "COVID-19") AS CovidCount
FROM EMPLOYEE e
JOIN SCHEDULES s ON e.MedCardNumber = s.MedCardNumber
JOIN INFECTIONS i ON e.MedCardNumber = i.MedCardNumber
JOIN EMPLOYS z ON e.MedCardNumber = z.MedCardnumber
WHERE (e.Role = "Doctor" OR e.Role = "Nurse") AND z.EndDate IS NULL
GROUP BY e.MedCardNumber
ORDER BY e.Role ASC, e.Fname ASC, e.Lname ASC;';

if (isset($_POST['Role']) || isset($_POST['NumInfect'])) {
    $Role = $_POST['Role'];
    $NumInfect = $_POST['NumInfect'];

    if ($Role !== 'See All' || $NumInfect !== 'See All') {
        $query =   'SELECT e.MedCardNumber, e.Fname, e.Lname, e.Role, e.DOB, e.Email, MIN(s.SDate) AS FirstDay, 
        SUM(TIME_TO_SEC(TIMEDIFF(s.EndTime, s.StartTime))) / 3600 AS TotalHours, 
        (SELECT COUNT(*) FROM INFECTIONS WHERE MedCardNumber = e.MedCardNumber AND INature = "COVID-19") AS CovidCount
        FROM EMPLOYEE e
        JOIN SCHEDULES s ON e.MedCardNumber = s.MedCardNumber
        JOIN INFECTIONS i ON e.MedCardNumber = i.MedCardNumber
        JOIN EMPLOYS z ON e.MedCardNumber = z.MedCardnumber';

        $whereClause = ' WHERE (e.Role = "Doctor" OR e.Role = "Nurse") AND z.EndDate IS NULL AND ';

        if ($Role !== 'See All') {
            $whereClause .= " Role = '$Role' AND";
        } 
        
        if ($NumInfect !== 'See All') {
            if ($NumInfect === '0') {
                $whereClause .= " (SELECT COUNT(*) FROM INFECTIONS WHERE MedCardNumber = e.MedCardNumber AND INature = 'COVID-19') = 0 AND";
            }
        
            if ($NumInfect === '1') {
                $whereClause .= " (SELECT COUNT(*) FROM INFECTIONS WHERE MedCardNumber = e.MedCardNumber AND INature = 'COVID-19') = 1 AND";
            }

            if ($NumInfect === '2') {
                $whereClause .= " (SELECT COUNT(*) FROM INFECTIONS WHERE MedCardNumber = e.MedCardNumber AND INature = 'COVID-19') = 2 AND";
            }

            if ($NumInfect === '3+') {
                $whereClause .= " (SELECT COUNT(*) FROM INFECTIONS WHERE MedCardNumber = e.MedCardNumber AND INature = 'COVID-19') > 2 AND";
            }
        }

    }

    // Remove the trailing "AND" from the where clause
    if (!empty($whereClause)) {
        $whereClause = rtrim($whereClause, 'AND');
        $query .= "$whereClause";
    }
}

// Add the ORDER BY clause to sort the results
$query .= ' GROUP BY e.MedCardNumber
ORDER BY e.Role ASC, e.Fname ASC, e.Lname ASC;';

// Execute the query
$result = $conn->prepare($query);
$result->execute();
?>


<!DOCTYPE html>
<html>
<head>
    <title>HFESTS - Covid Stats</title>
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
            margin-top: 50px;
        }
        form {
            display: flex;
            flex-direction: row;
            align-items: center;
            margin-top: 25px;
            max-width: 1000px;
            margin: 0 auto;
            justify-content: space-between;
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
            <li><a href="../Facilities/">Search Facility</a></li>
            <li><a href="../Emails/">Search Email</a></li>
            <li><a href="./">Back</a></li>
        </ul>
    </nav>
    <h1>MEDICAL PERSONNEL COVID INFORMATION</h1>

    <div>
    <form method="POST" action="">
    <!-- -------------------------------- -->
    <!-- Role drop down menu              -->
    <!-- -------------------------------- -->
    <label for="Role">Role:</label>
            <?php
            $selected_Role = '';

            if (isset($_POST['Role'])) {
                $selected_Role = $_POST['Role'];
            }
            ?>
            <select id="Role" name="Role">
                <option value="See All" <?php echo ($selected_Role == 'See All') ? 'selected' : ''; ?>>See All</option>
                <option value="Nurse" <?php echo ($selected_Role == 'Nurse') ? 'selected' : ''; ?>>Nurse</option>
                <option value="Doctor" <?php echo ($selected_Role == 'Doctor') ? 'selected' : ''; ?>>Doctor</option>
            </select>

    <!-- -------------------------------- -->
    <!-- NumInfect drop down menu         -->
    <!-- -------------------------------- -->

    <label for="NumInfect">Number of Infection(s):</label>
            <?php
                $selected_NumInfect = '';
                if (isset($_POST['NumInfect'])) {
                    $selected_NumInfect = $_POST['NumInfect'];
                }
            ?>
            <select id="NumInfect" name="NumInfect">
                <option value="See All" <?php echo ($selected_NumInfect == 'See All') ? 'selected' : ''; ?>>See All</option>
                <option value="0" <?php echo ($selected_NumInfect == '0') ? 'selected' : ''; ?>>0</option>
                <option value="1" <?php echo ($selected_NumInfect == '1') ? 'selected' : ''; ?>>1</option>
                <option value="2" <?php echo ($selected_NumInfect == '2') ? 'selected' : ''; ?>>2</option>
                <option value="3+" <?php echo ($selected_NumInfect == '3+') ? 'selected' : ''; ?>>3+</option>
            </select>
        <button type="submit">Search</button>
    </form>
    </div>

<?php if ($result): ?>
        <table>
            <thead>
                <tr>
                    <th>First Name</th>    
                    <th>Last Name</th>
                    <th>First Day Of Work</th>
                    <th>Role</th>
                    <th>Date of Birth</th>
                    <th>Email Address</th>
                    <th>Total Hours Scheduled</th>
                    <th># Infection(s)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result as $row): ?>
                    <tr>
                        <td><?php echo $row['Fname']; ?></td>
                        <td><?php echo $row['Lname']; ?></td>
                        <td><?php echo $row['FirstDay']; ?></td>
                        <td><?php echo $row['Role']; ?></td>
                        <td><?php echo $row['DOB']; ?></td>
                        <td><?php echo $row['Email']; ?></td>
                        <td><?php echo round($row['TotalHours'], 0); ?> hours</td>
                        <td><?php echo $row['CovidCount']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No results found.</p>
    <?php endif; ?>
</body>
</html>
