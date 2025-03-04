<?php

namespace App\Services\ARHS_Data_Extractor\Absents;

use App\Services\CacheOperator;
use App\Services\DatabaseRequests;
use App\Services\DBC;
use App\Services\GoogleDocumentExtractor;
use App\Services\RedisOperator;
use App\Services\Formatters;

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
            'r' => '________________________________________',
            'end' => '_______________________________________________________________'
        ];
        $class = $targets[$target];
        for ($i = 0; $i <= $maxRepeat; $i++){
            $index += 1;
            $str = strtolower($document[$index]);
            // 2025-12-06, никогда не забудем
            if ($target == 'r' || $target == 'end'){
                foreach ($months as $month){
                    if (str_contains($str, $month)){
                        return $index;
                    }
                }
            }
            if (str_contains($str, $class)){
                return $index;
            }
            else if (str_contains($str, '_______________________________________________________________')){
                return $index - 1;
            }
        }
        return $index - 1;
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
    static private function roomChangesAndAdditionalToArray($array)
    {
        $data = [
            'roomChanges' => null,
            'additional' => null
        ];
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
            if ($period !== 0){
                $directionArray = explode('-', $str);
                foreach ($directionArray as $element){
                    $tempArray = explode('To', ucwords($element));
                    $teachers = self::fix_name($tempArray[0]);
                    $location = trim($tempArray[1]);
                    foreach ($teachers as $teacher){
                        $data['roomChanges'][$period][$teacher]['name'] = $teacher;
                        $data['roomChanges'][$period][$teacher]['location'] = $location;
                    }
                }
            }
            else {
                if (!str_contains($str, 'room changes')){
                    $data['additional'][] = ucwords($str);
                }
            }
        }
        return $data;
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
    static private function fetchDayData($DBC, $date)
    {
        return $DBC->fetch($DBC->query('SELECT * FROM `calendar_schedules` WHERE `date` >= ? ORDER BY `date` ASC LIMIT 1', [$date->format('Y-m-d')]));
    }
    static private function isDay($DBC, $date, $schedule)
    {
        if ((int)$schedule !== 0) {
            $result = $DBC->fetchAll($DBC->query('SELECT * FROM `schedule_default` WHERE `schedule_id` = ? ORDER BY `position` ASC', [$schedule]));
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
        var_dump($data_array);
        foreach ($data_array as $method => $object) {
            var_dump($object);
            foreach ($object as $period => $period_array) {
                foreach ($period_array as $info) {
                    $class->add($period, $info, $method);
                }
            }
        }
    }
    static private function fix_name($str){
        $array = explode(',', $str);
        $newArray = [];
        foreach ($array as $item){
            $corrected_name = preg_replace('/(\.)([A-Za-z0-9])/', '$1 $2', $item);
            $newArray[] = ucwords(self::analyzable(trim($corrected_name)));
        }
        return $newArray;
    }
    static private function analyzable($str){
        return strtolower(trim(preg_replace('/\s+/', ' ', $str)));
    }
    static public function load()
    {
        $execution_time_start = microtime(true);

        $DBC = DBC::getInstance();

        $redis = RedisOperator::getInstance();

        $cacheOperator = new CacheOperator($DBC, $redis);

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
        $date = \DateTime::createFromFormat('Y-m-d', '2025-03-03');


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
            $roomChangesAndAdditional = self::roomChangesAndAdditionalToArray($absenceData['roomChanges']);
            $absences->setPeople(self::peopleToArray($absenceData['teachers'], $absenceData['staff']));
            $absences->setRoomChanges($roomChangesAndAdditional['roomChanges']);
            $absences->setAdditional($roomChangesAndAdditional['additional']);

            // PEOPLE
            $absentPeople = new People();
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

            // ROOM CHANGES
            $room_changes = new Room_Changes();
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
            if ($room_changes_new !== $room_changes_old) {
                self::record_data(self::find_diference($room_changes_new, $room_changes_old), $room_changes);
                $absentPeople->update_locations($DBC);
            }


            // Additional
            $result = $DBC->fetchAll($DBC->query('SELECT `text` FROM `absent_additional` WHERE `date` = ?', [$date->format('Y-m-d')]));
            $additional_new = $absences->getAdditional();
            $additional_old = [];
            if (!empty($result)) {
                foreach ($result as $object) {
                    $additional_old[] = $object['text'];
                }
            }
            if ($additional_new !== $additional_old) {
                // add new data to SQL about additional
                $DBC->query('DELETE FROM `absent_additional` WHERE `date` = ?', [$date->format('Y-m-d')]);
                if (!is_null($additional_new)) {
                    foreach ($additional_new as $str) {
                        $DBC->query('INSERT INTO `absent_additional` (`date`, `text`)  VALUES (?, ?)', [$date->format('Y-m-d'), $str]);
                    }
                }
                // Redis
                $cacheOperator->updateAbsents('absent_additional', $date->format('Y-m-d'));
            }


            $absent_md5 = md5(json_encode([$people_new, $room_changes_new ?? null]));
            // check is md5 the same
            if ($absent_md5 !== $redis->get('temp:absents:md5')) {
                $redis->set('temp:absents:md5', $absent_md5);
                $redis->set('temp:absents:last-update-date', (new \DateTime('now'))->format('j F, h:i A'));
            }
            // SQL absents
            $newData = [
                'people' => $absentPeople->getInfo(),
                'room_changes' => $room_changes->getInfo()
            ];
            foreach ($newData as $class => $change_array) {
                foreach ($change_array as $method => $periods) {
                    foreach ($periods as $period => $person_object) {
                        foreach ($person_object as $person) {
                            self::db_operate_absents($DBC, $date->format('Y-m-d'), $period, $person, $class, $method);
                        }
                    }
                }
            }
            // redis save
            if (!empty($newData['people'])) {
                $cacheOperator->updateAbsents('absent_people', $date->format('Y-m-d'));
            }
            if (!empty($newData['room_changes'])) {
                $cacheOperator->updateAbsents('absent_room_changes', $date->format('Y-m-d'));
            }

            // SQL school staff + redis if updated
            $peopleToRecordAtDBArray = $absentPeople->peopleToRecordAtDB();
            if (!empty($peopleToRecordAtDBArray)) {
                foreach ($peopleToRecordAtDBArray as $name => $personObject) {
                    // TODO закончить наконец-то с гендарами на которые я забил раньше : )
                    $textForGender = join(array_slice($document, $documentStartIndex, 50));
                    $result = $DBC->fetchAll($DBC->query('SELECT * FROM school_staff_list WHERE `short_name` = ?', [$name]));
                    if (empty($result)) {
                        $DatabaseRequests->schoolStaffInsertPerson($personObject);
                    }
                }
            }
            
            $execution_time_end = microtime(true);
            $function_time = round($execution_time_end - $execution_time_start, 2);


//            echo '<pre>';
//            var_dump(json_decode($redis->get('data:absent_people:2025-02-27'), true));
//            echo '<pre>';

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