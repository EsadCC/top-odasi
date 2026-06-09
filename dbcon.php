<?php
function getDB() {
    $server   = "localhost";
    $username = "root";
    $password = "";        // macbook gebruikers vullen hier "root" in
    $db       = "escape-room"; // pas aan als jouw database anders heet

    try {
        $db_connection = new PDO("mysql:host=$server;dbname=$db;charset=utf8mb4", $username, $password);
        $db_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db_connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $db_connection;
    } catch (PDOException $e) {
        die("Verbinding mislukt: " . $e->getMessage());
    }
}
