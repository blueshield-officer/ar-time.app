<?php

namespace App\Services\ARHS_Data_Extractor\Absents;

class Absences
{
    private $DBC;
    private $date;
    private $people = null;
    private $roomChanges = null;
    private $additional = null;

    public function __construct($DBC, $date)
    {
        $this->pdo = $DBC;
        $this->date = $date;
    }
    function setPeople($array)
    {
        $this->people = $array;
    }
    function setRoomChanges($array)
    {
        $this->roomChanges = $array;
    }

    function setAdditional($str)
    {
        $this->additional = $str;
    }
    function getPeople()
    {
        if ($this->people === null){
            return null;
        }
        return $this->people;
    }

    function getRoomChanges()
    {
        if ($this->roomChanges === null){
            return null;
        }
        return $this->roomChanges;
    }
    function getAdditional(){
        return $this->additional;
    }
}