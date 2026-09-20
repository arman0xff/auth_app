<?php use DTOs\User\ProfileUserDto;
/** @var ?ProfileUserDto $userDto */
?>

<!DOCTYPE html>
<html lang="en">
<head>
      <meta charset="UTF-8">
      <title>Profile</title>
      <link rel="stylesheet" href="/style.css">
</head>
<body>
    <main class="panel">
        <?php if(!empty($error)) {?>
            <p class="auth-switch">
                  <?php echo $error; ?> <a href="<?php echo PROFILE_USER_ROUTE?>" class="router-link">Return to your profile</a>
            </p>
        <?php } else { ?>
        <div>
            <?php if(!empty($posts)) {?>
                <?php foreach($posts as $post) {?>
                    <article>
                        <p class="post-text"><?php echo htmlspecialchars($post['first_name'] . " " . $post['last_name']); ?></p>
                        <h2 class="post-header"><?php echo htmlspecialchars($post['title']); ?></h2>
                        <p class="post-text"><?php echo nl2br(htmlspecialchars($post['text']), false); ?></p>
                        <p><?php echo $post['created_at']; ?></p>
                    </article>
                    <?php } ?>
                <?php } ?>
            <?php } ?>
        </div>
    </main>
</body>
</html>