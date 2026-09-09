<?php /** @var array $users */?>

<!DOCTYPE html>

<html lang="en">
<head>
      <meta charset="UTF-8">
      <title>Dashboard</title>
      <link rel="stylesheet" href="/style.css">
</head>
<body class="page-body">
      <main class="panel">
            <h2>Admin panel</h2>

            <?php if(!empty($error)) { ?>
                  <p class="auth-switch">
                        <?php echo $error; ?> <a href="<?php echo LOGIN_USER_ROUTE?>" class="router-link">Go to login page</a>
                  </p>
            <?php } else {?>
                  <table>
                        <thead>
                              <tr>
                                    <th>Id</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Email verification status</th>
                                    <th>Created date</th>
                                    <th>Change role</th>
                              </tr>
                        </thead>

                        <tbody>
                              <?php foreach($users as $user) { ?>
                                    <tr>
                                          <td><?php echo htmlspecialchars($user['id']); ?></td>
                                          <td><?php echo htmlspecialchars($user['name']); ?></td>
                                          <td><?php echo htmlspecialchars($user['email']); ?></td>
                                          <td><?php echo htmlspecialchars($user['role']); ?></td>
                                          <td><span class="badge <?php echo $user['email_verified_at'] != null ? "badge-success" : "badge-danger"; ?>">
                                                <?php echo $user['email_verified_at'] != null ? "Verified" : "Not verified"; ?>
                                                </span>
                                          </td>
                                          <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                                          <td>
                                                <form method="post" action="<?php echo ADMIN_PANEL_ROUTE?>">
                                                      <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                      <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                      <select name="new_role">
                                                            <option value="admin" <?php echo $user['role'] == "admin" ? "selected" : ""; ?>>Admin</option>
                                                            <option value="moderator" <?php echo $user['role'] == "moderator" ? "selected" : ""; ?>>Moderator</option>
                                                            <option value="user" <?php echo $user['role'] == "user" ? "selected" : ""; ?>>User</option>
                                                      </select>
                                                      <button type="submit">Change role</button>
                                                </form>
                                          </td>
                                    </tr>
                              <?php } ?>
                        </tbody>
                  </table>
            <?php } ?>
      </main>
</body>
</html>