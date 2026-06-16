<?php
function sendSMSReminder($phone, $message)
{
    // Placeholder for SMS provider integration
    // $url = "https://api.smsprovider.com/send?to=" . urlencode($phone) . "&msg=" . urlencode($message);
    // file_get_contents($url);
    return true;
}

function sendWhatsAppReminder($phone, $message)
{
    $link = "https://wa.me/88" . preg_replace('/[^0-9]/', '', $phone) . "?text=" . urlencode($message);
    return $link;
}
