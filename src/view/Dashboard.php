<?php
session_start();

if(!isset($_SESSION["id"])){
    header("Location: Login.php");
    exit;
}

?>

<!DOCTYPE html>

<h1>Dashboard</h1>
<p>User: <?= $_SESSION["name"] ?>!</p>
<p>User: <?= $_SESSION["id"] ?>!</p>
<a href="../Logout.php">Logout</a>

