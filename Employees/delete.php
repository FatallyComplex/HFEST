<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Connect to the database
require_once '../database.php';

// Check if the delete button was clicked
if (isset($_POST['delete'])) {
    // Get the FID parameter value
    $MedCardNumber = $_POST['MedCardNumber'];

    // Prepare a DELETE statement to delete the facility with the given FID
    $query = "DELETE FROM EMPLOYEE WHERE MedCardNumber = :MedCardNumber";

    // Execute the statement with the FID parameter
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':MedCardNumber', $MedCardNumber);
    $stmt->execute();

    // Redirect back to the search page
    header('Location: index.php');
    exit();
}
?>