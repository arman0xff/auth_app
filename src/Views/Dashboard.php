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
                        <?php echo $error; ?> <a href="<?php echo LOGIN_USER_ROUTE?>" class="router-link">Go to login page</a>
                  </p>
            <?php } else { ?>
                  <table>
                        <tr>
                              <th>Id</th>
                              <td><?php echo htmlspecialchars($_SESSION['id']); ?></td>
                        </tr>
                        <tr>
                              <th>Email</th>
                              <td><?php echo htmlspecialchars($_SESSION['email']); ?></td>
                        </tr>
                        <tr>
                              <th>Name</th>
                              <td><?php echo htmlspecialchars($_SESSION['name']); ?></td>
                        </tr>
                        <tr>
                              <th>Role</th>
                              <td><?php echo htmlspecialchars($_SESSION['role']); ?></td>
                        </tr>
                  </table>

                  <?php if(isset($_SESSION['role']) && $this->authService->can('access_admin_page')) {?>
                        <a href="<?php echo ADMIN_PANEL_ROUTE?>" class="router-button-main">Go to admin panel</a>
                  <?php } ?>
                  <?php if(isset($_SESSION['role']) && $this->authService->can('access_moderator_page')) {?>
                        <a href="<?php echo MODERATOR_PANEL_ROUTE?>" class="router-button-main">Go to moderator page</a>
                  <?php } ?>

                  <a href="<?php echo LOGOUT_USER_ROUTE?>" class="router-button">Logout</a>
            <?php } ?>
      </form>
</body>
</html>