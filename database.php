<?php
session_start();
$server = 'kcc5531.encs.concordia.ca';
$username = 'kcc55314';
$passowrd = '5531ACMM';
$database = 'kcc55314';

try{
    $conn = new PDO("mysql:host=$server;dbname=$database", $username, $passowrd);
}catch(PDOException $e){
    die('Connection failed: '. $e->getMessage());
}
?>