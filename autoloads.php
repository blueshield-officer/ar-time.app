<?php
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

$myURIs = ['/', '/log-in/', '/register/'];
while (true){
    if (in_array($_SERVER["REQUEST_URI"], $myURIs, true)) {
        break;
    }
    shutdown_404();
}






// Get PDO
include __DIR__ . '/app/app.php';
$pdo = App::getPDO();


// IP data
require __DIR__ . '/database/sql_update/ip-logs.php';
$ipLogs = new IPlogs($pdo);
$ipLogs->dataUpdate();
$ipLogs->uploadToSQL();


// Smarty API
require __DIR__ . '/libs/smarty/libs/Smarty.class.php';
$smarty = new Smarty();
$smarty->setTemplateDir(__DIR__ . '/templates/');
$smarty->setCompileDir(__DIR__ . '/cache/templates_c/');
$smarty->setConfigDir(__DIR__ . '/cache/configs/');
$smarty->setCacheDir(__DIR__ . '/cache/cache/');



// status checker
include __DIR__ . '/database/sql_output/website-status.php';
$status = new WebsiteStatus($pdo);
$result = $status->fetchSQL();
if ($result){
$status_json = json_encode($status->return());
$smarty->assign('status_json', $status_json);
$smarty->display('website-status.tpl');
exit();
}