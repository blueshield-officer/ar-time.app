<?php

namespace App\Services\Data_Extraction\SQL\Calendar;

use App\Services\DB_Connection;

class Loader
{
    static function load(){
        $data = [];

        $pdo = DB_Connection::getPDO();

        $information_array = [
            [
                'name' => 'schedules',
                'table' => 'schedule_calendar_test',
                'difficult' => false
            ],
            [
                'name' => 'sports',
                'table' => 'sport_calendar_test',
                'difficult' => true
            ],
            [
                'name' => 'events',
                'table' => 'event_calendar_test',
                'difficult' => true
            ],
            [
                'name' => 'grade_off',
                'table' => 'grade_off_test',
                'difficult' => false
            ]
        ];
        foreach ($information_array as $element){
            $query = 'SELECT * FROM ' . $element['table'];
            $stmt = $pdo->prepare($query);
            $stmt->execute();
            $result = $stmt->fetchAll();

            foreach($result as $row){
                $data[$row['date']][$element['name']] = $row;
            }
        }
        return $data;
    }
}