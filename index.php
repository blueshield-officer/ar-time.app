<?php

// 404 check
if ($_SERVER["REQUEST_URI"] !== '/') {
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




// SQL data
require __DIR__ . '/database/sql_output/day-info.php';
require __DIR__ . '/database/sql_output/absence.php';
require __DIR__ . '/database/sql_output/event_bar.php';




// Extracting data from SQL
$day_info = new DataLoad($pdo);
$day_info = $day_info->analysis();
$smarty->assign('schedule_JSON', json_encode($day_info));

$absence = new AbsenceLoad($pdo);
$absence->fetchSQL();
$smarty->assign('absence', $absence->return());

$event_message = new EventBar($pdo);
$event_message->fetchSQL();
$smarty->assign('event_bar', $event_message->return());



$i = -1;
if (is_array($day_info['bells'])) {
    foreach ($day_info['bells'] as $element) {
        $i += 1;
        $day_info['bells'][$i]['name'] = ucwords($element['name']);
    }
}
$smarty->assign('navbar', 'universal/navbar.tpl');

$smarty->assign('table_data', $day_info['bells']);

//session_start();
//$smarty->assign('session_id', session_id());
$smarty->display('main-page/index.tpl');
exit();