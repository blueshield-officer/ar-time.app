<?php

namespace App\Services\ARHS_Data_Extractor\Calendar;

class DayOff {
    private $date;
    private $grades;
    private $percentage;
    private $type;
    private const GRADE_LEVELS = [9, 10, 11, 12];

    private function numberInArray($array, $number){
        if(in_array($number, $array)){
            return 1;
        }
        return 0;
    }

    public function __construct($date, $grades, $percentage, $type){
        if ($grades === 'all'){
            $this->grades = [9, 10, 11, 12];
        }
        else {
            $this->grades = $grades;
        }
        $this->date = $date;
        $this->percentage = (float)$percentage;
        $this->type = $type;
    }
    public function getDate(){
        return $this->date;
    }
    public function getInfo(){
        $gradeInfo = [];
        foreach(self::GRADE_LEVELS as $grade){
            $gradeInfo[$grade] = in_array($grade, $this->grades) ? 1 : 0;
        }

        return [
                'date' => $this->date,
                'percentage' => $this->percentage,
                'type' => $this->type
            ] +  $gradeInfo;
    }
}