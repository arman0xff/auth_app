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
            <img src="<?php echo($userDto->profileImageUrl ?? "storage/images/default-image.png")?>" alt="profile photo">
        </div>
        <table>
            <tr><td>First Name: </td><td><?php echo htmlspecialchars($userDto->firstName); ?></td></tr>
            <tr><td>Last Name: </td><td><?php echo htmlspecialchars($userDto->lastName); ?></td></tr>
            <tr><td>Phone: </td><td><?php echo htmlspecialchars($userDto->phone); ?></td></tr>
            <tr><td>Location: </td><td><?php echo htmlspecialchars($userDto->location); ?></td></tr>
            <tr><td>Date of birth: </td><td><?php echo htmlspecialchars($userDto->dateOfBirth); ?></td></tr>
            <tr><td>Bio: </td><td><?php echo htmlspecialchars($userDto->bio); ?></td></tr>
        </table>

        <?php if (empty($_GET['id'])) {?>
            <a href="<?= EDIT_PROFILE_ROUTE ?>" class="router-button">Edit</a>

            <h2 class="post-header">Add new post</h2>
            <form method="post" action="/profile/add-new-post" class="post-form">
                <label for="header">Write a header</label>
                <input type="text" id="header" name="header" placeholder="Type header" required>
                <label for="maintext">Write a main text</label>
                <textarea name="maintext" id="maintext" placeholder="Type main text"></textarea>
                <button type="submit">Add new post</button>
            </form>
        <?php } ?>

        <div>
            <?php if (!empty($userPosts)) {?>
                <?php foreach($userPosts as $post) {?>
                    <article>
                        <h2><?php echo $post['title']; ?></h2>
                        <p><?php echo $post['text']; ?></p>
                        <p><?php echo $post['created_at']; ?></p>
                        <a href="edit-post.php?post_id=1" class="router-button">Edit</a>
                        <a href="delete-post.php?post_id=1" class="router-button">Delete</a>
                    </article>
                <?php } ?>
            <?php } ?>
        </div>
    <?php } ?>
    </main>
</body>
</html>