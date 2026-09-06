<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mail resend</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <form method="post" action="<?php echo RESET_PASSWORD_ROUTE; ?>">
        <h2>Set new password</h2>

        <div class="verify-form-group">
            <label for="new-password">Write new password</label>
            <input type="password" id="new-password" name="new-password" required>
        </div>

        <div class="verify-form-group">
            <label for="confirm-new-password">Confirm new password</label>
            <input type="password" id="confirm-new-password" name="confirm-new-password" required>
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