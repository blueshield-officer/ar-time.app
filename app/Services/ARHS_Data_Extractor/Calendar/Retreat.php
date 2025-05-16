<?php

namespace App\Services\ARHS_Data_Extractor\Calendar;

class Retreat
{
    private $date;
    private $grade;

    public function __construct($date, $grade)
    {
        $this->date = $date;
        $this->grade = $grade;
    }

    public function getDate(){
        return $this->date;
    }

    public function getInfo(){
        return [
            'date' => $this->date,
            'grade' => $this->grade
        ];
    }
}