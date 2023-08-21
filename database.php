<?php
session_start();
$server = '**************************';
$username = '*********************';
$passowrd = '************************';
$database = '************************';

try{
    $conn = new PDO("mysql:host=$server;dbname=$database", $username, $passowrd);
}catch(PDOException $e){
    die('Connection failed: '. $e->getMessage());
}
?>
