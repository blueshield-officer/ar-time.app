<?php

namespace App\Services\ARHS_Data_Extractor\Calendar;


class DateList_HTML
{
    private $dateList;

    function __construct()
    {
        $this->dateList = [];
    }

    function add($year, $month, $day, $htmlElement)
    {
        $this->dateList[$year . '-' . $month . '-' . $day] = $htmlElement;
    }
    function getList()
    {
        return $this->dateList;
    }
}