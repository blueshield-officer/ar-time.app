<?php

namespace App\Services\ARHS_Data_Extractor\Calendar;

class DaySpecial
{
    private $date;
    private $title;
    private $description = null;
    private $timestampStart = null;
    private $timestampEnd = null;
    private $location = null;
    private $code;
    private $important = 0;

    function __construct($date, $title, $description, $code = null, $type = null){
        $this->date =  $date;
        $this->title = $title;
        $this->code = $code;
        $this->type = $type;
        if ($code !== null || $type !== null){
            $this->important = 1;
        }
        $this->processDescription($description);
    }
    private function processDescription($str)
    {
        if ($str === null){
            return;
        }
        $str = html_entity_decode($str);
        preg_match('/(?:(.*)(?:\s*–\s*)(.*?)(?:\s*)(?=\(|$))?(?:\((?:(.*)\)))?/', $str, $matches);
        $this->timestampStart = $this->analyzeDate($matches[1] ?? null);
        $this->timestampEnd = $this->analyzeDate($matches[2] ?? null);
        $this->location = $matches[3] ?? null;

        if (is_null($this->timestampStart) &&
            is_null($this->timestampEnd) &&
            is_null($this->location)
        ){
            $this->description = $str;
        }
    }
    private function analyzeDate($str){
        if (is_null($str)){
            return null;
        }
        return (
            new \DateTime((str_contains($str, '/') ? '' : $this->date . ' ' ) .  $str)
        )->format('Y-m-d H:i:s');
    }
    public function getDate()
    {
        return $this->date;
    }
    public function getInfo()
    {
        return [
            'date' => $this->date,
            'title' => $this->title,
            'description' => $this->description,
            'time_start' => $this->timestampStart,
            'time_end' => $this->timestampEnd,
            'location' => $this->location,
            'code' => $this->code,
            'type' => $this->type,
            'important' => $this->important
        ];
    }
}