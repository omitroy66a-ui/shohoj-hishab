<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';

$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC");

if(isset($_POST['save'])){
    $category_id = (int) $_POST['category_id'];
    $name = $conn->real_escape_string(trim($_POST['name']));
    $barcode = $conn->real_escape_string(trim($_POST['barcode']));
    $purchase_price = $conn->real_escape_string(trim($_POST['purchase_price']));
    $sale_price = $conn->real_escape_string(trim($_POST['sale_price']));
    $stock = (int) $_POST['stock'];

    $stmt = $conn->prepare("INSERT INTO products(category_id, name, barcode, purchase_price, sale_price, stock) VALUES(?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        $purchase_price = (float) $purchase_price;
        $sale_price = (float) $sale_price;
        $stmt->bind_param('issddi', $category_id, $name, $barcode, $purchase_price, $sale_price, $stock);
        $stmt->execute();
        $stmt->close();
    }
    header('Location: list.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Add Product - Sohoj Hishab</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f9f9f9; padding: 30px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 4px; box-shadow: 0 0 8px rgba(0,0,0,0.08); }
        label { display:block; margin-bottom:8px; color:#333; }
        input, select { width:100%; padding:10px; border:1px solid #ccc; border-radius:4px; }
        button { padding:10px 16px; background:#007bff; border:none; color:#fff; border-radius:4px; cursor:pointer; }
        a { color:#007bff; text-decoration:none; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Add Product</h1>
        <form method="post" action="add.php">
            <label for="category_id">Category</label>
            <select id="category_id" name="category_id" required>
                <option value="">Select Category</option>
                <?php while($category = $categories->fetch_assoc()): ?>
                    <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                <?php endwhile; ?>
            </select>

            <label for="name">Name</label>
            <input type="text" id="name" name="name" required>

            <label for="barcode">Barcode</label>
            <input type="text" id="barcode" name="barcode">

            <label for="purchase_price">Purchase Price</label>
            <input type="text" id="purchase_price" name="purchase_price" required>

            <label for="sale_price">Sale Price</label>
            <input type="text" id="sale_price" name="sale_price" required>

            <label for="stock">Stock</label>
            <input type="number" id="stock" name="stock" value="0" min="0" required>

            <br><br>
            <button type="submit" name="save">Save Product</button>
        </form>
        <p><a href="list.php">Back to Product List</a></p>
    </div>
</body>
</html>
