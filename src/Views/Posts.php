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
        <div style="margin-bottom: 20px; display: flex; gap: 10px; justify-content: center;">
            <a href="<?php echo DASHBOARD_USER_ROUTE; ?>" class="router-button-main">Dashboard</a>
            <a href="<?php echo PROFILE_USER_ROUTE; ?>" class="router-button-main">My Profile</a>
        </div>

        <?php if(!empty($error)) {?>
            <p class="auth-switch">
                  <?php echo $error; ?> <a href="<?php echo PROFILE_USER_ROUTE?>" class="router-link">Return to your profile</a>
            </p>
        <?php } else { ?>
        <div>
            <p>Filter by category:</p>
            <form method="get" action="<?php echo POSTS_ROUTE ?>">
                <select name="category">
                    <option value="<?php echo 0?>">
                        All categories
                    </option>

                    <?php 
                        if(empty($categories)) { 
                            echo ('No category added'); 
                        }
                        else {
                            foreach($categories as $cat) { ?> 
                                <option value="<?php echo $cat['id']?>">
                                    <?php echo (isset($_GET['category']) && $_GET['category'] == $cat['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php
                            }
                        }
                    ?>
                </select>

                <button type="submit">Apply filter</button>
            </form>

            <?php if(!empty($posts)) {?>
                <?php foreach($posts as $post) {?>
                    <article>
                        <p class="post-author"><?php echo htmlspecialchars($post['first_name'] . " " . $post['last_name']); ?></p>
                        <h2 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h2>
                        <p class="post-text"><?php echo nl2br(htmlspecialchars($post['text']), false); ?></p>
                        <p class="post-date"><?php echo $post['created_at']; ?></p>
                        <?php foreach($tags as $post) {?>

                        ?>
                    </article>
                    <?php } ?>
                <?php } ?>
            <?php } ?>
        </div>
    </main>
</body>
</html>