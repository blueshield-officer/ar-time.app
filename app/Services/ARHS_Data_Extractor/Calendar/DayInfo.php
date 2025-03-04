<?php

namespace App\Services\ARHS_Data_Extractor\Calendar;

class DayInfo {
    private $date;
    private $schedule = null;
    private $description = null;
    private $testDay = null;

    function __construct($date, $schedule, $description, $testDay){
        $this->date = $date;
        $this->schedule = $schedule;
        $this->description = $description;
        $this->testDay = $testDay;

    }
    function getInfo()
    {
        return [
            'date' => $this->date,
            'schedule' => $this->schedule,
            'description' => $this->description,
            'testDay' => $this->testDay,
        ];
    }
    function getDate()
    {
        return $this->date;
    }
}