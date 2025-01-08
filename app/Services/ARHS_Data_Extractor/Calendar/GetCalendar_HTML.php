<?php

namespace App\Services\ARHS_Data_Extractor\Calendar;


class GetCalendar_HTML
{
    private $date;
    private $availableDays;
    private $path;
    private $id;

    function __construct($date, $type)
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
        $url = 'https://www.archbishopryan.com/apps/events/' . $year . '/' . $month . '/print/?id=' . $this->id;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:17.0) Gecko/20100101 Firefox/17.0');
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        $data = curl_exec($ch);
        if (curl_error($ch)) {
            var_dump(curl_error($ch));
        }
        curl_close($ch);
        try {
            $f = fopen($_SERVER['DOCUMENT_ROOT'] . '/data_txt/' . $this->path . '/' . $month . '_' . $year . '.txt', "w");
            if ($f === false) {
                throw new \Exception('Error opening file');
            }
            fwrite($f, $data);
            fclose($f);
        } catch (\Exception $exception){

        }
        require_once __DIR__ . "/../../../../libs/simplehtmldom/simple_html_dom.php";
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