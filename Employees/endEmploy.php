<?php
// Connect to the database and fetch all facilities
require_once '../database.php';

// Get the medical card number from the url
$medical_card_number = isset($_GET['medical_card_number']) ? $_GET['medical_card_number'] : '';

// Set up the initial query
$query = 'SELECT f.FID, f.Name, f.Address, l.City, l.Province, f.Type, m.MedCardNumber AS ManagerID
        FROM FACILITIES f
        JOIN LOCATION l ON f.PostalCode = l.PostalCode
        LEFT JOIN MANAGES m ON f.FID = m.FID
        LEFT OUTER JOIN EMPLOYS z ON f.FID = z.FacilityID 
        WHERE z.MedCardNumber = :MedCardNumber AND z.EndDate IS NULL
        GROUP BY f.FID
        ORDER BY l.Province ASC, l.City ASC, f.Type ASC;';

$result = $conn->prepare($query);
$result->bindParam("MedCardNumber", $_GET['medical_card_number']);
$result->execute();

if(isset($_POST['endEmploy']) && isset($_POST['ManagerID']))
{
    //If employee is the Manager of the facility, Delete this tuple
    if($_POST['ManagerID'] == $_GET['medical_card_number']){
        $stmt = $conn->prepare("DELETE FROM MANAGES WHERE FID = :FID ");
        $stmt->bindParam(':FID', $_POST["endEmploy"]);
        $stmt->execute();
    }

    $stmt = $conn->prepare("UPDATE EMPLOYS SET EndDate = (SELECT CAST( curdate() AS Date )) WHERE FacilityID = :FID AND MedCardNumber = :MedCardNumber");
    $stmt->bindParam(':MedCardNumber', $_GET['medical_card_number']);
    $stmt->bindParam(':FID', $_POST['endEmploy']);
    $stmt->execute();

    header("Location: ./");
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
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
</head>
<body>
<nav>
    <ul>
        <li><a href="../">Home</a></li>
        <li><a href="../Facilities/">Search Facility</a></li>
        <li><a href="../Emails/">Search Email</a></li>
    </ul>
</nav>

<h1>Select an Employment to Terminate</h1>

<?php if ($result): ?>
<table>
<thead>
    <tr>    
        <th>Name</th>
        <th>Address</th>
        <th>City</th>
        <th>Province</th>
        <th>Actions</th>
    </tr>
</thead>

<tbody>
    <?php foreach ($result as $row): ?>
        <tr>
            <td><?php echo $row['Name']; ?></td>
            <td><?php echo $row['Address']; ?></td>
            <td><?php echo $row['City']; ?></td>
            <td><?php echo $row['Province']; ?></td>
            <td><form action="./endEmploy.php?medical_card_number=<?php echo $medical_card_number; ?>" method="post">
            <input type="hidden" name="endEmploy" id="endEmploy" value="<?php echo $row['FID']; ?>">
            <input type="hidden" name="ManagerID" id="ManagerID" value="<?php echo $row['ManagerID']; ?>">
            <button type="submit" >Terminate</button>
            </form>
            </td>
            </tr>
    <?php endforeach; ?>

</tbody>

</table>
<?php else: ?>
    <p>No results found.</p>
<?php endif; ?>
 
</body>
</html>