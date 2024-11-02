<?php
include __DIR__ . '/autoloads.php';




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