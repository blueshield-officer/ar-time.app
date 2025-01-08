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
    private $time;
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
        $this->description = $description;
        $this->time = $time;
        $this->location = $location;
        $this->special = $special;
        $this->md5key = $md5key;
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
            'time' => $this->time,
            'location' => $this->location,
            'special' => $this->special,
            'md5_key' => $this->md5key
        ];
    }
}