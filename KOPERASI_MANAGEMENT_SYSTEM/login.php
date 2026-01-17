<?php
session_start();
require_once __DIR__ . '/dbconn.php';

$conn = $dbconn; 

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // NOTE: In a real app, use password_verify() instead of plain text comparison
    $sql = "SELECT userID, name FROM UserAccount 
            WHERE username = :u AND password = :p AND status = 'ACTIVE'";

    $stid = oci_parse($conn, $sql);
    oci_bind_by_name($stid, ":u", $username);
    oci_bind_by_name($stid, ":p", $password);

    oci_execute($stid);
    $user = oci_fetch_assoc($stid);

    if ($user) {
        $_SESSION['user_id']   = $user['USERID'];
        $_SESSION['user_name'] = $user['NAME'];
        header("Location: index.php");
        exit();
    } else {
        $error = "Invalid username/password or account inactive.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login Now</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(to bottom, #f0f2f5 50%, #8b2626 50%);            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .login-container {
            background: white;
            width: 400px;
            padding: 40px;
            border-radius: 30px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            position: relative;
            text-align: left;
        }

        /* The Purple Lock Icon Circle */
        .icon-header {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #d63031, #c0392b);            border-radius: 50%;
            position: absolute;
            top: -40px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            justify-content: center;
            align-items: center;
            border: 5px solid white;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .icon-header::after {
            content: '🔒'; /* Simple emoji icon, replace with <img> if preferred */
            font-size: 30px;
            color: white;
        }

        h2 {
            color: #c0392b; 
            text-align: center;
            font-size: 28px;
            margin-top: 20px;
            margin-bottom: 30px;
        }

        label {
            display: block;
            color: #b0b0b0;
            font-size: 14px;
            margin-bottom: 8px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 15px;
            margin-bottom: 20px;
            border: none;
            background-color: #e8e8e8;
            border-radius: 25px;
            box-sizing: border-box;
            outline: none;
            color: #666;
        }

        .remember-me {
            display: flex;
            align-items: center;
            color: #b0b0b0;
            font-size: 14px;
            margin-bottom: 25px;
        }

        .remember-me input {
            margin-right: 8px;
        }

        button {
            background-color: #d63031;
            width: 100%;
            padding: 15px;
            color: white;
            border: none;
            border-radius: 25px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            text-transform: uppercase;
            transition: background 0.3s;
        }

        button:hover {
            background-color: #b02121;        
        }

        .error {
            color: #e74c3c;
            text-align: center;
            margin-bottom: 15px;
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="icon-header"></div>
    
    <h2>Login Now</h2>

    <?php if ($error): ?>
        <p class="error"><?= $error; ?></p>
    <?php endif; ?>

    <form method="post">
        <label>Username *</label>
        <input type="text" name="username" placeholder="Enter your Username" required>

        <label>Password *</label>
        <input type="password" name="password" placeholder="Enter your Password" required>

        <button type="submit">LOGIN</button>
    </form>
</div>

</body>
</html>