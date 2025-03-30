<?php

namespace App\Services\ARHS_Data_Extractor\Calendar;

class DaySports {
    private $date;
    private $sport;
    private $gender;
    private $side;
    private $varsity;
    private $opponent;
    private $description;
    private $time_start = null;
    private $time_end = null;
    private $location;
    private $special;
    private $md5key;

    function __construct($date, $sport, $gender, $side, $varsity, $opponent, $description, $time, $location, $special, $md5key){
        $this->date = $date;
        $this->sport = self::sportUncode($sport);
        $this->gender = $gender;
        $this->side = $side;
        $this->varsity = $varsity;
        $this->opponent = $opponent;
        $this->description = $description;;
        $this->location = $location;
        $this->special = $special;
        $this->md5key = $md5key;

        $this->analyzeTime($time);
    }
    private function sportUncode($sport)
    {
        return match (strtolower($sport)){
            'bkb' => 'Basketball',
            'lax' => 'Lacrosse',
            'vb' => 'Volleyball',
            'bb' => 'Baseball',
            'wrs' => 'Wrestling',
            'sw' => 'Swimming',
            'ih' => 'Ice Hockey',
            'sb' => 'Soccer',
            default => ucwords($sport)
        };
    }
    private function analyzeTime($str){
        if (is_null($str)){
            return;
        }
        $str = html_entity_decode($str);
        $timeArray = explode('–', $str);
        if (empty($timeArray)){
            return;
        }
        if (count($timeArray) > 0){
            $this->time_start = (new \DateTime($timeArray[0]))->format('Y-m-d H:i:s');
        }
        if (count($timeArray) === 2){
            $this->time_end = (new \DateTime($timeArray[1]))->format('Y-m-d H:i:s');
        }
    }
    function getDate()
    {
        return $this->date;
    }
    function getInfo()
    {
        return [
            'date' => $this->date,
            'sport' => $this->sport,
            'gender' => $this->gender,
            'side' => $this->side,
            'varsity' => $this->varsity,
            'opponent' => $this->opponent,
            'description' => $this->description,
            'time_start' => $this->time_start,
            'time_end' => $this->time_end,
            'location' => $this->location,
            'special' => $this->special,
            'md5_key' => $this->md5key
        ];
    }
}