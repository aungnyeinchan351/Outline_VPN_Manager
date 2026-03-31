<?php
session_start();
require_once 'config.php'; // Ensure this exists with $admin_password

// Handle Login
if (isset($_POST['login'])) {
    if ($_POST['password'] === $admin_password) {
        $_SESSION['logged_in'] = true;
        header("Location: index.php");
        exit();
    } else { $error = "Incorrect password!"; }
}

// Handle Password Change
if (isset($_POST['change_pw'])) {
    if ($_POST['old_password'] === $admin_password) {
        $newPass = addslashes($_POST['new_password']);
        $content = "<?php \$admin_password = '$newPass'; ?>";
        file_put_contents('config.php', $content);
        $success = "Password changed successfully!";
    } else { $error = "Old password incorrect!"; }
}
?>
<!DOCTYPE html>
<html>
<head><link rel="stylesheet" href="style.css"></head>
<body>
<div class="container">
    <h2>Admin Login</h2>
    <?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <?php if(isset($success)) echo "<p style='color:green;'>$success</p>"; ?>

    <form method="POST" id="loginForm">
        <input type="password" name="password" placeholder="Enter Password" required>
        <button type="submit" name="login" class="btn-generate">Login</button>
    </form>

    <p style="text-align:center; cursor:pointer; color:#3498db; font-size:0.8rem;" onclick="togglePW()">Change Password?</p>

    <form method="POST" id="pwForm" style="display:none; border-top:1px solid #ddd; padding-top:10px;">
        <input type="password" name="old_password" placeholder="Old Password" required>
        <input type="password" name="new_password" placeholder="New Password" required>
        <button type="submit" name="change_pw" class="btn-generate">Update Password</button>
    </form>
    <p style="text-align:center; cursor:pointer; color:#3498db; font-size:0.8rem;" 
       onclick="alert('Contact your server admin for help. Thank you!')">
       Forgot Password?
    </p>
</div>
<script>
function togglePW() {
    document.getElementById('loginForm').style.display = 'none';
    document.getElementById('pwForm').style.display = 'block';
}
</script>
</body>
</html>