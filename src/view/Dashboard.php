<!DOCTYPE html>

<html lang="en">
<head>
      <meta charset="UTF-8">
      <title>Dashboard</title>
      <link rel="stylesheet" href="/style.css">
</head>
<body>
      <h1>Dashboard</h1>
      <div><p>User: <?= e($_SESSION["name"]) ?>!</p></div>
      <div><p>Id: <?= e($_SESSION["id"]) ?></p></div>
      <div><a href="/logout">Logout</a></div>
</body>
</html>
