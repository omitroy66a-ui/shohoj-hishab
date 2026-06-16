<?php
require_once __DIR__ . '/../../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;

if (empty($email)) {
    echo 'Email address is required';
    exit;
}

$mail = new PHPMailer();
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'your@gmail.com';
$mail->Password = 'password';
$mail->SMTPSecure = 'tls';
$mail->Port = 587;

$mail->addAddress($email);
$mail->Subject = 'Invoice';
$mail->Body = 'Invoice Generated';

if ($mail->send()) {
    echo 'Email sent successfully';
} else {
    echo 'Email sending failed: ' . $mail->ErrorInfo;
}
