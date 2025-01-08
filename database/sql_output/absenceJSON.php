<?php
// OLD
use App\Services\DBConnection;

date_default_timezone_set('America/New_York');
include_once __DIR__ . '/../../app/app.php';
$pdo = DBConnection::getPDO();
$query = 'SELECT `doc_json`, `doc_md5`, `doc_time_version` FROM `absence`';
$statement = $pdo->prepare($query);
$statement->execute();
$response = $statement->fetch();
header('absence: application/json');
echo json_encode($response);


//session_start();
//if (session_id() === '') {
//    exit();
//}
//
//class AbsentTimeCheck {
//    private $pdo;
//    private $session_id;
//    private $current_unix;
//    public $last_unix;
//
//    public function __construct()
//    {
//        $this->pdo = App::getPDO();
//        $this->session_id = session_id();
//        $this->current_unix = time();
//    }
//    public function lastUNIX()
//    {
//        var_dump($this->session_id);
//        $query = 'SELECT `absence_date` FROM `sessions` WHERE `absence_id` = "' . $this->session_id . '" ORDER BY `absence_date` DESC';
//        $statement = $this->pdo->prepare($query);
//        $statement->execute();
//        $result = $statement->fetch();
//        if (!$result){
//            $result = null;
//        }
//        else {
//            $this->last_unix = $result["absence_date"];
//        }
//    }
//    public function checkInterval()
//    {
//        if ($this->current_unix - (int)$this->last_unix) {
//            return true;
//        }
//        else {
//            return false;
//        }
//    }
//}
//
//$myClass = new AbsentTimeCheck();
//$myClass->lastUNIX();




//$query = 'SELECT `doc_json`, `doc_md5`, `doc_time_version` FROM `absence`';
//$statement = $pdo->prepare($query);
//$statement->execute();
//$response = $statement->fetch();
//header('absence: application/json');
//echo json_encode($response);









