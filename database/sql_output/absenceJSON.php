<?php
include_once __DIR__ . '/../../app/app.php';
$pdo = App::getPDO();

$query = 'SELECT `doc_json`, `doc_md5`, `doc_time_version` FROM `absence`';
$statement = $pdo->prepare($query);
$statement->execute();
$response = $statement->fetch();
header('absence: application/json');
echo json_encode($response);