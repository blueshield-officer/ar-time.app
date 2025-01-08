<?php

namespace App\Services\ARHS_Data_Extractor\Absents;

use App\Services\DB_Connection;
use App\Services\Notificator_Absent;
use App\Services\Redis_Operator;
use Google\Client;
use Google\Service\Docs;

class Loader
{
    static public function load()
    {
        $execution_time_start = microtime(true);

        $pdo = DB_Connection::getPDO();

        function docExtractor()
        {
            $client = new Client();
            $client->setAuthConfig(__DIR__ . '/../../../../../credentials.json');
            $client->setScopes([Docs::DOCUMENTS]);
            $client->setAccessType('offline');
            $service = new Docs($client);
            $id = '1KhmTALivDbAvLusQjZQOOQmPKDm-2Do-gVfvAdf_6WA';
            $doc = $service->documents->get($id);

            $allText = [];
            foreach ($doc->body->content as $structuralElement) {
                if ($structuralElement->paragraph) {
                    foreach ($structuralElement->paragraph->elements as $paragraphElement) {
                        if ($paragraphElement->textRun && strlen(trim($paragraphElement->textRun->content)) > 0) {
                            $allText[] = trim($paragraphElement->textRun->content);
                        }
                    }
                }
            }
            return $allText;
        }

        function fetchDayData($pdo, $date)
        {
            $query = 'SELECT * FROM `schedule_calendar_test` WHERE `date` >= ? AND `school` = 1 ORDER BY `date` ASC LIMIT 1';
            $statement = $pdo->prepare($query);
            $statement->execute([$date->format('Y-m-d')]);
            return $statement->fetch();
        }

        function isDay($pdo, $date, $schedule)
        {
            if ((int)$schedule !== 0) {
                $query = 'SELECT * FROM `classes` WHERE `schedule_id` = ' . $schedule . ' ORDER BY `position` ASC';
                $statement = $pdo->prepare($query);
                $statement->execute();
                $result = $statement->fetchAll();
                $end_unix = $result[count($result) - 1]['end_unix'];
                if (is_null($end_unix)) {
                    $end_unix = $result[count($result) - 1]['start_unix'];
                }
                $end_unix = $end_unix + strtotime(date_format($date, 'Y-m-d'));
                if ($end_unix < time()) {
                    return false;
                } else {
                    return true;
                }
            } else {
                $end_unix = strtotime(date_format($date, 'Y-m-d')) + 51600;
                if ($end_unix < time()) {
                    return false;
                } else {
                    return true;
                }
            }
        }

        function record_data($data_array, $class)
        {
            foreach ($data_array as $method => $object) {
                foreach ($object as $period => $period_array) {
                    foreach ($period_array as $info) {
                        $class->add($period, $info, $method);
                    }
                }
            }
        }

//      $date = \DateTime::createFromFormat('Y-m-d', $date);

        // getting next available date
        $date = new \DateTime('now');
        $dayData = fetchDayData($pdo, $date);
        if (isDay($pdo, $date, $dayData['schedule']) !== true) {
            $date->modify('+1 day');
            $date = fetchDayData($pdo, $date)['date'];
        } else {
            $date = $dayData['date'];
        }
        // DEBUG
        $date = \DateTime::createFromFormat('Y-m-d', '2024-12-16');


        // Actuall work
        $absences = new Absences($pdo, $date);
        $document = docExtractor();
        $documentStartIndex = Detectors::findDateIndex($date, $document, 1000);

        if ($documentStartIndex !== null) {
            $index = [
                'teachers' => $teachers = Detectors::findStartIndex($document, 't', $documentStartIndex, 3),
                'staff' => $staff = Detectors::findStartIndex($document, 's', $teachers, 30),
                'roomChanges' => $roomChanges = Detectors::findStartIndex($document, 'r', $staff, 30),
                'end' => $end = Detectors::findStartIndex($document, 'end', $roomChanges, 30)
            ];
            $absenceData = [
                'teachers' => null,
                'staff' => null,
                'roomChanges' => null
            ];
            $typesArray = ['teachers', 'staff', 'roomChanges'];

            // writing down strings of teachers and staff
            foreach ($typesArray as $type) {
                $indexType = match ($type) {
                    'teachers' => 'staff',
                    'staff' => 'roomChanges',
                    'roomChanges' => 'end'
                };
                $stringArray = [];
                for ($i = $index[$type] + 1; $i < $index[$indexType]; $i++) {
                    if (!str_contains($document[$i], '__________')) {
                        $stringArray[] = $document[$i];
                    }
                }
                if ($type === 'roomChanges') {
                    $absenceData[$type] = $stringArray;
                } else {
                    $absenceData[$type] = $output = preg_replace('!\s+!', ' ', trim(join(' ', $stringArray)));
                }
            }
            // adders
            $absences->setPeople(DocString_Formatters::peopleToArray($absenceData['teachers'], $absenceData['staff']));
            $absences->setRoomChanges(DocString_Formatters::roomChangesToArray($absenceData['roomChanges']));

            // PEOPLE
            $notificator = new Notificator_Absent();
            if ($absences->getPeople() !== null) {
                // Work with SQL
                $query = 'SELECT `period`, `name`, `type` FROM `absent_people_test` WHERE `date` = ?';
                $statement = $pdo->prepare($query);
                $statement->execute([$date->format('Y-m-d')]);
                $people_old_temp = $statement->fetchAll();
                $people_old = [];
                $people_new = $absences->getPeople();

                foreach ($people_old_temp as $element) {
                    $people_old[$element['period']][$element['name']] = [
                        'name' => $element['name'],
                        'type' => $element['type']
                    ];
                }
                if ($people_new !== $people_old) {
                    record_data(Detectors::find_diference($people_new, $people_old), $notificator);
                }
            }

            // ROOM CHANGES
            $room_changes = new Room_Changes();
            if ($absences->getRoomChanges() !== null) {
                // Working with SQL
                $query = 'SELECT `period`, `name`, `location` FROM `room_changes_test` WHERE `date` = ?';
                $statement = $pdo->prepare($query);
                $statement->execute([$date->format('Y-m-d')]);
                $result = $statement->fetchAll();

                $room_changes_old = [];
                $room_changes_new = $absences->getRoomChanges();
                if (!empty($result)) {
                    foreach ($result as $object) {
                        $room_changes_old[$object['period']][$object['name']] = [
                            'name' => $object['name'],
                            'location' => $object['location']
                        ];
                    }
                } else {
                    $room_changes_old = null;
                }
                // find new data

                record_data(Detectors::find_diference($room_changes_new, $room_changes_old), $room_changes);
                $notificator->update_locations($pdo);
            }

            $absent_json = json_encode([
                'absent_people' => $people_new,
                'room_changes' => $room_changes_new,
            ]);
            $old_absent_json = Redis_Operator::get_old_md5();
            if ($old_absent_json !== md5($absent_json)) {
                Redis_Operator::write_new_data($absent_json);
            }

            // SQL data
            foreach ([
                         'people' => $notificator->retreat(),
                         'room_changes' => $room_changes->retreat()
                     ] as $class => $change_array) {
                foreach ($change_array as $method => $periods) {
                    foreach ($periods as $period => $person_object) {
                        var_dump($person_object);
                        foreach ($person_object as $person) {
                            Database_Requests::operate_absents($pdo, $date->format('Y-m-d'), $period, $person, $class, $method);
                        }
                    }
                }
            }

            $execution_time_end = microtime(true);
            $function_time = round($execution_time_end - $execution_time_start, 2);
            // DEBUG
            return [
                'name' => 'absents',
                'spent_time' => $function_time,
                'data' => [
                    'absent_people' => [
                        'data' => $notificator->retreat(),
                        'important' => true
                    ],
                    'room_changes' => [
                        'data' => $room_changes->retreat(),
                        'important' => true
                    ]
                ],
                'local_date' => $date,
                'type' => 'default'
            ];
        }
    }
}