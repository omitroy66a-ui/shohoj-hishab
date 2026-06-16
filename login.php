<?php
require_once __DIR__ . '/config/database.php';
session_start();

if(isset($_SESSION['user_id'])){
    header('Location: admin/dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sohoj Hishab</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-container { background: white; padding: 30px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h1 { text-align: center; color: #333; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #555; font-weight: bold; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 3px; box-sizing: border-box; }
        input:focus { outline: none; border-color: #007bff; }
        button { width: 100%; padding: 10px; background: #007bff; color: white; border: none; border-radius: 3px; cursor: pointer; font-weight: bold; }
        button:hover { background: #0056b3; }
        .error { color: #dc3545; text-align: center; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Sohoj Hishab</h1>
        <h2 style="text-align:center; color:#666;">Login</h2>
        
        <?php
            if (isset($_GET['error'])) {
                echo '<p class="error">Invalid Email or Password</p>';
            } elseif (isset($_GET['registered']) && $_GET['registered'] == '1') {
                echo '<p class="error" style="color: #28a745;">Registration successful. Please log in.</p>';
            }
        ?>
        
        <form method="post" action="login_process.php">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>
