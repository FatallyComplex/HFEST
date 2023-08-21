<?php
// Connect to the database and fetch all facilities
require_once '../database.php';

// Set up the initial query
$query = 'SELECT f.FID, f.Name, l.Province, f.PostalCode, f.Capacity, 
(SELECT COUNT(*) 
FROM INFECTIONS
JOIN EMPLOYEE ON INFECTIONS.MedCardNumber = EMPLOYEE.MedCardNumber
JOIN EMPLOYS ON EMPLOYEE.MedCardNumber = EMPLOYS.MedCardNumber
WHERE EMPLOYS.FacilityID = f.FID
AND INFECTIONS.IDate BETWEEN DATE_SUB(NOW(), INTERVAL 2 WEEK) AND NOW() 
AND INFECTIONS.INature = "COVID-19"
AND EMPLOYS.EndDate IS NULL
)AS NumEmployees
FROM FACILITIES f
JOIN LOCATION l ON f.PostalCode = l.PostalCode
GROUP BY f.FID
ORDER BY l.Province ASC, NumEmployees ASC;';

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
            <li><a href="../Emails/">Search Email</a></li>
            <li><a href="./">Back</a></li>
        </ul>
    </nav>
    <h1>COVID STATISTICS OF THE LAST TWO WEEKS</h1>
    
<?php if ($result): ?>
        <table>
            <thead>
                <tr>
                    <th>Province</th>    
                    <th>Name</th>
                    <th>Capacity</th>
                    <th>Number of infections in the last two weeks</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result as $row): ?>
                    <tr>
                        <td><?php echo $row['Province']; ?></td>
                        <td><?php echo $row['Name']; ?></td>
                        <td><?php echo $row['Capacity']; ?></td>
                        <td><?php echo $row['NumEmployees']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No results found.</p>
    <?php endif; ?>
</body>
</html>
