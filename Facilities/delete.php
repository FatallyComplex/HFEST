<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Connect to the database
require_once '../database.php';

// Check if the delete button was clicked
if (isset($_POST['delete'])) {
    // Get the FID parameter value
    $FID = $_POST['fid'];

    // Prepare a DELETE statement to delete the facility with the given FID
    $query = "DELETE FROM FACILITIES WHERE FID = :FID";

    // Execute the statement with the FID parameter
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':FID', $FID);
    $stmt->execute();

    // Redirect back to the search page
    header('Location: index.php');
    exit();
}
?>
