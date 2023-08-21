<?php
require_once '../database.php';

// Get the medical card number and date range from the URL
$medical_card_number = isset($_GET['medical_card_number']) ? $_GET['medical_card_number'] : '';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

$time = strtotime($start_date);
$start_month = intval(date("m", $time)); // Get the month number as an integer
$start_year = intval(date("Y", $time));  // Get the year number as an integer

// Fetch shifts for the user within the selected date range
$query = "SELECT S.SDate, S.FID, F.Name, S.StartTime, S.EndTime, F.Address FROM SCHEDULES AS S INNER JOIN FACILITIES AS F ON S.FID = F.FID WHERE S.MedCardNumber = ? AND S.SDate >= ? AND S.SDate <= ? ORDER BY F.Name, S.SDate, S.StartTime";
$stmt = $conn->prepare($query);
$stmt->execute([$medical_card_number, $start_date, $end_date]);
$shifts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organize shifts by facility
$shifts_by_facility = [];
foreach ($shifts as $shift) {
    $facility_id = $shift['FID'];
    if (!isset($shifts_by_facility[$facility_id])) {
        $shifts_by_facility[$facility_id] = ['name' => $shift['Name'], 'shifts' => []];
    }
    $shifts_by_facility[$facility_id]['shifts'][] = $shift;
}

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
        .table-wrapper-scroll-y {
            width: 100%;
            overflow-y: auto;
            max-height: 500px;
            display: inline-block;
            position: relative;
            }

        .table-wrapper-scroll-y > div {
        flex: 1;
        overflow-x: auto;
        }

        .table-wrapper-scroll-y {
        display: inline-block;
        max-height: 300px; /* set a fixed height for the table body */
        overflow-y: auto; /* enable vertical scroll bar */
        overflow-x: hidden; /* hide horizontal scroll bar */
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
        <li><a href="../Schedule/?medical_card_number=<?php echo $medical_card_number?>">Back</a></li>
    </ul>
</nav>

<?php foreach($shifts_by_facility as $facility_id => $facility_data):?>
    <h2><?php echo $facility_data['name']?></h2>
    <div class="table-wrapper-scroll-y my-custom-scrollbar">
        <table>
            <thead style="position: sticky; top: 0;">
                <tr>
                    <th>Date</th>
                    <th>Address</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                </tr>
            </thead>
            <tbody >
            <?php foreach($facility_data['shifts'] as $shift):?>
                <tr>
                    <td><?php echo $shift['SDate']; ?></td>
                    <td><?php echo $shift['Address']; ?></td>
                    <td><?php echo $shift['StartTime']; ?></td>
                    <td><?php echo $shift['EndTime']; ?></td>
                </tr>
            <?php endforeach;?>
            </tbody>
        </table>
    </div>
<?php endforeach;?>


</body>
</html>