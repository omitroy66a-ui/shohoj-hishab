<?php
require_once "../../config/database.php";
require_once "../../modules/business/middleware.php";

$business_id = businessId();
$id = (int)($_GET['id'] ?? 0);

$conn->query("DELETE FROM cashbook WHERE id='$id' AND business_id='$business_id'");

header("Location: index.php");
exit;
