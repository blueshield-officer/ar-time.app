<?php
include __DIR__ . '/../app/app.php';
$pdo = App::getPDO();
$query = 'SELECT * FROM `calendar` WHERE `date` = 20241105';
$statement = $pdo->prepare($query);
$statement->execute();
$result = $statement->fetch();
echo '<pre>';
var_dump($result);
echo '</pre>';