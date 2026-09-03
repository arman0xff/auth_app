<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <div class="card">
        <h1>Login</h1>
        <?php if ($message): ?>
            <p style="color: red;"><?= $message ?></p>
        <?php endif; ?>

        <form method="POST" action="/login">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>

        <a class="link" href="/register">Register</a>
    </div>
</body>
</html>