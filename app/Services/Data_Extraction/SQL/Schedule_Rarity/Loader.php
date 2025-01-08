<?php

namespace App\Services\Data_Extraction\SQL\Schedule_Rarity;

use App\Services\DB_Connection;

class Loader
{
    static public function load(){
        $pdo = DB_Connection::getPDO();

        // creating schedule array OBJECTS
        $schedule_data = [
            'total_quantity' => 0,
            'data_array' => []
        ];
        $schedule_array = range(0, 12);
        foreach ($schedule_array as $schedule_number) {
            $schedule_data['data_array'][$schedule_number] = new ScheduleInfo($schedule_number);
        }

        // adding quantities
        $database_schedules_array = DataExtractor::extract_schedule($pdo);
        $schedule_data['total_quantity'] = count($database_schedules_array);
        foreach ($database_schedules_array as $database_schedule) {
            $database_schedule = (int)$database_schedule['schedule'];
            if (($database_schedule >= 0 && $database_schedule <= 12) === false){
                $database_schedule = 0;
            }
            $schedule_data['data_array'][$database_schedule]->add_quantity(1);
        }


        // adding percentage
        $percentage_per_one = 100 / $schedule_data['total_quantity'];
        foreach ($schedule_data['data_array'] as $schedule_object) {
            $percentage = $schedule_object->get_quantity() * $percentage_per_one;
            $percentage = round($percentage, 2);
            $schedule_object->set_percentage($percentage);
        }

        // returning formatted array
        /** @var string $index */
        $formatted_schedule_data = [];
        foreach ($schedule_data['data_array'] as $index => $schedule_object) {
            $formatted_schedule_data[$index] = $schedule_object->getInfo();
        }

        return $formatted_schedule_data;
    }
}