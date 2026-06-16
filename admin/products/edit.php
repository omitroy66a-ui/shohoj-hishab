<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$product = null;
$categories = $conn->query("SELECT * FROM categories ORDER BY name ASC");

if($id > 0){
    $product = $conn->query("SELECT * FROM products WHERE id=$id")->fetch_assoc();
}

if(!$product){
    header('Location: list.php');
    exit;
}

if(isset($_POST['update'])){
    $category_id = (int) $_POST['category_id'];
    $name = $conn->real_escape_string(trim($_POST['name']));
    $barcode = $conn->real_escape_string(trim($_POST['barcode']));
    $purchase_price = $conn->real_escape_string(trim($_POST['purchase_price']));
    $sale_price = $conn->real_escape_string(trim($_POST['sale_price']));
    $stock = (int) $_POST['stock'];

    $conn->query("UPDATE products SET category_id=$category_id, name='$name', barcode='$barcode', purchase_price='$purchase_price', sale_price='$sale_price', stock=$stock WHERE id=$id");

    header('Location: list.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Edit Product - Sohoj Hishab</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f9f9f9; padding: 30px; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 4px; box-shadow: 0 0 8px rgba(0,0,0,0.08); }
        label { display:block; margin-bottom:8px; color:#333; }
        input, select { width:100%; padding:10px; border:1px solid #ccc; border-radius:4px; }
        button { padding:10px 16px; background:#28a745; border:none; color:#fff; border-radius:4px; cursor:pointer; }
        a { color:#007bff; text-decoration:none; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Edit Product</h1>
        <form method="post" action="edit.php?id=<?php echo $id; ?>">
            <label for="category_id">Category</label>
            <select id="category_id" name="category_id" required>
                <?php while($category = $categories->fetch_assoc()): ?>
                    <option value="<?php echo $category['id']; ?>" <?php echo $category['id'] == $product['category_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($category['name']); ?></option>
                <?php endwhile; ?>
            </select>

            <label for="name">Name</label>
            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>

            <label for="barcode">Barcode</label>
            <input type="text" id="barcode" name="barcode" value="<?php echo htmlspecialchars($product['barcode']); ?>">

            <label for="purchase_price">Purchase Price</label>
            <input type="text" id="purchase_price" name="purchase_price" value="<?php echo htmlspecialchars($product['purchase_price']); ?>" required>

            <label for="sale_price">Sale Price</label>
            <input type="text" id="sale_price" name="sale_price" value="<?php echo htmlspecialchars($product['sale_price']); ?>" required>

            <label for="stock">Stock</label>
            <input type="number" id="stock" name="stock" value="<?php echo htmlspecialchars($product['stock']); ?>" min="0" required>

            <br><br>
            <button type="submit" name="update">Update Product</button>
        </form>
        <p><a href="list.php">Back to Product List</a></p>
    </div>
</body>
</html>
