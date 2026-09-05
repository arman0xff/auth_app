<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <form method="post" action="/login">
        <h2>Login</h2>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>

        <?php if(!empty($message)) { ?>
            <span class="error"><?php echo $message; ?></span>
        <?php } ?>

        <?php if(isset($success)) { ?>
            <span class="success"><?php echo $success; ?></span>
        <?php } else {?>
            <p class="auth-switch">
                Don't have an account? <a class="router-link" href="/register">Register</a>
            </p>
        <?php } ?>

        <button type="submit">Login</button>
    </form>
</body>
</html>