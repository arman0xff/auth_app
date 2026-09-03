<!DOCTYPE html>

<html lang="en">
<link rel="stylesheet"
      href="/style.css">

<body>
<h1>Dashboard</h1>
<div><p>User: <?= $_SESSION["name"] ?>!</p></div>
<div><p>Id: <?= $_SESSION["id"] ?>!</p></div>
<div><a href="/logout">Logout</a></div>
</body>
</html>
