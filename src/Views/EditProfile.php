<!DOCTYPE html>
<html lang="en">
<head>
      <meta charset="UTF-8">
      <title>Edit profile</title>
      <link rel="stylesheet" href="/style.css">
</head>
<body>
    <main class="panel">
        <img src="image.png" alt="profile photo">

        <form method="post" action="<?php echo EDIT_PROFILE_ROUTE; ?>" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <input type="text" name="first_name" placeholder="First Name">
            <input type="text" name="last_name" placeholder="Last Name">
            <input type="text" name="phone" placeholder="Phone">
            <input type="text" name="location" placeholder="Location">
            <input type="date" name="dob" placeholder="Date of Birth">
            <textarea name="bio" placeholder="Bio"></textarea>
            <button type="button" onclick="document.querySelector('input[type=file]').click()">Upload Profile Picture</button>
            <input type="file" name="profile_image" style="display:none">
            <label for="delete_picture">Delete Profile Picture</label>
            <input type="checkbox" name="delete_picture" id="delete_picture" value="1">
            <button type="submit">Update Profile</button>
        </form>
    </main>
</body>
</html>