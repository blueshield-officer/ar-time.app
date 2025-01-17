<?php

namespace App\Services\ARHS_Data_Extractor\Calendar;

class DayNoGrade {
    private $date;
    private $grade;

    public function __construct($date, $grade){
        $this->date = $date;
        $this->grade = $grade;
    }
    public function numberInArray($array, $number){
        if(in_array($number, $array)){
            return 1;
        }
        return 0;
    }
    public function getDate(){
        return $this->date;
    }
    public function getInfo(){
        return [
            'date' => $this->date,
            '9' => $this->numberInArray($this->grade, '9'),
            '10' => $this->numberInArray($this->grade, '10'),
            '11' => $this->numberInArray($this->grade, '11'),
            '12' => $this->numberInArray($this->grade, '12'),
        ];
    }
}