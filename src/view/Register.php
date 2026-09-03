<!DOCTYPE html>
<html lang="en">
<body>
    <?php if ($message): ?>
        <p style="color: red;"><?= $message ?></p>
    <?php endif; ?>

    <form method="post" action="/register">
        <label> Name: <input type="text" id="name" name="name" required><br><br> </label>
        <label> Email: <input type="email" id="email" name="email" required><br><br></label>
        <label> Password: <input type="password" id="password" name="password" required><br><br> </label>

        <button type="submit">Register</button>
    </form>
</body>
</html>