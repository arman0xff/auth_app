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

        <a href="<?php echo(EDIT_PROFILE_ROUTE)?>" class="router-button">Edit</a>

        <form method="post" action="add-new-post">
            <label for="header">Write a header</label>
            <input type="text" id="header" name="header" placeholder="Type header" required>
            <label for="maintext">Write a main text</label>
            <textarea name="maintext" id="maintext" placeholder="Type main text"></textarea>
            <button type="submit">Add new post</button>
        </form>

        <div>
            <article>
                <h2>Placeholder header</h2>
                <p>Placeholder text</p>
                <p>Placeholder date</p>
                <a href="edit-post.php?post_id=1" class="router-button">Edit</a>
                <a href="delete-post.php?post_id=1" class="router-button">Delete</a>
            </article>
        </div>
    </main>
</body>
</html>