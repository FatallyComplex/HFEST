<?php
// Connect to the database and fetch all facilities
require_once '../database.php';

// Get the medical card number from the url
$medical_card_number = isset($_GET['medical_card_number']) ? $_GET['medical_card_number'] : '';

$query = "SELECT MedCardNumber, Role, Fname, Lname, DOB, PhoneNumber, Address, City, Province, e.PostalCode, Citizenship, Email FROM EMPLOYEE e, LOCATION l WHERE MedCardNumber = ? AND e.PostalCode = l.PostalCode";
$stmt = $conn -> prepare($query);
$stmt -> execute([$medical_card_number]);
$employeeInfo = $stmt->fetchAll(PDO::FETCH_ASSOC);

$query = "SELECT * FROM INFECTIONS WHERE MedCardNumber = ?";
$stmt = $conn -> prepare($query);
$stmt -> execute([$medical_card_number]);
$infections = $stmt->fetchAll(PDO::FETCH_ASSOC);

$query = "SELECT DoseNumber, VType, VDate, Name, FacilityId FROM VACCINES JOIN FACILITIES ON VACCINES.FacilityId = FACILITIES.FID WHERE MedCardNumber = ? ORDER BY DoseNumber";
$stmt = $conn -> prepare($query);
$stmt -> execute([$medical_card_number]);
$vaccines = $stmt->fetchAll(PDO::FETCH_ASSOC);

$query = "SELECT * FROM EMPLOYS JOIN FACILITIES ON EMPLOYS.FacilityId = FACILITIES.FID WHERE MedCardNumber = ?";
$stmt = $conn -> prepare($query);
$stmt -> execute([$medical_card_number]);
$Facilities = $stmt->fetchAll(PDO::FETCH_ASSOC);


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
        .centered-link{
            padding-top: 20px;
            text-align: center;
        }
        .last-table{
            margin-bottom: 50px;
        }

        .table-wrapper-scroll-y > div {
        display: flex; justify-content: center;
        flex: 1;
        overflow-x: auto;
        }

        .table-wrapper-scroll-y {
        max-height: 300px; /* set a fixed height for the table body */
        overflow-y: auto; /* enable vertical scroll bar */
        overflow-x: hidden; /* hide horizontal scroll bar */
        margin: 0 auto;
        }

        .table-wrapper-scroll-y::-webkit-scrollbar {
        width: 8px;
        }

        .table-wrapper-scroll-y::-webkit-scrollbar-track {
        background-color: #f1f1f1;
        }

        .table-wrapper-scroll-y::-webkit-scrollbar-thumb {
        background-color: #912338;
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
        <li><a href="../Employees">Search Employees</a></li>
        <li><a href="../Facilities">Search Facilities</a></li>
        <li><a href="./?medical_card_number=<?php echo $medical_card_number;?>">Back</a></li>
    </ul>
</nav>
    <h1>Employee Information</h1>
    <h2><?php echo $medical_card_number; ?></h2>
    <?php if ($employeeInfo): ?>
        <table>
            <thead>
                <tr>
                    <th>Medical Card Number</th>
                    <th>Role</th>
                    <th>Name</th>
                    <th>Date of Birth</th>
                    <th>Telephone Number</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($employeeInfo as $einfo): ?>
                    <tr>
                        <td><?php echo $einfo['MedCardNumber']; ?></td>
                        <td><?php echo $einfo['Role']; ?></td>
                        <td><?php echo $einfo['Fname'] . ' ' . $einfo['Lname']; ?></td>
                        <td><?php echo $einfo['DOB']; ?></td>
                        <td><?php echo $einfo['PhoneNumber']; ?></td>
                    </tr>
                    <?php endforeach; ?>
            </tbody>
        </table>
        <table>
            <thead>
                <tr>
                    <th>Address</th>
                    <th>City</th>
                    <th>Province</th>
                    <th>Postal Code</th>
                    <th>Citizenship</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($employeeInfo as $einfo): ?>
                    <tr>
                        <td><?php echo $einfo['Address']; ?></td>
                        <td><?php echo $einfo['City']; ?></td>
                        <td><?php echo $einfo['Province']; ?></td>
                        <td><?php echo $einfo['PostalCode']; ?></td>
                        <td><?php echo $einfo['Citizenship']; ?></td>
                        <td><?php echo $einfo['Email']; ?></td>
                    </tr>
                    <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No employee found for this medical card number.</p>
    <?php endif; ?>

    <div class="centered-link" style="margin-bottom:50px;">
    <button onclick="window.location.href='../Schedule?medical_card_number=<?php echo urlencode($medical_card_number); ?>'">View Schedule</button>
    </div>
        <h2>Employment Facilities</h2>
    <?php if ($Facilities): ?>
        <table class="table-wrapper-scroll-y my-custom-scrollbar" style="margin-bottom:50px;">
            <thead style="position: sticky; top: 0;">
                <tr>
                    <th>Name of facility</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($Facilities as $facility): ?>
                    <tr>
                        <td>
                            <?php echo $facility['Name']; ?>
                        </td>
                        <td>
                            <?php if ($facility['EndDate'] == null) {
                                echo "<span style='color:green;'>ACTIVE</span>";
                            } else {
                                echo "<span style='color:red;'>TERMINATED ON " . $facility['EndDate'] . "</span>";
                            } ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No facilities found for this medical card number.</p>
    <?php endif; ?>
    <h2>Infections</h2>
<?php if ($infections): ?>
        <table class="table-wrapper-scroll-y my-custom-scrollbar">
            <thead style="position: sticky; top: 0;">
                <tr>
                    <th>Date of infection</th>
                    <th>Nature of infection</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($infections as $iNature): ?>
                    <tr>
                        <td>
                            <?php echo $iNature['IDate']; ?>
                        </td>
                        <td>
                            <?php echo $iNature['INature']; ?>
                        </td>
                        <td>
                            <form action="update-infection.php" method="POST">
                                <input type="hidden" name="medical_card_number" value="<?php echo $medical_card_number; ?>">
                                <input type="hidden" name="IDate" value="<?php echo $iNature['IDate']; ?>">
                                <input type="hidden" name="INature" value="<?php echo $iNature['INature']; ?>">
                                <button type="submit">
                                    Update
                                </button>
                            </form>
                            <form action="delete-infection.php" method="POST">
                                <input type="hidden" name="medical_card_number" value="<?php echo $medical_card_number; ?>">
                                <input type="hidden" name="IDate" value="<?php echo $iNature['IDate']; ?>">
                                <input type="hidden" name="INature" value="<?php echo $iNature['INature']; ?>">
                                <button type="submit"
                                    onclick="return confirm('Are you sure you want to delete this infection?')">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No infections found for this medical card number.</p>
    <?php endif; ?>
    <h2>Add infection</h2>
    
<div style="text-align: center; margin-bottom: 50px;">
        <button onclick="location.href='create-infection.php?medical_card_number=<?php echo $medical_card_number ?>'">Create
            New Infection</button>
    </div>

    <h2>Vaccines</h2>
<?php if ($vaccines): ?>
        <table class="table-wrapper-scroll-y my-custom-scrollbar">
            <thead style="position: sticky; top: 0;">
                <tr>
                    <th>Dose number</th>
                    <th>Facility name</th>
                    <th>Vaccine Type</th>
                    <th>Date of vaccine</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($vaccines as $iVax): ?>
                    <tr>
                        <td>
                            <?php echo $iVax['DoseNumber']; ?>
                        </td>
                        <td>
                            <?php echo $iVax['Name']; ?>
                        </td>
                        <td>
                            <?php echo $iVax['VType']; ?>
                        </td>
                        <td>
                            <?php echo $iVax['VDate']; ?>
                        </td>
                        <td>
                            <form action="update-vaccine.php" method="POST">
                                <input type="hidden" name="medical_card_number" value="<?php echo $medical_card_number; ?>">
                                <input type="hidden" name="VType" value="<?php echo $iVax['VType']; ?>">
                                <input type="hidden" name="VDate" value="<?php echo $iVax['VDate']; ?>">
                                <input type="hidden" name="FacilityID" value="<?php echo $iVax['FacilityId'];?>">
                                <input type="hidden" name="FacilityName" value="<?php echo $iVax['Name']; ?>">
                                <input type="hidden" name="DoseNumber" value="<?php echo $iVax['DoseNumber']; ?>">
                                <button type="submit">
                                    Update
                                </button>
                            </form>
                            <form action="delete-vaccine.php" method="POST">
                                <input type="hidden" name="medical_card_number" value="<?php echo $medical_card_number; ?>">
                                <input type="hidden" name="DoseNumber" value="<?php echo $iVax['DoseNumber']; ?>">
                                <input type="hidden" name="FacilityId" value="<?php echo $iVax['FacilityId']; ?>">
                                <button type="submit" onclick="return confirm('Are you sure you want to delete this vaccine?')">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No vaccinations found for this medical card number.</p>
    <?php endif; ?>
    <h2>Add Vaccine</h2>
    
<div style="text-align: center; margin-bottom: 40px;">
        <button onclick="location.href='create-vaccine.php?medical_card_number=<?php echo $medical_card_number ?>'">Create New
            Vaccine</button>
</div>

</body>
</html>
