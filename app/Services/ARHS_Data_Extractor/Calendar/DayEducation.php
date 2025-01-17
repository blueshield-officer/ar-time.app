<?php

namespace App\Services\ARHS_Data_Extractor\Calendar;

class DayEducation
{
    private $date;
    private $quarter;
    private $status;

    public function __construct($date, $quarter, $status)
    {
        $this->date = $date;
        $this->quarter = $quarter;
        $this->status = $status;
    }
    public function getDate(){
        return $this->date;
    }
    public function getInfo(){
        return [
            'date' => $this->date,
            'quarter' => $this->quarter,
            'status' => $this->status
        ];
    }
}