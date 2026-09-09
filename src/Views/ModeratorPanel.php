<!DOCTYPE html>

<html lang="en">
<head>
      <meta charset="UTF-8">
      <title>Dashboard</title>
      <link rel="stylesheet" href="/style.css">
</head>
<body>
      <h2>Moderator panel</h2>

      <?php if(!empty($error)) {?>
            <p class="auth-switch">
                  <?php echo $error; ?> <a href="<?php echo LOGIN_USER_ROUTE?>" class="router-link">Go to login page</a>
            </p>
      <?php } else { ?>
            <table>
                  <thead>
                        <tr>
                              <th>Id</th>
                              <th>Name</th>
                              <th>Email</th>
                              <th>Role</th>
                              <th>Email verification status</th>
                              <th>Created date</th>
                        </tr>
                  </thead>

                  <tbody>
                        <?php foreach($users as $user) { ?>
                              <tr>
                                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                                    <td><?php echo htmlspecialchars($user['name']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><?php echo htmlspecialchars($user['role']); ?></td>
                                    <td><?php echo $user['email_verified_at'] != null ? "Verified" : "Not verified"; ?></td>
                                    <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                              </tr>
                        <?php } ?>
                  </tbody>
            </table>
      <?php } ?>
</body>
</html>