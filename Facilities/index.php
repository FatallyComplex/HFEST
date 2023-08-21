<?php
// Connect to the database and fetch all facilities
require_once '../database.php';

$searchTerm = '';
if(isset($_POST['search'])){
    $searchTerm = $_POST['search'];
}

if (isset($_POST['province'])) {
    $province = $_POST['province'];
}else{
    $province = 'See All';
}

if(isset($_POST['Type'])){
    $type = $_POST['Type'];
}else{
    $province = 'See All';
}

// Set up the initial query
$query = 'SELECT f.FID, f.Name, f.Address, l.City, l.Province, f.PostalCode, f.PhoneNumber, f.WebAddress, f.Type, f.Capacity, m.MedCardNumber, e.Fname AS ManagerFirstName, e.Lname AS ManagerLastName, COUNT(z.MedCardNumber) AS NumEmployees 
FROM FACILITIES f
JOIN LOCATION l ON f.PostalCode = l.PostalCode
LEFT JOIN MANAGES m ON f.FID = m.FID
LEFT JOIN EMPLOYEE e ON m.MedCardNumber = e.MedCardNumber 
LEFT JOIN EMPLOYS z ON f.FID = z.FacilityID AND z.EndDate IS NULL
WHERE (f.Name LIKE :searchTerm OR f.FID LIKE :searchTerm)';

if (isset($province) && $province !== 'See All' || isset($Type) && $Type !== 'See All') {
    $query =   'SELECT f.FID, f.Name, f.Address, l.City, l.Province, f.PostalCode, f.PhoneNumber, f.WebAddress, f.Type, f.Capacity, m.MedCardNumber, e.Fname AS ManagerFirstName, e.Lname AS ManagerLastName, COUNT(z.MedCardNumber) AS NumEmployees 
    FROM FACILITIES f
    JOIN LOCATION l ON f.PostalCode = l.PostalCode
    LEFT JOIN MANAGES m ON f.FID = m.FID
    LEFT JOIN EMPLOYEE e ON m.MedCardNumber = e.MedCardNumber 
    LEFT JOIN EMPLOYS z ON f.FID = z.FacilityID AND z.EndDate IS NULL
    WHERE (f.Name LIKE :searchTerm OR f.FID LIKE :searchTerm)
    AND ';

    $whereClause = '';

    if (isset($province) && $province !== 'See All') {
        $whereClause .= " Province = '$province' AND";
    }

    if (isset($Type) && $Type !== 'See All') {
        $whereClause .= " Type = '$Type' AND";
    }

    // Remove the trailing "AND" from the where clause
    if (!empty($whereClause)) {
        $whereClause = rtrim($whereClause, 'AND');
        $query .= "$whereClause";
    }
}

// Add the ORDER BY clause to sort the results
$query .= ' GROUP BY f.FID
ORDER BY l.Province ASC, l.City ASC, f.Type ASC, NumEmployees ASC';

// Execute the query
$result = $conn->prepare($query);
$result->bindValue(':searchTerm', '%' . $searchTerm . '%');
$result->execute();


$query = "SELECT DISTINCT Province FROM LOCATION";
$stmt = $conn->prepare($query);
$stmt->execute();
$provinces = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html>
<head>
    <title>HFESTS - Search Facility</title>
    <link href="https://fonts.googleapis.com/css?family=Montserrat|Open+Sans&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #f7f7f7;
        }
        h1 {
            color: #333333;
            text-align: center;
            margin-top: 25px;
        }
        h2 {
            color: #444444;
            text-align: center;
            margin-top: 10px;
        }
        form {
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
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
        td:nth-of-type(4) {
        max-width: 90px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        }
        td:nth-of-type(8) {
        max-width: 90px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        }
        td:nth-of-type(9) {
        max-width: 100px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: normal;
        }
        .add-facility-button {
            float: left;
            margin-left: 40px;
            margin-top: 20px;
            margin-bottom: 20px;
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
        </ul>
    </nav>
    <h1>Search Facility</h1>
    <h2><a href="../Facilities/covid.php">See Covid Statistics Of The Last Two Weeks</a></h2>

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
            <!-- Type drop down menu              -->
            <!-- -------------------------------- -->
            <label for="Type">Type:</label>
            <?php
            $selected_Type = '';

            if (isset($_POST['Type'])) {
                $selected_Type = $_POST['Type'];
            }
            ?>
            <select id="Type" name="Type">
                <option value="See All" <?php echo ($selected_Type == 'See All') ? 'selected' : ''; ?>>See All</option>
                <option value="Vaccination Center" <?php echo ($selected_Type == 'Vaccination Center') ? 'selected' : ''; ?>>Vaccination Center</option>
                <option value="Clinic" <?php echo ($selected_Type == 'Clinic') ? 'selected' : ''; ?>>Clinic</option>
                <option value="Hospital" <?php echo ($selected_Type == 'Hospital') ? 'selected' : ''; ?>>Hospital</option>
                <option value="Pharmacy" <?php echo ($selected_Type == 'Pharmacy') ? 'selected' : ''; ?>>Pharmacy</option>
                <option value="CLSC" <?php echo ($selected_Type == 'CLSC') ? 'selected' : ''; ?>>CLSC</option>
            </select>

            <label for="search" style="margin-left:15px;">Search:</label>
            <input type="text" name="search" id="search" value="<?php echo $searchTerm; ?>" style="margin-left:15px;" placeholder="Search...">
            <input type="hidden" name="searchTerm" id="searchTerm" value="<?php echo $searchTerm; ?>">


            <button type="submit">Search</button>
        </form>
        
        <div> 
            <button class="add-facility-button" onclick="location.href='./add_facility.php';">Add New Facility</button>
        </div>

<?php if ($result): ?>

        <table>
            <thead style="position: sticky; top: 0;">
                <tr>
                    <th>Facility ID</th>    
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
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($result as $row): ?>
                    <tr>
                        <td><a href="./details.php?FID=<?php echo urlencode($row['FID']); ?>"><?php echo $row['FID']; ?></a></td>
                        <td><?php echo $row['Name']; ?></td>
                        <td><?php echo $row['Address']; ?></td>
                        <td><?php echo $row['City']; ?></td>
                        <td><?php echo $row['Province']; ?></td>
                        <td><?php echo $row['PostalCode']; ?></td>
                        <td><?php echo $row['PhoneNumber']; ?></td>
                        <td><span title="<?php echo $row['WebAddress']; ?>"><?php echo $row['WebAddress']; ?></span></td>
                        <td><?php echo $row['Type']; ?></td>
                        <td><?php echo $row['Capacity']; ?></td>
                        <td><?php echo $row['ManagerFirstName'] . ' ' . $row['ManagerLastName']; ?></td>
                        <td><?php echo $row['NumEmployees']; ?></td>
                        <!-- Add Edit and Delete buttons -->
                        <td>
                        <a href="./edit.php?FID=<?php echo urlencode($row['FID']); ?>" class="button">Edit</a>
                        <a href="#" class="button" onclick="if (confirm('Are you sure you want to delete this tuple?')) { document.getElementById('delete-form-<?php echo $row['FID']; ?>').submit(); }">Delete</a>
                         <form id="delete-form-<?php echo $row['FID']; ?>" method="post" action="delete.php">
                          <input type="hidden" name="fid" value="<?php echo $row['FID']; ?>">
                          <input type="hidden" name="delete" value="true">
                         </form>
                        </td>
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
