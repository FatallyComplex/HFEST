<?php
// Connect to the database and fetch all facilities
require_once '../database.php';

$searchTerm = '';
if(isset($_POST['search'])){
    $searchTerm = $_POST['search'];
}
// First Query
$query =   'SELECT e.MedCardNumber, e.Fname, e.Lname, l.Province, e.Role, l.City, COUNT(z.MedCardNumber) AS Count
FROM EMPLOYEE e
JOIN LOCATION l ON e.PostalCode = l.PostalCode
LEFT JOIN EMPLOYS z ON e.MedCardNumber = z.MedCardNumber AND z.EndDate IS NULL
WHERE (CONCAT(e.Fname, " ", e.Lname) LIKE :searchTerm OR e.MedCardnumber LIKE :searchTerm)
';

if (isset($_POST['province']) || isset($_POST['Role']) || isset($_POST['endDate'])) {
    $province = $_POST['province'];
    $Role = $_POST['Role'];
    $endDate = $_POST['endDate'];

    if ($province !== 'See All' || $Role !== 'See All' || $endDate !== 'See All') {
        $query =   'SELECT e.MedCardNumber, e.Fname, e.Lname, l.Province, e.Role, l.City, COUNT(z.MedCardNumber) AS Count
        FROM EMPLOYEE e
        JOIN LOCATION l ON e.PostalCode = l.PostalCode
        LEFT JOIN EMPLOYS z ON e.MedCardNumber = z.MedCardNumber AND z.EndDate IS NULL
        WHERE (CONCAT(e.Fname, " ", e.Lname) LIKE :searchTerm OR e.MedCardnumber LIKE :searchTerm)
        AND ';

        $whereClause = '';

        if ($province !== 'See All') {
            $whereClause .= " Province = '$province' AND";
        }

        if ($Role !== 'See All') {
            $whereClause .= " Role = '$Role' AND";
        }    
        
        if ($endDate !== 'See All') {
            if ($endDate === 'Yes') {
                $whereClause .= " (SELECT COUNT(*) FROM EMPLOYS WHERE MedCardNumber = e.MedCardNumber AND EndDate IS NULL) > 0 AND";
            }
        
            if ($endDate === 'No') {
                $whereClause .= " (SELECT COUNT(*) FROM EMPLOYS WHERE MedCardNumber = e.MedCardNumber AND EndDate IS NULL) = 0 AND";
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
            ORDER BY l.City ASC, (SELECT COUNT(*) FROM EMPLOYS WHERE MedCardNumber = e.MedCardNumber AND EndDate IS NULL) DESC';
$result = $conn->prepare($query);
$result->bindValue(':searchTerm', '%' . $searchTerm . '%');
$result->execute();

/////////////////////////////////////////////////////////
// Second Query
/////////////////////////////////////////////////////////
$query2 = 'SELECT e.MedCardNumber, e.Fname, e.Lname, round(SUM(TIME_TO_SEC(TIMEDIFF(s.EndTime, s.StartTime))) / 3600,0) AS TotalHours, MIN(s.SDate) AS FirstDayOfWork, e.DOB, e.Email
FROM EMPLOYEE e
JOIN SCHEDULES s ON s.MedCardNumber = e.MedCardNumber
WHERE e.Role = "Nurse"
GROUP BY e.MedCardNumber
HAVING TotalHours = (
  SELECT MAX(TotalHours)
  FROM (
    SELECT round(SUM(TIME_TO_SEC(TIMEDIFF(s.EndTime, s.StartTime))) / 3600,0) AS TotalHours
    FROM EMPLOYEE e
    JOIN SCHEDULES s ON s.MedCardNumber = e.MedCardNumber
    WHERE e.Role = "Nurse"
    GROUP BY e.MedCardNumber
  ) t
)';

$stmt2 = $conn->prepare($query2);
$stmt2->execute();
$BestNurse = $stmt2->fetchAll(PDO::FETCH_ASSOC);

$query = "SELECT DISTINCT Province FROM LOCATION";
$stmt = $conn->prepare($query);
$stmt->execute();
$provinces = $stmt->fetchAll(PDO::FETCH_ASSOC);



?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>HFESTS - Employees</title>
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
            margin-top: 25px;
        }
        h2 {
            color: #444444;
            text-align: center;
            margin-top: 20px;
            font-size: 20px;
        }
        h3 {
            color: #444444;
            text-align: center;
            margin-top: 50px;
            font-size: 20px;
        }
        h4 {
            color: #444444;
            text-align: left;
            margin-top: 20px;
            font-size: 20px;
        }
        caption{
            font-size: 24px;
        }
        form {
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            margin-top: 25px;
            margin-left: 15px;
            max-width: 1250px;
            margin: 0 auto;
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
        div {
        display: flex;
        justify-content: center;
        align-items: center;
        }
        div2 {
            display:block;
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
        .table-container {
        margin-top: 20px;
        border: 1px solid black;
        padding: 10px;
        margin-left: 175px;
        margin-right: 175px;
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
        </ul>
    </nav>

    <div class="table-container">
        <h4>Nurse(s) With Highest Number Of Hours Scheduled Award</h4>
        <table>
            <thead>
                <tr>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>First Day Of Work</th>
                    <th>Date Of Birth</th>
                    <th>Email Address</th>
                    <th>Total Number Of Hours Scheduled</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($BestNurse as $row): ?>
                    <tr>
                        <td><?php echo $row['Fname']; ?></td>
                        <td><?php echo $row['Lname']; ?></td>
                        <td><?php echo $row['FirstDayOfWork']; ?></td>
                        <td><?php echo $row['DOB']; ?></td>
                        <td><?php echo $row['Email']; ?></td>
                        <td><?php echo round($row['TotalHours'], 0); ?> hours</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <h3>
    <a href="../Employees/covid.php">See Covid Statistics Of The Last Two Weeks</a>
    <a href="../Employees/medpers.php" style="margin-left: 20px;">See Medical Personnel Covid Statistics</a>
    </h3>
    
    <h1>Search Employee</h1>
        
        <form method="POST" action="">
            <!-- -------------------------------- -->
            <!-- Province drop down menu          -->
            <!-- -------------------------------- -->    
            <label for="province">Province:</label>
            <?php
            $selected_province = '';

            if (isset($_POST['province'])) {
                $selected_province = $_POST['province'];
            }
            ?>
            <select id="province" name="province">
            <option value="See All" <?php echo ($selected_province == 'See All') ? 'selected' : '' ?>>See All</option>
            <?php foreach($provinces as $pro):?>
                <option value="<?php echo $pro['Province']?>" <?php echo ($selected_province == $pro['Province']) ? 'selected' : '' ?>><?php echo $pro['Province']?></option>
            <?php endforeach?>
            </select>

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
                <option value="Cashier" <?php echo ($selected_Role == 'Cashier') ? 'selected' : ''; ?>>Cashier</option>
                <option value="Pharmacist" <?php echo ($selected_Role == 'Pharmacist') ? 'selected' : ''; ?>>Pharmacist</option>
                <option value="Receptionist" <?php echo ($selected_Role == 'Receptionist') ? 'selected' : ''; ?>>Receptionist</option>
                <option value="Administrative Personnel" <?php echo ($selected_Role == 'Administrative Personnel') ? 'selected' : ''; ?>>Administrative Personnel</option>
                <option value="Security Personnel" <?php echo ($selected_Role == 'Security Personnel') ? 'selected' : ''; ?>>Security Personnel</option>
                <option value="Regular Employee" <?php echo ($selected_Role == 'Regular Employee') ? 'selected' : ''; ?>>Regular Employee</option>
            </select>

            <!-- -------------------------------- -->
            <!-- Currently working drop down menu -->
            <!-- -------------------------------- -->
            <label for="endDate">Currently Working:</label>
            <?php
                $selected_endDate = '';
                if (isset($_POST['endDate'])) {
                    $selected_endDate = $_POST['endDate'];
                }
            ?>
            <select id="endDate" name="endDate">
                <option value="See All" <?php echo ($selected_endDate == 'See All') ? 'selected' : ''; ?>>See All</option>
                <option value="Yes" <?php echo ($selected_endDate == 'Yes') ? 'selected' : ''; ?>>Yes</option>
                <option value="No" <?php echo ($selected_endDate == 'No') ? 'selected' : ''; ?>>No</option>
            </select>
            
            <label for="search" style="margin-left:15px;">Search:</label>
            <input type="text" name="search" id="search" value="<?php echo $searchTerm; ?>" style="margin-left:15px;" placeholder="Search...">
            <input type="hidden" name="searchTerm" id="searchTerm" value="<?php echo $searchTerm; ?>">

            <button type="submit">Search</button>
        </form>
        
        <form method="POST" action="">
        
        </form>
        
        <div>    
        <button onclick="window.location.href='./create.php'">Add New Employee</button>
        </div>
    
        <?php if ($result): ?>
        <table>
            <thead style="position: sticky; top: 0;">
                <tr>
                    <th>Medical Card Number</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Province</th>
                    <th>City</th>
                    <th>Role</th>
                    <th># Facilities Currently Working For</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result as $row): ?>
                    <tr>
                        <td>
                            <a href="./details.php?medical_card_number=<?php echo urlencode($row['MedCardNumber']); ?>">
                                <?php echo $row['MedCardNumber']; ?>
                            </a>
                        </td>
                        <td><?php echo $row['Fname']; ?></td>
                        <td><?php echo $row['Lname']; ?></td>
                        <td><?php echo $row['Province']; ?></td>
                        <td><?php echo $row['City']; ?></td>
                        <td><?php echo $row['Role']; ?></td>
                        <td><?php echo $row['Count']; ?></td>
                        <td><button onclick="window.location.href='./edit.php?medical_card_number=<?php echo urlencode($row['MedCardNumber']); ?>'">Update</button><br>
                        <button onclick="if (confirm('Are you sure you want to delete this Employee\'s Information?')) { document.getElementById('delete-form-<?php echo $row['MedCardNumber']; ?>').submit(); }">Delete</button>
                        <form id="delete-form-<?php echo $row['MedCardNumber']; ?>" method="post" action="delete.php">
                                <input type="hidden" name="MedCardNumber" value="<?php echo $row['MedCardNumber']; ?>">
                                <input type="hidden" name="delete" value="true">
                            </form></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No results found.</p>
    <?php endif; ?>
    <script>
  let timeoutId;
  const delay = 750;

  document.getElementById("search").addEventListener("input", function() {
    clearTimeout(timeoutId);
    timeoutId = setTimeout(() => {
      document.querySelector("form").submit();
    }, delay);
  });
</script>
</body>
</html>