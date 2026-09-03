<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet"
          href="/style.css">
</head>
<body>
    <?php if ($message): ?>
        <p style="color: red;"><?= $message ?></p>
    <?php endif; ?>

    <form method="POST" action="/login">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required><br><br>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br><br>

        <button type="submit">Login</button>
    </form>
</body>
</html>