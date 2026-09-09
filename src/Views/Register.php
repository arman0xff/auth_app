<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registration</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <form method="post" action="<?php echo REGISTER_USER_ROUTE?>">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

        <h2>Registration</h2>

        <div class="form-group">
            <label> Name</label>
            <input type="text" name="name" id="name" placeholder="Arman Vardanyan" required>
            <?php if (!empty($errors['name'])) { ?>
                <span class="error"><?php echo $errors['name']; ?></span>
            <?php } ?>
        </div>

        <div class="form-group">
            <label> Email</label>
            <input type="email" name="email" id="email" placeholder="arman@email.com" required>
            <?php if (!empty($errors['email'])) { ?>
                <span class="error"><?php echo $errors['email']; ?></span>
            <?php } ?>
        </div>

        <div class="form-group">
            <label> Password</label>
            <input type="password" id="password" name="password" placeholder="******" required>
            <?php if (!empty($errors['password'])) { ?>
                <span class="error"><?php echo $errors['password']; ?></span>
            <?php } ?>
        </div>

        <button type="submit">Register</button>

        <p class="auth-switch">
            Already have an account? <a class="router-link" href="<?php echo LOGIN_USER_ROUTE?>">Login</a>
        </p>

        <?php if(!empty($errors['button'])) { ?>
            <span class="error"><?php echo $errors['button']; ?></span>
        <?php } ?>
    </form>
</body>
</html>