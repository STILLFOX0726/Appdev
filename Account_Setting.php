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
    <title>Account Settings</title>
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
                <li><a href="index.php" style="color: white; text-decoration: none;">Dashboard</a></li>
                <li>View Orders</li>
                <li><a href="Account_settings.php" style="color: white; text-decoration: none;">Account Settings</a></li>
                <li><a href="dash.php" style="color: white; text-decoration: none;">Logout</a></li>
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