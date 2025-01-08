<?php

namespace App\Services\ARHS_Data_Extractor\Absents;

class Absences
{
    private $pdo;
    private $date;
    private $people = null;
    private $roomChanges = null;

    public function __construct($pdo, $date)
    {
        $this->pdo = $pdo;
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
}