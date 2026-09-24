<?php use DTOs\User\DashboardUserDto;
/** @var ?DashboardUserDto $userDto */
?>

<!DOCTYPE html>
<html lang="en">
<head>
      <meta charset="UTF-8">
      <title>Dashboard</title>
      <link rel="stylesheet" href="/style.css">
</head>
<body>
      <form method="post" action="<?php echo DASHBOARD_USER_ROUTE?>">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <h2>Dashboard</h2>

            <?php if(!empty($error)) {?>
                  <p class="auth-switch">
                        <?php echo $error; ?> <a href="<?php echo LOGIN_USER_ROUTE . "?redirect=" . LOGIN_USER_ROUTE?>" class="router-link">Go to login page</a>
                  </p>
            <?php } else { ?>
                  <table>
                        <tr>
                              <th>Id</th>
                              <td><?php echo htmlspecialchars($userDto->id); ?></td>
                        </tr>
                        <tr>
                              <th>Email</th>
                              <td><?php echo htmlspecialchars($userDto->email); ?></td>
                        </tr>
                        <tr>
                              <th>First name</th>
                              <td><?php echo htmlspecialchars($userDto->firstName); ?></td>
                        </tr>
                        <tr>
                              <th>Last name</th>
                              <td><?php echo htmlspecialchars($userDto->lastName); ?></td>
                        </tr>
                        <tr>
                              <th>Role</th>
                              <td><?php echo htmlspecialchars($userDto->role); ?></td>
                        </tr>
                  </table>

                  <a href="<?php echo PROFILE_USER_ROUTE; ?>" class="router-button">My profile</a>
                  <a href="<?php echo POSTS_ROUTE; ?>" class="router-button">All Posts</a>

                  <?php if(isset($userDto) && $this->authService->can($userDto->role, 'access_admin_page')) {?>
                        <a href="<?php echo ADMIN_PANEL_ROUTE?>" class="router-button-main">Go to admin panel</a>
                  <?php } ?>
                  <?php if(isset($userDto) && $this->authService->can($userDto->role, 'access_moderator_page')) {?>
                        <a href="<?php echo MODERATOR_PANEL_ROUTE?>" class="router-button-main">Go to moderator page</a>
                  <?php } ?>

                  <a href="<?php echo LOGOUT_USER_ROUTE?>" class="router-button">Logout</a>
            <?php } ?>
      </form>
</body>
</html>