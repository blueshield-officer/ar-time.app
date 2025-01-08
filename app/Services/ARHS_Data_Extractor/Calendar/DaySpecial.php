<?php

namespace App\Services\ARHS_Data_Extractor\Calendar;

class DaySpecial
{
    private $date;
    private $title;
    private $description;
    private $code;
    private $important = 0;

    function __construct($date, $title, $description, $code = null){
        $this->date =  $date;
        $this->title = $title;
        $this->description = $description;
        $this->code = $code;
        if ($code !== null){
            $this->important = 1;
        }
    }
    function getDate()
    {
        return $this->date;
    }
    function getInfo()
    {
        return [
            'date' => $this->date,
            'title' => $this->title,
            'description' => $this->description,
            'code' => $this->code,
            'important' => $this->important
        ];
    }
}