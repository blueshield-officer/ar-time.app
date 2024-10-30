<?php
include __DIR__.'/app/app.php';
$pdo = App::getPDO();

require __DIR__.'/libs/smarty/libs/Smarty.class.php';
$smarty = new Smarty();
$smarty->setTemplateDir(__DIR__.'/templates/');
$smarty->setCompileDir(__DIR__.'/cache/templates_c/');
$smarty->setConfigDir(__DIR__.'/cache/configs/');
$smarty->setCacheDir(__DIR__.'/cache/cache/');

// status checker
$query = 'SELECT `current_status` FROM `website` WHERE `id` = 1';
$statement = $pdo->prepare($query);
$statement->execute();
$status = $statement->fetch();
if ($status !== false) {
  if ($status["current_status"] !== 0){
    include __DIR__ . '/website-status.php';
    exit();
  }
}

require __DIR__ . '/database/sql_output/day-info.php';
require __DIR__ . '/database/sql_output/absence.php';
require __DIR__ . '/database/sql_output/event_bar.php';

$day_info = new DataLoad($pdo);
$day_info = $day_info->analysis();
$smarty->assign('schedule_JSON', json_encode($day_info));

$absence = new AbsenceLoad($pdo);
$absence = $absence->analysis();
$smarty->assign('absence', $absence);

$i = -1;
if (is_array($day_info['bells'])){
  foreach ($day_info['bells'] as $element){
    $i += 1;
    $day_info['bells'][$i]['name'] = ucwords($element['name']);
  }
}
$smarty->assign('navbar','universal/navbar.tpl');

$smarty->assign('table_data', $day_info['bells']);
$smarty->display('main-page/index.tpl');
exit();