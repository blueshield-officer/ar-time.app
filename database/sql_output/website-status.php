<?php
class WebsiteStatus
{
    private $pdo;
    private $code;
    private $title;
    private $description;
    private $image;
    private $color;
    private $bg_color;



    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function fetchSQL(){
        $query = 'SELECT * FROM `website_status` WHERE `status` = 1';
        $statement = $this->pdo->prepare($query);
        $statement->execute();
        $result = $statement->fetch();
        if ($result !== false){
            $this->code = $result["code"];
            $this->title = $result['title'];
            $this->description = $result['description'];
            $this->image = $result['image'];
            $this->color = $result['color'];
            $this->bg_color = $result['bg_color'];
            return true;
        }
        else {
            return false;
        }
    }
    public function return(){
        return [
            'code' => $this->code,
            'title' => $this->title,
            'description' => $this->description,
            'image' => $this->image,
            'color' => $this->color,
            'bg_color' => $this->bg_color
        ];
    }
}