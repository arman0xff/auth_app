<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mail resend</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <form method="post" action="<?php echo FORGET_PASSWORD_ROUTE; ?>">
        <h2>Reset password</h2>

        <div class="verify-form-group">
            <label for="email">Write an email to reset password</label>
            <input type="email" id="email" name="email" required>
        </div>

        <?php if(!empty($error)) { ?>
            <span class="error"><?php echo $error; ?></span>
        <?php } ?>

        <?php if(!empty($success)) { ?>
            <span class="success"><?php echo $success; ?></span>
        <?php } ?>

        <button type="submit">Send</button>
    </form>
</body>
</html>