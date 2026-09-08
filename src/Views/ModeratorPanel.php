<!DOCTYPE html>

<html lang="en">
<head>
      <meta charset="UTF-8">
      <title>Dashboard</title>
      <link rel="stylesheet" href="/style.css">
</head>
<body>
      <h2>Moderator panel</h2>

      <?php if(!isset($_SESSION["id"]) || !isset($_SESSION["email_verified_at"]) || $_SESSION["email_verified_at"] == null 
            || !isset($_SESSION["role"]) || isset($_SESSION["role"]) && !$this->authService->can('access_moderator_page')) {?>
            <p class="auth-switch">
                  <?php echo !empty($message) ? $message : "Some error occured"; ?> <a href="<?php echo LOGIN_USER_ROUTE?>" class="router-link">Go to login page</a>
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
                                    <td><?php echo $user['id']; ?></td>
                                    <td><?php echo $user['name']; ?></td>
                                    <td><?php echo $user['email']; ?></td>
                                    <td><?php echo $user['role']; ?></td>
                                    <td><?php echo $user['email_verified_at'] != null ? "Verified" : "Not verified"; ?></td>
                                    <td><?php echo $user['created_at']; ?></td>
                              </tr>
                        <?php } ?>
                  </tbody>
            </table>
      <?php } ?>
</body>
</html>