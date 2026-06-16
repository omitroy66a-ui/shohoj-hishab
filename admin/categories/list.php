<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';

$result = $conn->query("SELECT * FROM categories ORDER BY id DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Categories - Sohoj Hishab</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f9f9f9; padding: 30px; }
        .container { max-width: 900px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.08); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #f4f4f4; }
        .actions a { margin-right: 8px; text-decoration: none; color: #fff; padding: 6px 10px; border-radius: 3px; }
        .btn-primary { background: #007bff; }
        .btn-danger { background: #dc3545; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Categories</h1>
            <a href="add.php" class="btn-primary" style="color:#fff;">Add Category</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($category = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($category['id']); ?></td>
                    <td><?php echo htmlspecialchars($category['name']); ?></td>
                    <td class="actions">
                        <a href="delete.php?id=<?php echo $category['id']; ?>" class="btn-danger" onclick="return confirm('Delete this category?');">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php if($result->num_rows === 0): ?>
                <tr>
                    <td colspan="3">No categories found.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
