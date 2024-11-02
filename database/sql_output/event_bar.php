<?php
class EventBar{
    private $message;
    private $pdo;
    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function fetchSQL(){
        $query = 'SELECT `text`, `color`, `bg_color` FROM `messages` WHERE id = 1 AND `status` = 1;';
        $statement = $this->pdo->prepare($query);
        $statement->execute();
        $result = $statement->fetch();
        if ($result !== false) {
            $this->message = json_encode([
                'text' => $result['text'],
                'color' => $result['color'],
                'bg_color' => $result['bg_color']
            ]);
        }
        else {
            $this->message = '';
        }
    }

    public function return(){
        return $this->message;
    }
}