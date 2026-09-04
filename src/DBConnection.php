<?php

$host = "127.0.0.1";
$user = "root";
$pass = "root";
$db = "auth_app";

try{
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
}
catch (PDOException $e) {
    die("Error connecting to DB: " . $e->getMessage());
}

