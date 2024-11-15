<?php
require_once __DIR__ . '/includes.php';

// status checker
require __DIR__ . '/database/sql_output/bypass_code.php';
$bypass_code = new BypassCode($pdo);
$bypass_code->fetchSQL();
$code = $bypass_code->return();
if (!isset($_SESSION['bypass_code']) || !in_array($_SESSION['bypass_code'], $code)) {
    require __DIR__ . '/database/sql_output/website-status.php';
    $status = new WebsiteStatus($pdo);
    $result = $status->fetchSQL();
    if ($result){
        $status_json = json_encode($status->return());
        $smarty->assign('status_json', $status_json);
        $smarty->display('other/website-status.tpl');
        exit();
    }
}


// SQL data
// day info
require __DIR__ . '/database/sql_output/day-info.php';
$day_info = new DataLoad($pdo);
$day_info = $day_info->analysis();
$smarty->assign('schedule_JSON', json_encode($day_info));
// bell table
$i = -1;
if (is_array($day_info['bells'])) {
    foreach ($day_info['bells'] as $element) {
        $i += 1;
        $day_info['bells'][$i]['name'] = ucwords($element['name']);
    }
}
$smarty->assign('table_data', $day_info['bells']);

// absence
require __DIR__ . '/database/sql_output/absence.php';
$absence = new AbsenceLoad($pdo);
$absence->fetchSQL();
$smarty->assign('absence', $absence->return());

// event bar
require __DIR__ . '/database/sql_output/event_bar.php';
$event_message = new EventBar($pdo);
$event_message->fetchSQL();
$smarty->assign('event_bar', $event_message->return());

// news
require __DIR__ . '/database/sql_output/news.php';
$news = new News($pdo);
$fetch = $news->fetchSQL();
if ($fetch){
    $news->formatData();
    $newsList = $news->getNews();
    if (count($newsList) > 0) {
        $smarty->assign('news', json_encode($newsList));
    }
    else {
        $smarty->assign('news', null);
    }
}
else {
    $smarty->assign('news', null);
}


// !!!!!!!!!!!!!!!!!!!!!!!! TEMP THING (OR NOT?) !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
// our partners
require __DIR__ . '/database/sql_output/partners.php';
$partners = new Partners($pdo);
$fetch = $partners->fetchSQL();
if ($fetch){
    $partners->formatData();
    $partnersList = $partners->getPartners();
    if (count($partnersList) > 0) {
        $smarty->assign('partners', json_encode($partnersList));
    }
    else {
        $smarty->assign('partners', null);
    }
}
else {
    $smarty->assign('partners', null);
}
// !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!


$smarty->assign('navbar', 'universal/navbar.tpl');



$smarty->display('main-page/index.tpl');
