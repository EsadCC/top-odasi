<?php
$server   = "localhost";
$username = "root";
$password = "";        
$db       = "escape-room";

try {
    $db_connection = new PDO("mysql:host=$server;dbname=$db;charset=utf8mb4", $username, $password);
    $db_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db_connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Verbinding mislukt: " . $e->getMessage());
}
