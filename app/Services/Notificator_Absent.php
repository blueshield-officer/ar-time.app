<?php

namespace App\Services;

use App\Services\ARHS_Data_Extractor\Absents\Database_Requests;

class Notificator_Absent
{
    private $notifications;

    public function __construct()
    {
        $this->notifications = [];
    }
    public function add($period, $object, $method){
        $this->notifications[$method][$period][$object['name']] = [
            'name' => $object['name'],
            'type' => $object['type'],
            'status' => 0,
        ];
    }
    public function update_locations($pdo){
        foreach($this->notifications as $method => $periods){
                foreach($periods as $period_index => $period){
                    foreach($period as $object){
                        $name = $object['name'];
                        $this->notifications[$method][$period_index][$name]['location'] = $this->find_location($pdo, $period, $name);
                }
            }
        }
    }
    private function find_location($pdo, $period, $name)
    {
        if (isset($room_changes[$period])){
            foreach($room_changes[$period] as $direction){
                // TODO переместить это чтобы было автономно, когда норм дб будет
                $person_respective = Database_Requests::short_names_to_respectively($pdo, $name);
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
    public function retreat(){
        return $this->notifications;
    }
}