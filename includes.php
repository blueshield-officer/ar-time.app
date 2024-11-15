<?php
// session
session_start();
session_regenerate_id(true);
//var_dump($_SESSION['bypass_code']);
// date
date_default_timezone_set('America/New_York');

// errors
error_reporting(E_ERROR | E_WARNING);
ini_set('display_errors', 'On');


// 404 check
function shutdown_404()
{
    $sapi_name = php_sapi_name();

    if ($sapi_name == 'cgi' || $sapi_name == 'cgi-fcgi') {
        header('Status: 404 Not Found');
    } else {
        header($_SERVER['SERVER_PROTOCOL'] . ' 404 Not Found');
    }
    echo '404<br>';
    echo 'Not Found';
    exit();
}

$myURIs = ['/', '/privacy-policy/', '/terms-of-service/', '/about/', '/admin/'];
while (true){
    if (in_array($_SERVER["REQUEST_URI"], $myURIs, true)) {
        break;
    }
    else if (str_contains($_SERVER["REQUEST_URI"], '/about/?fbclid=')) {
        header('https://ar-time.org/about/');
        break;
    }
    else if (str_contains($_SERVER["REQUEST_URI"], '/?fbclid=')) {
        header('https://ar-time.org/');
        break;
    }
    shutdown_404();
}




// Get PDO
include __DIR__ . '/app/app.php';
$pdo = App::getPDO();



// Smarty API
require __DIR__ . '/libs/smarty/libs/Smarty.class.php';
$smarty = new Smarty();
$smarty->setTemplateDir(__DIR__ . '/templates/');
$smarty->setCompileDir(__DIR__ . '/cache/templates_c/');
$smarty->setConfigDir(__DIR__ . '/cache/configs/');
$smarty->setCacheDir(__DIR__ . '/cache/cache/');