<!DOCTYPE html>

<html lang="en">
<link rel="stylesheet"
      href="/style.css">

<body>
<h1>Dashboard</h1>
<p>User: <?= $_SESSION["name"] ?>!</p>
<p>Id: <?= $_SESSION["id"] ?>!</p>
<a href="/logout">Logout</a>
</body>
</html>
