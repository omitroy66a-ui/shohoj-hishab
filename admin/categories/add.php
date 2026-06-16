<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';

if(isset($_POST['save'])){
    $name = $conn->real_escape_string(trim($_POST['name']));

    if($name !== ''){
        $stmt = $conn->prepare("INSERT INTO categories(name) VALUES(?)");
        if ($stmt) {
            $stmt->bind_param('s', $name);
            $stmt->execute();
            $stmt->close();
        }
        header('Location: list.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Add Category - Sohoj Hishab</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f9f9f9; padding: 30px; }
        .container { max-width: 500px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 4px; box-shadow: 0 0 8px rgba(0,0,0,0.08); }
        label { display:block; margin-bottom:8px; color:#333; }
        input[type=text] { width:100%; padding:10px; border:1px solid #ccc; border-radius:4px; }
        button { padding:10px 16px; background:#007bff; border:none; color:#fff; border-radius:4px; cursor:pointer; }
        a { color:#007bff; text-decoration:none; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Add Category</h1>
        <form method="post" action="add.php">
            <label for="name">Category Name</label>
            <input type="text" id="name" name="name" required>
            <br><br>
            <button type="submit" name="save">Save Category</button>
        </form>
        <p><a href="list.php">Back to Category List</a></p>
    </div>
</body>
</html>
