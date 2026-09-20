<?php use DTOs\User\ProfileUserDto;
/** @var ?ProfileUserDto $userDto */
?>
<!DOCTYPE html>
<html lang="en">
<head>
      <meta charset="UTF-8">
      <title>Edit profile</title>
      <link rel="stylesheet" href="/style.css">
</head>
<body>
    <main class="panel">
        <form method="post" action="<?php echo EDIT_PROFILE_ROUTE; ?>" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <input type="text" name="first_name" placeholder="First Name" value="<?= htmlspecialchars($userDto?->firstName ?? '') ?>">
            <input type="text" name="last_name" placeholder="Last Name" value="<?= htmlspecialchars($userDto?->lastName == null ? '' : $userDto?->lastName) ?>">
            <input type="text" name="phone" placeholder="Phone" value="<?= htmlspecialchars($userDto?->phone == null ? '' : $userDto?->phone) ?>">
            <input type="text" name="location" placeholder="Location" value="<?= htmlspecialchars($userDto?->location == null ? '' : $userDto?->location) ?>">
            <input type="date" name="dob" placeholder="Date of Birth" value="<?= htmlspecialchars($userDto?->dateOfBirth == null ? '' : $userDto?->dateOfBirth) ?>">
            <textarea name="bio" placeholder="Bio"><?= htmlspecialchars($userDto?->bio == null ? '' : $userDto?->bio) ?></textarea>

            <button type="button" onclick="document.querySelector('input[type=file]').click()">Upload Profile Picture</button>
            <input type="file" name="profile_image" style="display:none">
            <label for="delete_image">Delete Profile Image</label>
            <input type="checkbox" name="delete_image" id="delete_image" value="1">
            <button type="submit">Update Profile</button>
        </form>

        <?php if(!empty($success)) { ?>
            <span class="success"><?php echo $success; ?></span>
        <?php } ?>

        <?php if(!empty($errors)): ?>
            <div class="errors">
                <?php foreach ($errors as $error): ?>
                    <p style="color: red;"><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>