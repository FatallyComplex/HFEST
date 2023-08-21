

<?php

require_once '../database.php';
$name = $_POST['name'];
$address = $_POST['Address'];
$PostalCode = $_POST['PostalCode'];
$City = $_POST['City'];
$Province = $_POST['Province'];
$PhoneNumber = $_POST['PhoneNumber'];
$WebAddress = $_POST['WebAddress'];
$type = $_POST['type'];
$capacity = $_POST['capacity'];


$sql = "SELECT FACILITIES.*, LOCATION.city, LOCATION.province
        FROM FACILITIES
        INNER JOIN LOCATION ON FACILITIES.PostalCode = location.PostalCode
        WHERE FACILITIES.FID = :id";

// Check if a facility with the same name and address already exists
$stmt = $conn->prepare("SELECT COUNT(*) FROM FACILITIES WHERE name = :name AND address = :address");
$stmt->bindParam(':name', $name);
$stmt->bindParam(':address', $address);
$stmt->execute();
$count = $stmt->fetchColumn();

if ($count > 0) {
    // Prompt the user to re-enter all the data
    echo "A facility with the same name and address already exists. Do you want to refill the form?\n";
    // Add confirmation buttons to redirect or stay on the current page
    echo '<form action="add_facility.php" method="get">';
    echo '<button type="submit" name="refill" value="yes">Yes</button>';
    echo '</form>';
    // Check if the user wants to refill the form
    if (isset($_GET['refill']) && $_GET['refill'] == 'yes') {
        // Redirect the user back to the form page
        header("Location: add_facility.php");
        exit();
    } else {
        // Show a message and do nothing
        echo "Please re-enter all the data.";
    }
} else if(empty($PostalCode)) {
    echo "Postal code cannot be empty.";
} else {
    // Insert the location into the table
    $query = 'INSERT INTO LOCATION VALUES(?, ?, ?) ON DUPLICATE KEY UPDATE City=City';
    $stmt = $conn->prepare($query);
    $stmt->execute([$PostalCode, $City, $Province]);

    // Insert the facility information into the database
    $stmt = $conn->prepare("INSERT INTO FACILITIES (name, address, PostalCode, PhoneNumber, WebAddress, type, capacity) VALUES (:name, :address, :PostalCode, :PhoneNumber, :WebAddress, :type, :capacity)");
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':address', $address);
    $stmt->bindParam(':PostalCode', $PostalCode);
    $stmt->bindParam(':PhoneNumber', $PhoneNumber);
    $stmt->bindParam(':WebAddress', $WebAddress);
    $stmt->bindParam(':type', $type);
    $stmt->bindParam(':capacity', $capacity);
    $stmt->execute();
    // echo " Facility added successfully.  ";
    // echo "<button onclick=\"window.location.href='index.php'\">Return to facilities page</button>";
    header('Location: ./');
    exit;
}
?>
