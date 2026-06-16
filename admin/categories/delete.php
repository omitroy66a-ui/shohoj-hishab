<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if($id > 0){
    $conn->query("DELETE FROM categories WHERE id=$id");
}

header('Location: list.php');
exit;
