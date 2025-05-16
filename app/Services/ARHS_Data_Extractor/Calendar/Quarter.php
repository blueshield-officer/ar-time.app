<?php

namespace App\Services\ARHS_Data_Extractor\Calendar;

class Quarter {
    private $date;
    private $name;
    private $status;

    function __construct($date, $number, $status){
        $this->date = $date;
        $this->name = 'Quarter ' . $number;
        $this->status = $status;
    }
    function getInfo()
    {
        return [
            'date' => $this->date,
            'name' => $this->name,
            'status' => $this->status
        ];
    }
    function getDate()
    {
        return $this->date;
    }
}