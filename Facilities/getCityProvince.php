<?php
require_once "../database.php";

$firstLetter = $_GET["firstLetter"];

try {
    $stmt = $conn->prepare("SELECT GetCityFullName(?) AS City, GetProvinceFullName(?) AS Province");
    $stmt->execute([$firstLetter, $firstLetter]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode($result);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
