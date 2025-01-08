<?php

namespace App\Services\Data_Extraction\SQL\Schedule_Rarity;

class ScheduleInfo
{
    private $schedule;
    private $quantity;
    private $rarity_percentage;
    private $rarity;

    function __construct($schedule){
        if(($schedule >= 0 && $schedule <= 12) === false){
            $schedule = 0;
        }
        $this->schedule = $this->getScheduleArray()[$schedule];
        $this->quantity = 0;
        $this->rarity_percentage = 0;
        $this->rarity = null;
    }
    public function add_quantity($quantity){
        $this->quantity += $quantity;
    }
    public function get_quantity(){
        return $this->quantity;
    }
    public function set_percentage($percents){
        $this->rarity_percentage = $percents;
    }
    public function getInfo(){
        return [
            'schedule' => $this->schedule,
            'quantity' => $this->quantity,
            'rarity_percentage' => $this->rarity_percentage,
            'rarity' => $this->rarity
        ];
    }

    private function getScheduleArray(){
        return [
            0 => 'special',
            1 => 'a',
            2 => 'b',
            3 => 'c',
            4 => 'd',
            5 => 'e',
            6 => 'f',
            7 => 'g',
            8 => 'h',
            9 => 'p',
            10 => 'r',
            11 => 't',
            12 => 'v'
        ];
    }
}