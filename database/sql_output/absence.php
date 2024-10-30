<?php
class AbsenceLoad
{
  private $pdo;
  public function __construct($pdo)
  {
    $this->pdo = $pdo;
  }
  public function analysis(){
    $query = 'SELECT `doc_json`, `doc_md5`, `doc_time_version` FROM `absence`';
    $statement = $this->pdo->prepare($query);
    $statement->execute();
    $result = $statement->fetch();
    return [
      'absence_JSON' => $result["doc_json"],
      'absence_MD5' => $result["doc_md5"],
      'version_time' => $result["doc_time_version"]
    ];
  }
}

