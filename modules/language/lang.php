<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function lang($en, $bn)
{
    $current_lang = $_SESSION['lang'] ?? 'en';
    
    if (isset($_GET['lang']) && in_array($_GET['lang'], ['en', 'bn'])) {
        $current_lang = $_GET['lang'];
        $_SESSION['lang'] = $current_lang;
    }
    
    return $current_lang === 'bn' ? $bn : $en;
}
