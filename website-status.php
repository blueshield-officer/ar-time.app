<?php
function arrayMaker($title, $description, $bgColor, $color)
{
  return [
    'title' => $title,
    'description' => $description,
    'bgColor' => $bgColor,
    'color' => $color
  ];
}

$status = match ($status) {
  '507' => arrayMaker('Website Is Closed', 'someText', '#9a2123', 'white'),
  '500' => arrayMaker('Website Is Not Public Yet', 'someText', '#9a2123', 'white'),
  default => arrayMaker('Suspicious Things Going On', 'someText', '#ae5f79', 'white'),
};
$smarty->assign('status_JSON', json_encode($status));
$smarty->display('website-status.tpl');
?>