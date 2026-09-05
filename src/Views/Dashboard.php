<!DOCTYPE html>

<html lang="en">
<head>
      <meta charset="UTF-8">
      <title>Dashboard</title>
      <link rel="stylesheet" href="/style.css">
</head>
<body>
      <form method="post" action="/dashboard">
            <h2>Dashboard</h2>

            <?php if(!isset($_SESSION["id"]) || !isset($_SESSION["email_verified_at"]) || $_SESSION["email_verified_at"] == null) {?>
                  <p class="auth-switch">
                        <?php echo !empty($message) ? $message : "Some error occured"; ?> <a href="/login" class="router-link">Go to login page</a>
                  </p>
            <?php } else { ?>
                  <table>
                        <tr>
                              <th>Id</th>
                              <td><?php echo $_SESSION['id']; ?></td>
                        </tr>
                        <tr>
                              <th>Email</th>
                              <td><?php echo $_SESSION['email']; ?></td>
                        </tr>
                        <tr>
                              <th>Name</th>
                              <td><?php echo $_SESSION['name']; ?></td>
                        </tr>
                  </table>

                  <a href="/logout" class="router-button">Logout</a>
            <?php } ?>
      </form>
</body>
</html>