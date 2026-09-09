<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <form method="post" action="<?php echo LOGIN_USER_ROUTE?>">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

        <h2>Login</h2>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
            <a class="router-link2" href="<?php echo FORGET_PASSWORD_ROUTE?>">Forget password?</a>
        </div>

        <?php if(!empty($error)) { ?>
            <span class="error"><?php echo $error; ?></span>
        <?php } ?>

        <?php if(isset($success)) { ?>
            <span class="success"><?php echo $success; ?></span>
        <?php }?>

        <button type="submit">Login</button>

        <?php if(!isset($success) || !strlen($success)) { ?>
            <p class="auth-switch">
                Don't have an account? <a class="router-link" href="<?php echo REGISTER_USER_ROUTE?>">Register</a>
            </p>
        <?php } ?>
    </form>
</body>
</html>