<?php
// Connect to the database and fetch all facilities
require_once '../database.php';

// Set up the initial query
$query = 'SELECT e.Fname, e.Lname, e.Role, i.IDate, f.Name
FROM EMPLOYEE e
JOIN INFECTIONS i ON e.MedCardNumber = i.MedCardNumber
JOIN EMPLOYS z on e.MedCardNumber = z.MedCardNumber
JOIN FACILITIES f on f.FID = z.FacilityID
WHERE i.IDate BETWEEN DATE_SUB(NOW(), INTERVAL 2 WEEK) AND NOW()
GROUP BY i.IDate
ORDER BY f.Name ASC, e.Fname ASC;';

if (isset($_POST['Role'])) {
    $Role = $_POST['Role'];

    if ($Role !== 'See All') {
        $query =   'SELECT e.Fname, e.Lname, e.Role, i.IDate, f.Name
        FROM EMPLOYEE e
        JOIN INFECTIONS i ON e.MedCardNumber = i.MedCardNumber
        JOIN EMPLOYS z on e.MedCardNumber = z.MedCardNumber
        JOIN FACILITIES f on f.FID = z.FacilityID';

        $whereClause = ' WHERE i.IDate BETWEEN DATE_SUB(NOW(), INTERVAL 2 WEEK) AND NOW() AND';

        if ($Role !== 'See All') {
            $whereClause .= " Role = '$Role' AND";
        }    
    }

    // Remove the trailing "AND" from the where clause
    if (!empty($whereClause)) {
        $whereClause = rtrim($whereClause, 'AND');
        $query .= "$whereClause";
    }
}

// Add the ORDER BY clause to sort the results
$query .= ' GROUP BY i.IDate
ORDER BY f.Name ASC, e.Fname ASC;';

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
    <h1>COVID STATISTICS OF THE LAST TWO WEEKS</h1>

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
                <option value="Cashier" <?php echo ($selected_Role == 'Cashier') ? 'selected' : ''; ?>>Cashier</option>
                <option value="Pharmacist" <?php echo ($selected_Role == 'Pharmacist') ? 'selected' : ''; ?>>Pharmacist</option>
                <option value="Receptionist" <?php echo ($selected_Role == 'Receptionist') ? 'selected' : ''; ?>>Receptionist</option>
                <option value="Administrative Personnel" <?php echo ($selected_Role == 'Administrative Personnel') ? 'selected' : ''; ?>>Administrative Personnel</option>
                <option value="Security Personnel" <?php echo ($selected_Role == 'Security Personnel') ? 'selected' : ''; ?>>Security Personnel</option>
                <option value="Regular Employee" <?php echo ($selected_Role == 'Regular Employee') ? 'selected' : ''; ?>>Regular Employee</option>
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
                    <th>Role</th>
                    <th>Date of Infection</th>
                    <th>Currently Working At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result as $row): ?>
                    <tr>
                        <td><?php echo $row['Fname']; ?></td>
                        <td><?php echo $row['Lname']; ?></td>
                        <td><?php echo $row['Role']; ?></td>
                        <td><?php echo $row['IDate']; ?></td>
                        <td><?php echo $row['Name']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No results found.</p>
    <?php endif; ?>
</body>
</html>
