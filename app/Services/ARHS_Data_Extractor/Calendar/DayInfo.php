<?php

namespace App\Services\ARHS_Data_Extractor\Calendar;

class DayInfo {
    private $date;
    private $schedule = null;
    private $description = null;
    private $testDay = null;
    private $school = 0;
    private $keep_update = true;

    function __construct($date){
        $this->date = $date;
    }

    private function scheduleToNumber($schedule){
        if (strlen($schedule) === 1){
            $number = match (strtolower($schedule)) {
                'a' => 1,
                'b' => 2,
                'c' => 3,
                'd' => 4,
                'e' => 5,
                'f' => 6,
                'g' => 7,
                'h' => 8,
                'p' => 9,
                'r' => 10,
                't' => 11,
                'v' => 12
            };
            $this->setDescription($number);
            return (string)$number;
        }
        return $schedule;
    }
    private function setDescription($schedule)
    {
        $html = file_get_html($_SERVER['DOCUMENT_ROOT'] . '/data_txt/bell_schedules.txt');
        $this->description = $html->find('.bell-schedule .bell-schedule-description', $schedule - 1)->plaintext;
    }

    function setSchedule($schedule)
    {
        $this->schedule = self::scheduleToNumber($schedule);
    }
    function setTestDay($testDay)
    {
        $this->testDay = $testDay;
    }
    function setSchool()
    {
        $this->school = 1;
    }
    function setNoUpdate(){
        $this->keep_update = false;
    }
    function keep_update(){
        return $this->keep_update;
    }

    function getInfo()
    {
        return [
            'date' => $this->date,
            'schedule' => $this->schedule,
            'description' => $this->description,
            'testDay' => $this->testDay,
            'school' => $this->school
        ];
    }
    function noData()
    {
        $this->schedule = null;
        $this->description = null;
        $this->testDay = null;
        $this->school = 0;
    }
    function getDate()
    {
        return $this->date;
    }
}