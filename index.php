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
$status = $statement->fetch()["current_status"];
if ($status !== 0){
    include __DIR__ . '/website-status.php';
    exit();
}

$smarty->assign('navbar','universal/navbar.tpl');
$smarty->assign('countdown','countdown-box.tpl');
require __DIR__.'/dataLoader.php';
$schedule = new DataLoad($pdo);
  $data = $schedule->analysis();
  $smarty->assign('schedule_JSON', json_encode($data));
  $i = -1;
  if (is_array($data['bells'])){
    foreach ($data['bells'] as $element){
      $i += 1;
      $data['bells'][$i]['name'] = ucwords($element['name']);
    }
  }
  $smarty->assign('table_data', $data['bells']);
$smarty->display('index.tpl');
exit();