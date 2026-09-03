<?php
session_start();

if(!isset($_SESSION["user_id"])){
    header("Location: login.php");
    exit;
}

?>

<!DOCTYPE html>

<h1>Dashboard</h1>
<p>User: <?= $_SESSION["name"] ?>!</p>
<p>User: <?= $_SESSION["id"] ?>!</p>
<a href="logout.php">Logout</a>

