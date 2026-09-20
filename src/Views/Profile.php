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
        <div class="profile-image">
            <img src="<?php echo($profileImageUrl ?? "storage/images/default-image.png")?>" alt="profile photo">
        </div>
        <table>
            <tr><td>First Name: </td><td><?php echo htmlspecialchars($userDto->firstName); ?></td></tr>
            <tr><td>Last Name: </td><td><?php echo htmlspecialchars($userDto->lastName); ?></td></tr>
            <tr><td>Phone: </td><td><?php echo htmlspecialchars($userDto->phone); ?></td></tr>
            <tr><td>Location: </td><td><?php echo htmlspecialchars($userDto->location); ?></td></tr>
            <tr><td>Date of birth: </td><td><?php echo htmlspecialchars($userDto->dateOfBirth); ?></td></tr>
            <tr><td>Bio: </td><td><?php echo htmlspecialchars($userDto->bio); ?></td></tr>
        </table>

        <?php if(empty($_GET['id'])) {?>
            <a href="<?= EDIT_PROFILE_ROUTE ?>" class="router-button">Edit</a>

            <h2 class="post-header">Add new post</h2>
            <form method="post" action="<?php echo ADD_NEW_POST_ROUTE ?>" class="post-form">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <label for="header">Write a header</label>
                <input type="text" id="header" name="header" placeholder="Type header" required>
                <label for="maintext">Write a main text</label>
                <textarea name="maintext" id="maintext" placeholder="Type main text"></textarea>
                <button type="submit">Add new post</button>
            </form>
        <?php } ?>

        <div>
            <?php if(!empty($userPosts)) {?>
                <?php foreach($userPosts as $post) {?>
                    <article>
                        <?php if(!empty($editingPostId) && $editingPostId == $post['id']) { ?>
                            <div class="post-edit">
                                <form method="post" action="<?php echo EDIT_POST_ROUTE ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                    
                                    <label for="edit-header-<?php echo $post['id']; ?>">Title</label>
                                    <input type="text" name="header" id="edit-header-<?php echo $post['id']; ?>" value="<?php echo htmlspecialchars($post['title']) ?>" required>

                                    <label for="edit-header-<?php echo $post['id']; ?>">Text</label>
                                    <input type="text" name="text" id="edit-text-<?php echo $post['id']; ?>" value="<?php echo htmlspecialchars($post['text']) ?>" required>
                                    
                                    <button type="submit">Save</button>
                                    <a href="<?php PROFILE_USER_ROUTE ?>" class="router-button">Cancel</a>
                                </form>
                            </div>
                        <?php } else { ?>
                            <h2 class="post-header"><?php echo htmlspecialchars($post['title']); ?></h2>
                            <p class="post-text"><?php echo nl2br(htmlspecialchars($post['text']), false); ?></p>
                            <p><?php echo $post['created_at']; ?></p>
                            <?php if(empty($_GET["id"])) { ?>
                                <a href="<?php echo EDIT_POST_ROUTE ?>?post_id=<?php echo($post['id']); ?>" class="router-button">Edit</a>

                                <form method="post" action="<?php echo DELETE_POST_ROUTE; ?>" style="display: inline;">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                    <button type="submit" class="router-button" onclick="return confirm('Are you sure?')">Delete</button>
                                </form>
                            <?php } ?>
                        <?php } ?>
                    </article>
                <?php } ?>
            <?php } ?>
        </div>
    <?php } ?>
    </main>
</body>
</html>