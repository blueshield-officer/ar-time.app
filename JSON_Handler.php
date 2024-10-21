<?php
$qwery = 'SELECT schedule  FROM `calendar` WHERE DAY = '.date('d');




$str_json = file_get_contents('php://input');
$response = json_decode($str_json, true);
header('unix: application/json');
echo json_encode($response);
?>