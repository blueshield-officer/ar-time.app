<?php
class BypassCode
{
    private $pdo;
    private $result;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
        $this->result = [];
    }

    public function fetchSQL()
    {
        $query = 'SELECT `code` FROM `bypass_code`';
        $statement = $this->pdo->prepare($query);
        $statement->execute();
        $result = $statement->fetchAll();
        foreach ($result as $element) {
            $this->result[] = $element['code'];
        }

    }

    public function return(){
        return $this->result;
    }
}