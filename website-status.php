<?php
function arrayMaker($title, $description, $color, $bgColor, $image)
{
  return [
    'title' => $title,
    'description' => $description,
    'color' => $color,
    'bgColor' => 'rgba(' . $bgColor . ', 0.90)',
    'borderColor' => 'rgb(' . $bgColor . ')',
    'image' => '../img/svg/' . $image . '.svg'
  ];
}

$status = match ($status["current_status"]) {
  507 => arrayMaker('Website Is Closed', 'someText', 'white', '154, 33, 35','lock-fill'),
  500 => arrayMaker('Website Is Not Public Yet', 'Our website is not open yet', 'white', '#54, 33, 35', 'clock'),
  default => arrayMaker('Suspicious Things Going On', 'someText', 'white', '174, 95, 121', 'search'),
};
$smarty->assign('status_JSON', json_encode($status));
$smarty->display('website-status.tpl');
?>