<?php
die();
include_once __DIR__ . '/../includes.php';
session_start();
var_dump(session_id());
$smarty->display('admin/admin.tpl');