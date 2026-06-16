<?php
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../config/database.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$sale = null;
if($id > 0){
    $sale = $conn->query("SELECT sales.*, customers.name AS customer_name FROM sales LEFT JOIN customers ON customers.id = sales.customer_id WHERE sales.id=$id")->fetch_assoc();
}
if(!$sale){
    echo 'Sale not found.';
    exit;
}

$autoload = __DIR__ . '/../../vendor/autoload.php';
if(!file_exists($autoload)){
    echo 'Dompdf is not installed. Run composer require dompdf/dompdf';
    exit;
}
require_once $autoload;

use Dompdf\Dompdf;

ob_start();
$_GET['id'] = $id;
require __DIR__ . '/invoice.php';
$html = ob_get_clean();

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream('invoice_' . $sale['invoice_no'] . '.pdf', ["Attachment" => true]);
exit;
