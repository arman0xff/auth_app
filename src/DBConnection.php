<?php

$host = "127.0.0.1";
$user = "root";
$pass = "root";
$db = "auth_app";

try{
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
}
catch (PDOException $e) {
    exit("Error connecting to DB: " . $e->getMessage());
}