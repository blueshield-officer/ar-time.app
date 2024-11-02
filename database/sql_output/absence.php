<?php

class AbsenceLoad
{
    private $pdo;
    private $result;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function fetchSQL()
    {
        $query = 'SELECT `doc_json`, `doc_md5`, `doc_time_version` FROM `absence`';
        $statement = $this->pdo->prepare($query);
        $statement->execute();
        $result = $statement->fetch();
        $this->result = [
            'absence_JSON' => $result["doc_json"],
            'absence_MD5' => $result["doc_md5"],
            'version_time' => $result["doc_time_version"]
        ];
    }

    public function return(){
        return $this->result;
    }
}