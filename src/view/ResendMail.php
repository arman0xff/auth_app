<!DOCTYPE html>
<html lang="en">
<link rel="stylesheet"
      href="/style.css">
<body>
    <?php if ($message): ?>
        <p style="color: red;"><?= $message ?></p>
    <?php endif; ?>

    <form method="post" action="/resend-mail">
        <label> Email: <input type="email" id="email" name="email" required><br></label>
        <button type="submit">Send</button>
    </form>
</body>
</html>