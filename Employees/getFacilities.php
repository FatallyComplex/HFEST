<?php
require_once '../database.php';

if (isset($_GET['city'])) {
    $city = $_GET['city'];

    $stmt = $conn->prepare("SELECT * FROM FACILITIES JOIN LOCATION ON FACILITIES.PostalCode = LOCATION.PostalCode WHERE City = ?");
    $stmt->execute([$city]);
    $facilities = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($facilities);
}
?>
