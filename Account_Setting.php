<?php 
session_start();

require "dbconnection.php";

// Handle email change
if (isset($_POST['change_email'])) {
    $newEmail = $_POST['new_email'];
    $currentEmail = $_SESSION['email'] ?? '';
    
    // Validate email
    if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['email_error'] = "Invalid email format";
    } elseif ($newEmail === $currentEmail) {
        $_SESSION['email_error'] = "New email cannot be the same as current email";
    } else {
        // Update email in database
        $stmt = $conn->prepare("UPDATE user_table SET email = ? WHERE username = ?");
        $stmt->bind_param("ss", $newEmail, $_SESSION['username']);
        
        if ($stmt->execute()) {
            $_SESSION['email'] = $newEmail;
            $_SESSION['email_success'] = "Email updated successfully!";
        } else {
            $_SESSION['email_error'] = "Failed to update email. Please try again.";
        }
        $stmt->close();
    }
}

// Handle password change
if (isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    
    // Verify current password
    $stmt = $conn->prepare("SELECT password FROM user_table WHERE username = ?");
    $stmt->bind_param("s", $_SESSION['username']);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
    
    if (!password_verify($currentPassword, $user['password'])) {
        $_SESSION['password_error'] = "Current password is incorrect";
    } elseif (strlen($newPassword) < 8) {
        $_SESSION['password_error'] = "New password must be at least 8 characters";
    } else {
        // Update password in database
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE user_table SET password = ? WHERE username = ?");
        $stmt->bind_param("ss", $hashedPassword, $_SESSION['username']);
        
        if ($stmt->execute()) {
            $_SESSION['password_success'] = "Password updated successfully!";
        } else {
            $_SESSION['password_error'] = "Failed to update password. Please try again.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Account Settings
    </title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: #fff;
        }

        .dashboard {
            display: flex;
            height: 100vh;
        }

        .sidebar {
            width: 250px;
            background: linear-gradient(to bottom, #78f5c5, #0056b3);
            color: white;
            padding: 30px;
        }

        .sidebar .user {
            margin-bottom: 30px;
        }

        .sidebar .menu {
            list-style: none;
            padding: 0;
        }

        .sidebar .menu li {
            padding: 10px 0;
            cursor: pointer;
            opacity: 0.8;
        }

        .sidebar .menu li:hover {
            font-weight: bold;
            opacity: 1;
        }

        .main {
            flex-grow: 1;
            padding: 30px;
            background: #e6eaed;
            display: flex;
            flex-direction: column;
        }

        .account-settings {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 800px;
            margin: 0 auto;
            width: 100%;
        }

        .account-settings h1 {
            text-align: center;
            margin-top: 0;
            color: #333;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
        }

        .setting-section {
            margin-bottom: 30px;
            border-bottom: 1px solid #eee;
            padding-bottom: 20px;
        }

        .setting-section:last-child {
            border-bottom: none;
        }

        .setting-section h2 {
            margin-top: 0;
            color: #444;
            font-size: 1.2em;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        .btn {
            background: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .btn:hover {
            background: #0056b3;
        }

        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
        }

        .avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid white;
        }
        
        .avatar-placeholder {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background-color: #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid white;
            font-size: 24px;
        }
        
        .avatar-upload-form {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }
        
        .avatar-actions {
            display: flex;
            gap: 10px;
        }
        
        .avatar-actions button {
            padding: 5px 10px;
            font-size: 12px;
            cursor: pointer;
        }
        
        .delete-avatar {
            background-color: #dc3545 !important;
        }
        
        #avatar {
            display: none;
        }
        .bi-gear{
        display: flex;
        justify-content: left;
        width:  16px;
        height: 16px;
        float: left;
        position: relative;
        left: -20px;
        }
        .bi-arrow-bar-left{
        display: flex;
        justify-content: left;
        width:  16px;
        height: 16px;
        float: left;
        position: relative;
        left: -20px;
        }
        .bi-eye{
        display: flex;
        justify-content: left;
        width:  16px;
        height: 16px;
        float: left;
        position: relative;
        left: -20px;
        }
        .bi-box-arrow-in-left{
        display: flex;
        justify-content: left;
        width:  16px;
        height: 16px;
        float: left;
        position: relative;
        left: -20px;
        }
    </style>
</head>
<body>
    <div class="dashboard">

        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="user">
                <div class="avatar-container" style="position: relative; display: inline-block;">
                    <?php if (isset($_SESSION['avatar_path']) && file_exists($_SESSION['avatar_path'])): ?>
                        <img src="<?= $_SESSION['avatar_path'] ?>" alt="Avatar" class="avatar">
                    <?php else: ?>
                        <div class="avatar-placeholder"><?= substr($_SESSION['username'] ?? 'JP', 0, 2) ?></div>
                    <?php endif; ?>
                    <button type="button" onclick="document.getElementById('avatar').click()" style="position: absolute;bottom: 0;right: 0;background: #007bff;border: none;border-radius: 50%;padding: 6px;cursor: pointer;color: white;"title="Change avatar">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                </div>
                <form class="avatar-upload-form" method="post" enctype="multipart/form-data">
                    <input type="file" name="avatar" id="avatar" accept="image/*">
                    <div class="avatar-actions">
                        <?php if (isset($_SESSION['avatar_path'])): ?>
                            <button type="submit" name="delete_avatar" class="delete-avatar">Delete</button>
                        <?php endif; ?>
                    </div>
                    <button type="submit" name="upload_avatar" style="display: none;" id="upload-btn"></button>
                </form>
            </div>
            <ul class="menu">
                <li><a href="index.php" style="color: white; text-decoration: none;">Dashboard</a>
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi-arrow-bar-left" viewBox="0 0 16 16">
                 <path fill-rule="evenodd" d="M12.5 15a.5.5 0 0 1-.5-.5v-13a.5.5 0 0 1 1 0v13a.5.5 0 0 1-.5.5M10 8a.5.5 0 0 1-.5.5H3.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L3.707 7.5H9.5a.5.5 0 0 1 .5.5"/>
                </svg>
            </li>
                <li>View Orders
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi-eye" viewBox="0 0 16 16">
                <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8M1.173 8a13 13 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5s3.879 1.168 5.168 2.457A13 13 0 0 1 14.828 8q-.086.13-.195.288c-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5s-3.879-1.168-5.168-2.457A13 13 0 0 1 1.172 8z"/>
                <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5M4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0"/>
                </svg>
                </li>
                <li><a href="Account_settings.php" style="color: white; text-decoration: none;">Account Settings</a>
                <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" class="bi-gear" viewBox="0 0 16 16">
                <path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492M5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0"/>
                <path d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115z"/>
                </svg></li>
                <li><a href="dash.php" style="color: white; text-decoration: none;">Logout</a>
                <svg xmlns="http://www.w3.org/2000/svg"fill="currentColor" class="bi-box-arrow-in-left" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M10 3.5a.5.5 0 0 0-.5-.5h-8a.5.5 0 0 0-.5.5v9a.5.5 0 0 0 .5.5h8a.5.5 0 0 0 .5-.5v-2a.5.5 0 0 1 1 0v2A1.5 1.5 0 0 1 9.5 14h-8A1.5 1.5 0 0 1 0 12.5v-9A1.5 1.5 0 0 1 1.5 2h8A1.5 1.5 0 0 1 11 3.5v2a.5.5 0 0 1-1 0z"/>
                        <path fill-rule="evenodd" d="M4.146 8.354a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5H14.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708z"/>
                        </svg></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main">
            <div class="account-settings">
                <h1>Account settings</h1>
                
                <!-- Email Section -->
                <div class="setting-section">
                    <h2>Email address</h2>
                    <p>Your email address is <?= htmlspecialchars($_SESSION['email'] ?? 'email@private.com') ?></p>
                    
                    <?php if (isset($_SESSION['email_error'])): ?>
                        <div class="message error"><?= $_SESSION['email_error'] ?></div>
                        <?php unset($_SESSION['email_error']); ?>
                    <?php elseif (isset($_SESSION['email_success'])): ?>
                        <div class="message success"><?= $_SESSION['email_success'] ?></div>
                        <?php unset($_SESSION['email_success']); ?>
                    <?php endif; ?>
                    
                    <form method="post">
                        <div class="form-group">
                            <label for="new_email">New email address</label>
                            <input type="email" id="new_email" name="new_email" required>
                        </div>
                        <button type="submit" name="change_email" class="btn">Change</button>
                    </form>
                </div>
                
                <!-- Password Section -->
                <div class="setting-section">
                    <h2>Password</h2>
                    
                    <?php if (isset($_SESSION['password_error'])): ?>
                        <div class="message error"><?= $_SESSION['password_error'] ?></div>
                        <?php unset($_SESSION['password_error']); ?>
                    <?php elseif (isset($_SESSION['password_success'])): ?>
                        <div class="message success"><?= $_SESSION['password_success'] ?></div>
                        <?php unset($_SESSION['password_success']); ?>
                    <?php endif; ?>
                    
                    <form method="post">
                        <div class="form-group">
                            <label for="new_password">New password</label>
                            <input type="password" id="new_password" name="new_password" required>
                        </div>
                        <div class="form-group">
                            <label for="current_password">Current password</label>
                            <input type="password" id="current_password" name="current_password" required>
                        </div>
                        <p><a href="forgot_password.php" style="color: #007bff; text-decoration: none;">Can't remember your current password? Reset your password</a></p>
                        <button type="submit" name="change_password" class="btn">Save password</button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Auto-submit form when file is selected
        document.getElementById('avatar').addEventListener('change', function() {
            if (this.files && this.files[0]) {
                if (this.files[0].size > 2432000) {
                    alert('File size exceeds 2MB limit');
                    this.value = '';
                } else {
                    document.getElementById('upload-btn').click();
                }
            }
        });
    </script>
</body>
</html>
