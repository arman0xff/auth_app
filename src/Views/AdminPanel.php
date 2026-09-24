<?php /** @var array $users */ /** @var array $categories */?>

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
                                    <th>First name</th>
                                    <th>Last name</th>
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
                                          <td><?php echo htmlspecialchars($user['first_name']); ?></td>
                                          <td><?php echo htmlspecialchars($user['last_name']); ?></td>
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

                  <hr style="border: 0; border-top: 1px solid #b40e90;">
                  <h2>Manage categories</h2>

                  <form method="post" action="<?php echo ADMIN_PANEL_ROUTE; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="action" value="create_cat">

                        <label for="cat_name">Category name</label>
                        <input type="text" id="cat_name" name="cat_name" required>
                        <button type="submit">Add category</button>
                  </form>

                  <table>
                        <thead>
                              <tr>
                                    <td>Category name</td>
                                    <td>Actions</td>
                              </tr>
                        </thead>
                        <tbody>
                              <?php foreach($categories as $cat) { ?>
                                    <tr>
                                          <td>  
                                                <form method="post" action="<?php echo ADMIN_PANEL_ROUTE; ?>">
                                                      <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                      <input type="hidden" name="action" value="edit_cat">
                                                      <input type="hidden" name="cat_id" value="<?php echo $cat['id']; ?>">

                                                      <input type="text" name="edit_cat" value="<?php echo htmlspecialchars($cat["name"])?>" required>
                                                      <button type="submit" style="margin: 0; padding: 6px 12px; ">Save new name</button>
                                                </form>
                                          </td>
                                    </tr>
                                    <tr>
                                          <td>
                                                <form method="post" action="<?php echo ADMIN_PANEL_ROUTE; ?>">
                                                      <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                      <input type="hidden" name="action" value="delete_cat">
                                                      <input type="hidden" name="cat_id" value="<?php echo $cat['id']; ?>">

                                                      <button type="submit" style="margin: 0; padding: 6px 12px" onclick="return confirm('Are you sure you want to delete this category?')">Delete</button>
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