<?php
class IPlogs {
    private $ip;
    private $date;
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }
    public function dataUpdate(){
        $this->ip = $_SERVER['REMOTE_ADDR'];
        $this->date = date("Y-m-d H:i:s");
    }
    public function uploadToSQL(){
        $query = 'INSERT INTO `ip_logs` (ip, date) VALUES (?, ?)';
        $statement = $this->pdo->prepare($query);
        $statement->execute([$this->ip, $this->date]);
    }
}