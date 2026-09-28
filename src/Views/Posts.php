<?php use DTOs\User\ProfileUserDto;
use Services\E_IMAGES_TYPES;

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
                    <option value="<?php echo 0?>">All categories</option>

                    <?php if(empty($categories)) { ?>
                            <option disabled>No category added</option>
                    <?php } else { ?>
                        <?php foreach($categories as $cat) { ?> 
                            <option value="<?php echo $cat['id']; ?>" <?php echo isset($_GET['category']) && $_GET['category'] == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php } ?>
                    <?php } ?>
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
                        
                        <?php if(!empty($post['tags'])) { ?>
                            <div class="post-tags">
                                <strong>Tags:</strong>
                                <?php foreach(explode(', ', $post['tags']) as $tagName) { ?>
                                    <span class="tag-badge">#<?php echo htmlspecialchars($tagName); ?></span>
                                <?php } ?>
                            </div>
                        <?php } ?>

                        <?php if (!empty($post['images'])) { ?>
                            <div class="post-images">
                                <?php foreach(explode(',', $post['images']) as $imageName) { ?>
                                    <img src="<?php echo htmlspecialchars(\Services\ImagesService::getWebDirByType(E_IMAGES_TYPES::Post) . $imageName) ?>" alt="Post image" class="post-images">
                                <?php } ?>
                            </div>
                        <?php } ?>

                        <details style="margin-top: 6px;">
                            <summary style="cursor: pointer; color: blue;">Reply</summary>
                            <form method="post" action="/comment/add" style="margin-top: 6px;">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                
                                <input type="text" name="text" placeholder="Write a reply..." required maxlength="255">
                                <button type="submit">Send</button>
                            </form>
                        </details>

                        <?php if(!empty($post['comment'])) { ?>
                            <div class="post-comments">
                                <?php foreach ($post['comment'] as $c) { ?>
                                    <div class="comment-item">
                                        <?php if ($c['deleted_at'] !== null) { ?>
                                            <p style="color: gray; font-style: italic;">This comment has been deleted</p>
                                        <?php } else { ?>
                                            <p>
                                                <strong><?php echo htmlspecialchars($c['first_name'] . ' ' . $c['last_name']); ?>:</strong> 
                                                <?php echo htmlspecialchars($c['text']); ?>
                                            </p>
                                            
                                            <details style="margin-top: 6px;">
                                                <summary style="cursor: pointer; color: blue;">Reply</summary>
                                                <form method="post" action="/comment/add" style="margin-top: 6px;">
                                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                                    <input type="hidden" name="parent_id" value="<?php echo $c['id']; ?>">
                                                    
                                                    <input type="text" name="text" placeholder="Write a reply..." required maxlength="255">
                                                    <button type="submit">Send</button>
                                                </form>
                                            </details>
                                        <?php } ?>
                                    </div>
                                <?php } ?>
                            </div>
                        <?php } ?>
                    </article>
                <?php } ?>
            <?php } ?>
        </div>
    <?php } ?>
</main>
</body>
</html>