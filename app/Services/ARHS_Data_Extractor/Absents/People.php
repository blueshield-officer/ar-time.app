<?php

namespace App\Services\ARHS_Data_Extractor\Absents;

class People
{
    private $peopleObjects;
    private $newPeopleForDB;

    public function __construct()
    {
        $this->peopleObjects = [];
        $this->newPeopleForDB = [];
    }
    public function add($period, $object, $method){
        $this->peopleObjects[$method][$period][$object['name']] = [
            'name' => $object['name'],
            'type' => $object['type'],
            'status' => 0,
        ];
        if (!isset($this->newPeopleForDB['name'])){
            $lastNameTemp = explode('.', $object['name']);
            $lastName = null;
            if (count($lastNameTemp) > 1) {
                $lastName = trim($lastNameTemp[1]);
            }
            $this->newPeopleForDB['name'] = [
                'last_name' => $lastName,
                'short_name' => $object['name'],
                'email' => preg_replace('/[^a-z]/', '', strtolower($object['name'])) . '@arhs.org',
                'position' => $object['type'],
            ];
        }
    }
    public function update_locations($DBC){
        foreach($this->peopleObjects as $method => $periods){
            foreach($periods as $period_index => $period){
                foreach($period as $object){
                    $name = $object['name'];
                    $this->peopleObjects[$method][$period_index][$name]['location'] = $this->find_location($DBC, $period, $name);
                }
            }
        }
    }
    private function find_location($DBC, $period, $name)
    {
        if (isset($room_changes[$period])){
            foreach($room_changes[$period] as $direction){
                // TODO переместить это чтобы было автономно, когда норм дб будет
                $person_respective = null;
                $result = $DBC->fetch($DBC->query('SELECT `honorific`, `last_name` FROM `school_faculty_list` WHERE `short_name` = ?', [$name]));
                if ($result !== false) {
                    $person_respective = [$result['honorific'] . '. ' . $result['last_name']];
                }
                if ($person_respective === null) {
                    $name_array = explode('.', $name);
                    if (isset($name_array[1])) {
                        $last_name = $name_array[1];
                        $person_respective = [
                            'Ms.' . $last_name,
                            'Mr.' . $last_name
                        ];
                        if (in_array($direction['name'], $person_respective)) {
                            return $direction['location'];
                        }
                    }
                }
            }
        }
        return null;
    }
    public function getInfo(){
        return $this->peopleObjects;
    }
    public function peopleToRecordAtDB(){
        return $this->newPeopleForDB;
    }
}