<?php
require_once '../database.php';
date_default_timezone_set('America/New_York');

$medical_card_number = $_POST['medical_card_number'];
$date = $_POST['IDate'];
$nature = $_POST['INature'];

$today = date('y-m-d');

// Fetch the infection details from the database
$query = "SELECT * FROM INFECTIONS WHERE MedCardNumber = ? AND IDate = ? AND INature = ?";
$stmt = $conn->prepare($query);
$stmt->execute([$medical_card_number, $date, $nature]);
$infection = $stmt->fetch(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>HFESTS - Update <?echo $infection?></title>
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
            margin: 5px;
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
        <li><a href="../Employees">Search Employees</a></li>
        <li><a href="../Facilities">Search Facilities</a></li>
        <li><a href="./details.php?medical_card_number=<?php echo $medical_card_number;?>">Back</a></li>
    </ul>
</nav>
    <h1>Update Infection Details</h1>
    <form action="update-infection-action.php" method="POST">
        <input type="hidden" name="medical_card_number" value="<?php echo $medical_card_number; ?>">
        <input type="hidden" name="old_date" value="<?php echo $date; ?>">
        <input type="hidden" name="old_nature" value="<?php echo $nature; ?>">
        <label>New Date:</label>
        <input type="date" name="date" value="<?php echo $infection['IDate']; ?>" max="<?php echo date('Y-m-d'); ?>"required>
        <label>New Nature:</label>
        <select name="nature" required>
            <?php
            // Fetch all infection types from the database
            $query = "SELECT DISTINCT INature FROM INFECTIONS WHERE INature NOT LIKE '%COVID-19%'";
            $stmt = $conn->query($query);
            $infectionTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Output an option for each infection type
            foreach ($infectionTypes as $infectionType) {
                $selected = ($infection['INature'] === $infectionType['INature']) ? 'selected' : '';
                echo "<option value=\"{$infectionType['INature']}\" $selected>{$infectionType['INature']}</option>";
            }
            ?>
        </select>
        <button type="submit">Update</button>
    </form>

    <script>
        // Show/hide input fields based on selected option
        var natureInput = document.querySelector('select[name="nature"]');
        var variantInput = document.querySelector('#variant-input');
        var otherNatureInput = document.querySelector('#other-nature-input');

        natureInput.addEventListener('change', function() {
            if (this.value === 'variant') {
                variantInput.style.display = 'block';
                otherNatureInput.style.display = 'none';
            } else if (this.value === 'other){
                variantInput.style.display = 'none';
                otherNatureInput.style.display = 'block';
            } else {
                variantInput.style.display = 'none';
                otherNatureInput.style.display = 'none';
            }
        });
</script>

</body>
</html>