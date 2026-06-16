<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';

$result = $conn->query("SELECT products.*, categories.name AS category FROM products LEFT JOIN categories ON categories.id = products.category_id ORDER BY products.id DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Products - Sohoj Hishab</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f9f9f9; padding: 30px; }
        .container { max-width: 1000px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0,0,0,0.08); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #f4f4f4; }
        .actions a { margin-right: 8px; text-decoration: none; color: #fff; padding: 6px 10px; border-radius: 3px; }
        .btn-primary { background: #007bff; }
        .btn-warning { background: #ffc107; color: #212529; }
        .btn-danger { background: #dc3545; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Products</h1>
            <a href="add.php" class="btn-primary" style="color:#fff;">Add Product</a>
        </div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Category</th>
                    <th>Name</th>
                    <th>Barcode</th>
                    <th>Purchase</th>
                    <th>Sale</th>
                    <th>Stock</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($product = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($product['id']); ?></td>
                    <td><?php echo htmlspecialchars($product['category'] ?? 'Uncategorized'); ?></td>
                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                    <td><?php echo htmlspecialchars($product['barcode']); ?></td>
                    <td><?php echo htmlspecialchars($product['purchase_price']); ?></td>
                    <td><?php echo htmlspecialchars($product['sale_price']); ?></td>
                    <td><?php echo htmlspecialchars($product['stock']); ?></td>
                    <td class="actions">
                        <a href="edit.php?id=<?php echo $product['id']; ?>" class="btn-warning">Edit</a>
                        <a href="delete.php?id=<?php echo $product['id']; ?>" class="btn-danger" onclick="return confirm('Delete this product?');">Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php if($result->num_rows === 0): ?>
                <tr>
                    <td colspan="8">No products found.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
