<?php
class Partners {
    private $pdo;
    private $partners;
    private $sql;

    function __construct($pdo) {
        $this->pdo = $pdo;
        $this->partners = [];
    }
    public function fetchSQL()
    {
        $query = 'SELECT * FROM `partners` WHERE `status` = 1 ORDER BY `rank` ASC';
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $this->sql = $stmt->fetchAll();
        if ($this->sql !== false) {
            return true;
        }
        else {
            return false;
        }
    }

    public function formatData(){
        foreach ($this->sql as $element) {
            $this->partners[] = [
                'account' => $element['account'],
                'link' => $element['link'],
                'rank' => $element['rank']
            ];
        }
    }

    public function getPartners(){
        return $this->partners;
    }
}