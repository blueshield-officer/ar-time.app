<?php
class News {
    private $pdo;
    private $news;
    private $result;

    function __construct($pdo) {
        $this->pdo = $pdo;
        $this->news = [];
    }
    public function fetchSQL()
    {
        $query = 'SELECT * FROM `news` WHERE `status` = 1 ORDER BY `date` DESC';
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        $this->result = $stmt->fetchAll();
        if ($this->result !== false) {
            return true;
        }
        else {
            return false;
        }
    }

    public function formatData(){
        foreach ($this->result as $element) {
            $this->news[] = [
                'title' => $element['title'],
                'content' => $element['content'],
                'date' => $element['date'],
                'image' => $element['image'],
            ];
        }
    }

    public function getNews(){
        return $this->news;
    }
}