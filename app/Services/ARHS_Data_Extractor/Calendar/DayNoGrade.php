<?php

namespace App\Services\ARHS_Data_Extractor\Calendar;

class DayNoGrade {
    private $date;
    private $grade;

    function __construct($date, $grade){
        $this->date = $date;
        $this->grade = $grade;
    }
    function getDate(){
        return $this->date;
    }
    function getInfo(){
        return [
            'date' => $this->date,
            'grade' => join(', ', $this->grade)
        ];
    }
}