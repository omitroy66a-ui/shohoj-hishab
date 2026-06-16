<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../modules/business/middleware.php';

$business_id = businessId();

if (!$business_id) {
    http_response_code(401);
    exit('Unauthorized');
}

$id = (int) ($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT * FROM employees WHERE id = ? AND business_id = ?");
$stmt->bind_param("ii", $id, $business_id);
$stmt->execute();
$result = $stmt->get_result();
$employee = $result->fetch_assoc();

if (!$employee) {
    echo 'Employee not found.';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $salary = (float) ($_POST['salary'] ?? 0);

    $stmt = $conn->prepare("UPDATE employees SET name = ?, phone = ?, salary = ? WHERE id = ?");
    $stmt->bind_param("ssdi", $name, $phone, $salary, $id);
    $stmt->execute();
    
    header('Location: list.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Edit Employee</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; background: #f4f4f4; }
        .container { max-width: 700px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 8px; }
        label { display: block; margin: 12px 0 5px 0; }
        input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; margin-bottom: 15px; box-sizing: border-box; }
        button { padding: 12px 18px; background: #007bff; color: #fff; border: none; border-radius: 4px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Edit Employee</h1>
        <form method="POST">
            <label>Name</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($employee['name']); ?>" required>

            <label>Phone</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($employee['phone']); ?>">

            <label>Salary</label>
            <input type="number" step="0.01" name="salary" value="<?php echo $employee['salary']; ?>">

            <button type="submit">Update Employee</button>
        </form>
        <p><a href="list.php">Back to Employees</a></p>
    </div>
</body>
</html>
