<?php
session_start();
require "dbconnection.php"; 

if (isset($_SESSION['user_id'])) {

    header("Location: login.php");
    exit();
}

$success_message = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $db->real_escape_string($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($username) || empty($password) || empty($confirm_password)) {
        $error_message = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error_message = "Password must be at least 6 characters long.";
    } else {
        
        $stmt = $db->prepare("SELECT user_id FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error_message = "Username already exists. Please choose a different one.";
        } else {
           
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt_insert = $db->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt_insert->bind_param("ss", $username, $hashed_password);

            if ($stmt_insert->execute()) {
                $new_user_id = $stmt_insert->insert_id;

                $_SESSION['user_id'] = $new_user_id;
                $_SESSION['username'] = $username;

                header("Location: login.php");
                exit();
            } else {
                $error_message = "Error: Please try again!" . $stmt_insert->error;
            }
            $stmt_insert->close();
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: helvetica, sans-serif;
            background-color: #c9c9c9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .register-container {
            padding: 100px;
            border: 1px solid #ddd;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(70,70,70,0.5);
            max-width: 500px;
            width: 100%;
            box-sizing: border-box;
            text-align: center;
            border-radius: 8px;
            
        }

        h1 {
            text-align: center;
            color: #333;
            margin-top: 0;
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            text-align: left;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        input[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: #28a745; 
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #218838;
        }

        .success-message {
            color: #28a745; /* Bootstrap green for success */
            margin-bottom: 15px;
            font-weight: bold;
        }

        .error-message {
            color: #dc3545; /* Bootstrap red for errors */
            margin-bottom: 15px;
            font-weight: bold;
        }

        .login-link {
            margin-top: 20px;
            font-size: 14px;
            font-style: italic;
        }

        .login-link a {
            color: #007bff;
            text-decoration: none;
        }

        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<form action ="login.php" method=" POST">
    <div class="register-container">
        <h1>User Registration</h1>
        <?php if (!empty($success_message)): ?>
        <div class="success-message"><?php echo $success_message; ?></div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
        <div class="error-message"><?php echo $error_message; ?></div>
        <?php endif; ?>
        <form action="register.php" method="POST">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>

        <label for="confirm_password">Confirm Password:</label>
        <input type="password" id="confirm_password" name="confirm_password" required>

     
        

        <input type="submit" value="Register">
        </form>
        <div class="login-link">
        Already have an account? <a href="login.php">Login here</a>
        </div>
        </div>

</body>
</html>

