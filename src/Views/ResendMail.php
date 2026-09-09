<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mail resend</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>
    <form method="post" action="<?php echo RESEND_MAIL_ROUTE; ?>">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

        <h2>New verification link</h2>

        <div class="verify-form-group">
            <label for="email">Write an email to request a new verification link</label>
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