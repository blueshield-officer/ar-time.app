<?php

namespace App\Services\ARHS_Data_Extractor\Absents;

class Room_Changes
{
    private $room_changes;
    public function __construct(){
        $this->room_changes = [];
    }
    public function add($period, $object, $method){
        $this->room_changes[$method][$period][$object['name']] = [
            'name' => $object['name'],
            'location' => $object['location']
        ];
    }
    public function getInfo(){
        return $this->room_changes;
    }
}