<?php

namespace App\Services\ARHS_Data_Extractor\Absents;

use App\Services\DatabaseRequests;
use App\Services\DBC;
use App\Services\GoogleDocumentExtractor;
use App\Services\RedisOperator;
use Google\Client;
use Google\Service\Docs;
use App\Services\ARHS_Data_Extractor\Formatters;
use Redis;

class Loader
{
    static private function db_operate_absents($DBC, $date, $period, $object, $type, $method){
        $type_array = [
            'people' => [
                'table' => 'absent_people',
                'data' => ['date', 'period', 'name', 'type']
            ],
            'room_changes' => [
                'table' => 'absent_room_changes',
                'data' => ['date', 'period', 'name', 'location']
            ]
        ];
        $method_array = [
            0 => 'DELETE FROM `' . $type_array[$type]['table'] . '` WHERE ' . join(' AND ', array_map(fn($data) => '`' . $data . '` = ?', $type_array[$type]['data'])),
            1 => 'INSERT INTO `' . $type_array[$type]['table'] . '` (' . join(', ', $type_array[$type]['data']) . ') VALUES (' . join(', ', array_fill(0, count($type_array[$type]['data']), '?')) . ')',
        ];
        $query = $method_array[$method];
        $params = [];
        $params[] = $date;
        $params[] = $period;
        foreach ($type_array[$type]['data'] as $parameter) {
            if ($parameter !== 'date' && $parameter !== 'period') {
                $params[] = $object[$parameter];
            }
        }
        $DBC->query($query, $params);
    }
    static private function document_findStartIndex($document, $target, $index, $maxRepeat)
    {
        $months = [
            'january', 'february', 'march', 'april', 'may', 'june', 'july ', 'august', 'september', 'october', 'november', 'december',
        ];
        $targets = [
            't' => 'the following teachers is/are absent today',
            's' => 'the following staff is/are absent today',
            'r' => 'please note the following room changes',
            'end' => '_______________________________________________________________'
        ];
        $class = $targets[$target];
        for ($i = 0; $i <= $maxRepeat; $i++){
            $index += 1;
            $str = strtolower($document[$index]);
            // 2025-12-06, никогда не забудем
            if ($target == 'r' || $target == 'end'){
                if (str_contains($str, 'students must check')){
                    if ($target == 'r'){
                        return $index - 1;
                    }
                    return null;
                }
                foreach ($months as $month){
                    if (str_contains($str, $month)){
                        if ($target == 'r'){
                            return $index - 1;
                        }
                        return null;
                    }
                }
            }
            if (str_contains($str, $class)){
                return $index;
            }
            else if (str_contains($str, '_______________________________________________________________')){
                return null;
            }
        }
        return null;
    }
    static private function document_findDateIndex($date, $document, $maxRepeat)
    {
        $dateParams = [
            'day' => $date->format('j'),
            'week day' => strtolower($date->format('l')),
            'month' => strtolower($date->format('F')),
            'year' => $date->format('Y')
        ];
        for ($i = 0; $i < $maxRepeat; $i++){
            $str = strtolower($document[$i]);
            foreach ($dateParams as $dateElement){
                if (!str_contains($str, $dateElement)){
                    continue 2;
                }
            }
            return $i;
        }
        return null;
    }
    static private function find_diference($array_new, $array_old)
    {
        $new_data = [
            0 => [],
            1 => []
        ];
        if (is_null($array_old)){
            return [
                1 => $array_new
            ];
        }
        foreach ($array_new as $period_index => $period) {
            foreach ($period as $object) {
                if (isset($array_old[$period_index][$object['name']])) {
                    if ($array_old[$period_index][$object['name']] !== $object) {
                        $new_data[1][$period_index][] = $object;
                    }
                } else {
                    $new_data[1][$period_index][] = $object;
                }
            }
        }
        foreach ($array_old as $period_index => $period) {
            foreach ($period as $object) {
                if (!isset($array_new[$period_index][$object['name']])) {
                    $new_data[0][$period_index][] = $object;
                }
            }
        }
        return $new_data;
    }
    static private function roomChangesToArray($array)
    {
        $roomChangesArray = [];
        $period = 0;
        foreach ($array as $str){
            $str = trim(strtolower($str));
            if (str_contains($str, 'period')){
                $period = substr($str, 7, 1);
                $str = substr($str, 8);
                if ($str === ''){
                    continue;
                }
            }
            $directionArray = explode('-', $str);
            foreach ($directionArray as $element){
                $tempArray = explode('To', ucwords($element));
                $teachers = Formatters::fix_name($tempArray[0]);
                $location = trim($tempArray[1]);
                foreach ($teachers as $teacher){
                    $roomChangesArray[$period][$teacher]['name'] = $teacher;
                    $roomChangesArray[$period][$teacher]['location'] = $location;
                }
            }
        }
        if (count($roomChangesArray) === 0){
            return null;
        }
        return $roomChangesArray;
    }
    static private function peopleToArray($teachers, $staff){
        $typeArray = [
            't' => $teachers,
            's' => $staff
        ];
        $absent_people_array = [];
        foreach ($typeArray as $type => $str){
            $lastSpace = null;
            for ($i = 0; substr($str, $i, 1) !== ''; $i++){
                $symbol = substr($str, $i, 1);
                if ($symbol === ' '){
                    $lastSpace = $i;
                }
                else if ($symbol === '.'){
                    if (count(explode('.', $str)) === 2){
                        $lastSpace = 'LAST PERSON';
                        $fullString = trim($str);
                    }
                    else if ($lastSpace === null){
                        continue;
                    }
                    else {
                        $fullString = substr($str, 0, $lastSpace);
                    }

                    $tempArray = explode('(', $fullString);
                    $name = trim($tempArray[0]);
                    if (isset($tempArray[1])){
                        $periodsStr = str_replace(')', '', $tempArray[1]);
                        $periods = [];
                        $allPeriodArray = ['SB', 'HR', '1', '2', '3', '4', '5', '6', '7', '8'];
                        foreach ($allPeriodArray as $period){
                            if (str_contains($periodsStr, $period)){
                                $periods[] = $period;
                            }
                        }
                        if ($periods === ['SB']){
                            $periods = $allPeriodArray;
                        }
                        else if ($periods === []){
                            $periods[] = $periodsStr;
                        }
                    }
                    else {
                        $periods = ['HR', '1', '2', '3', '4', '5', '6', '7', '8'];
                    }
                    // array for better work with periods
                    foreach ($periods as $period){
                        $absent_people_array[$period][$name] = [
                            'name' => $name,
                            'type' => $type
                        ];
                    }
                    if ($lastSpace === 'LAST PERSON'){
                        break;
                    }
                    else {
                        $str = substr($str, $lastSpace);
                        $i = 0;
                        $lastSpace = null;
                    }
                }
            }
        }
        if (count($absent_people_array) === 0){
            return null;
        }
        return $absent_people_array;
    }
    static private function docExtractor()
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
    static private function fetchDayData($DBC, $date)
    {
        return $DBC->fetch($DBC->query('SELECT * FROM `calendar_schedules` WHERE `date` >= ? AND `school` = 1 ORDER BY `date` ASC LIMIT 1', [$date->format('Y-m-d')]));
    }
    static private function isDay($DBC, $date, $schedule)
    {
        if ((int)$schedule !== 0) {
            $result = $DBC->fetchAll($DBC->query('SELECT * FROM `bells` WHERE `schedule_id` = ? ORDER BY `position` ASC', [$schedule]));
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
    static private function record_data($data_array, $class)
    {
        foreach ($data_array as $method => $object) {
            foreach ($object as $period => $period_array) {
                foreach ($period_array as $info) {
                    $class->add($period, $info, $method);
                }
            }
        }
    }
    static public function load()
    {
        $execution_time_start = microtime(true);

        $DBC = DBC::getInstance();

        $DatabaseRequests = new DatabaseRequests($DBC);


//      $date = \DateTime::createFromFormat('Y-m-d', $date);

        // getting next available date
        $date = new \DateTime('now');
        $dayData = self::fetchDayData($DBC, $date);
        if (self::isDay($DBC, $date, $dayData['schedule']) !== true) {
            $date->modify('+1 day');
            $date = self::fetchDayData($DBC, $date)['date'];
        } else {
            $date = $dayData['date'];
        }
        // DEBUG
        $date = \DateTime::createFromFormat('Y-m-d', '2025-01-17');


        // Actuall work
        $absences = new Absences($DBC, $date);
        $googleDocumentExtractor = new GoogleDocumentExtractor($_SERVER['DOCUMENT_ROOT'] . '/../credentials.json');
        $document = $googleDocumentExtractor->extract('1KhmTALivDbAvLusQjZQOOQmPKDm-2Do-gVfvAdf_6WA');

        $documentStartIndex = self::document_findDateIndex($date, $document, 1000);

        if ($documentStartIndex !== null) {
            $index = [
                'teachers' => $teachers = self::document_findStartIndex($document, 't', $documentStartIndex, 3),
                'staff' => $staff = self::document_findStartIndex($document, 's', $teachers, 30),
                'roomChanges' => $roomChanges = self::document_findStartIndex($document, 'r', $staff, 30),
                'end' => $end = self::document_findStartIndex($document, 'end', $roomChanges, 30)
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
            $absences->setPeople(self::peopleToArray($absenceData['teachers'], $absenceData['staff']));
            $absences->setRoomChanges(self::roomChangesToArray($absenceData['roomChanges']));

            // PEOPLE
            $absentPeople = new People();
            if ($absences->getPeople() !== null) {
                // Work with SQL
                $people_old_temp = $DBC->fetchAll($DBC->query('SELECT `period`, `name`, `type` FROM `absent_people` WHERE `date` = ?', [$date->format('Y-m-d')]));
                $people_old = [];
                $people_new = $absences->getPeople();

                foreach ($people_old_temp as $element) {
                    $people_old[$element['period']][$element['name']] = [
                        'name' => $element['name'],
                        'type' => $element['type']
                    ];
                }

                if ($people_new !== $people_old) {
                    self::record_data(self::find_diference($people_new, $people_old), $absentPeople);
                }
            }

            // ROOM CHANGES
            $room_changes = new Room_Changes();
            if ($absences->getRoomChanges() !== null) {
                // Working with SQL
                $result = $DBC->fetchAll($DBC->query('SELECT `period`, `name`, `location` FROM `absent_room_changes` WHERE `date` = ?', [$date->format('Y-m-d')]));

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
                self::record_data(self::find_diference($room_changes_new, $room_changes_old), $room_changes);
                $absentPeople->update_locations($DBC);
            }

            // Redis
            $redis = RedisOperator::getInstance();
            $absent_json = json_encode([
                'absent_people' => $people_new,
                'room_changes' => $room_changes_new ?? null,
            ]);
            // check is md5 the same
            if (md5($absent_json) !== $redis->get('absents:md5')) {
                $redis->add('absents:data', $absent_json);
                $redis->add('absents:md5', md5($absent_json));
                $redis->add('absents:date', (new \DateTime('now'))->format('j F, h:i A'));
            }
            // SQL absents
            foreach ([
                         'people' => $absentPeople->getInfo(),
                         'room_changes' => $room_changes->getInfo()
                     ] as $class => $change_array) {
                foreach ($change_array as $method => $periods) {
                    foreach ($periods as $period => $person_object) {
                        foreach ($person_object as $person) {
                            self::db_operate_absents($DBC, $date->format('Y-m-d'), $period, $person, $class, $method);
                        }
                    }
                }
            }
            // SQL school staff
            foreach ($absentPeople->peopleToRecordAtDB() as $name => $personObject) {
                $result = $DBC->fetchAll($DBC->query('SELECT * FROM `school_staff_list` WHERE `short_name` = ?', [$name]));
                if (empty($result)) {
                    $DatabaseRequests->schoolStaffInsertPerson($personObject);
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
                        'data' => $absentPeople->getInfo(),
                        'important' => true
                    ],
                    'room_changes' => [
                        'data' => $room_changes->getInfo(),
                        'important' => true
                    ]
                ],
                'local_date' => $date,
                'type' => 'default'
            ];
        }
    }
}