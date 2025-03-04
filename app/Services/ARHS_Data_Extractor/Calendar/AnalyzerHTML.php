<?php

namespace App\Services\ARHS_Data_Extractor\Calendar;


use App\Services\PageDownloader;

class AnalyzerHTML
{
    private $pageDownloader;
    private $date;
    private $availableDays;
    private $path;
    private $id;

    function __construct($date, $type, $pageDownloader)
    {
        $this->calendarArray = [];
        $this->date = clone $date;
        $this->availableDays = 0;
        if ($type === 'schedule') {
            $this->path = 'schedule_calendar';
            $this->id = 0;
        } else {
            $this->path = 'sport_calendar';
            $this->id = 1;
        }
        $this->pageDownloader = $pageDownloader;
    }

    function loadCalendarHTML($monthAmount)
    {
        for ($i = 0; $i < $monthAmount; $i++) {
            $this->countAvailableDays($i);
            $month = $this->date->format('m');
            $year = $this->date->format('Y');
            $this->calendarHTML($month, $year);
            $this->nextMonth();
        }
    }

    private function nextMonth()
    {
        $this->date = $this->date->modify('first day of +1 month');
    }

    private function calendarHTML($month, $year)
    {
        $this->pageDownloader->downloadPage(
            'https://www.archbishopryan.com/apps/events/' . $year . '/' . $month . '/print/?id=' . $this->id,
            'data_txt/' . $this->path . '/',
            $month . '_' . $year,
            'txt'
        );
    }
    private function countAvailableDays($i)
    {
        $days = (int)$this->date->format('t');
        if ($i === 0) {
            $days = $days - ($this->date->format('j') - 1);
        }
        $this->availableDays += $days;
    }
    function getMonthHTML($year, $month)
    {
        return  file_get_html($_SERVER['DOCUMENT_ROOT'] . '/data_txt/' . $this->path . '/' . $month . '_' . $year . '.txt');
    }
    public function skipMonth($date){
        $this->availableDays -= $date->format('t');
        $date->modify('first day of +1 month');
    }
    function getAvailableDays()
    {
        return $this->availableDays;
    }
}