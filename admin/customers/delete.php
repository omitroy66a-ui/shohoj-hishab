<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../modules/business/middleware.php';

$business_id = businessId();

if (!$business_id) {
    http_response_code(401);
    exit('Unauthorized');
}

$id = (int) ($_GET['id'] ?? 0);

if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM customers WHERE id = ? AND business_id = ?");
    $stmt->bind_param("ii", $id, $business_id);
    $stmt->execute();
}

header('Location: index.php');
exit;
