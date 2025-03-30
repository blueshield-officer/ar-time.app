<?php

namespace App\Services\ARHS_Data_Extractor\Calendar;

class DayInfo {
    private $date;
    private $schedule = null;
    private $testDay = null;

    function __construct($date, $schedule, $testDay){
        $this->date = $date;
        $this->schedule = $schedule;
        $this->testDay = $testDay;

    }
    function getInfo()
    {
        return [
            'date' => $this->date,
            'schedule' => $this->schedule,
            'testDay' => $this->testDay,
        ];
    }
    function getDate()
    {
        return $this->date;
    }
}