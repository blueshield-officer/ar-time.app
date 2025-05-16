<?php

namespace App\Services;

use DateTime;

class CacheOperator
{
    private $redis;
    private $DBC;
    private $isAcademicDayOver = false;
    private $config = [
        'regular' => [
            'school' => 1000,
            'non_school' => 1000,
        ],
        'static' => 3600
    ];
    private $formatterTypes = [
        'formatScheduleDescription' => ['bell_schedules_descriptions'],

        'oneStr' => ['calendar_retreats'],

        'oneElement' => ['calendar_schedules', 'calendar_day_off'],

        'multipleElements' => ['absent_additional', 'calendar_events', 'calendar_sports'],

        'absentPeople' => ['absent_people'],

        'absentRoomChanges' => ['absent_room_changes'],

        'scheduleTable' => ['bell_schedules'],

        'facultyList' => ['school_faculty_list'],

        'formatInfoRoadmap' => ['info_roadmap'],

        'fullTable' => ['info_testdays_classes', 'calendar_countdowns', 'calendar_holidays'],

        'safeUserData' => ['users_chosen_people'],

        'byFirstIndex' => ['info_changelog']
    ];

    public function __construct($DBC, $redis)
    {
        $this->DBC = $DBC;
        $this->redis = $redis;

        $this->isAcademicDayOver = $this->fetch('is_day_over', null, false) ?? false;
    }
    public function fetch($tableName, $dataIndex = null, $json = true, $cacheable = true)
    {
        if (!is_null($dataIndex) && !str_starts_with($dataIndex, 'next')){
            $dateObject = explode('-', $dataIndex);
            if (count($dateObject) === 2) {
                $monthDays = cal_days_in_month(CAL_GREGORIAN, $dateObject[1], $dateObject[0]);
                if (is_int($monthDays)) {
                    return $this->fetchMonth($tableName, $dataIndex, $monthDays, $json);
                }
            }
        }
        return $this->fetchSimple($tableName, $dataIndex, $json, $cacheable);
    }
    private function fetchSimple($tableName, $dataIndex = null, $json = true, $cacheable = true)
    {
        if ($cacheable === true){
            $cacheData = $this->getCache($tableName, $dataIndex);
            if ($cacheData === '') {
                return null;
            }
            else {
                if ($json === false && !is_null($cacheData)){
                    $cacheData = json_decode($cacheData, true);
                }
            }
        }
        if ($cacheable === false || is_null($cacheData) ) {
            // get data form SQL
            $dataSQL = $this->fetchDataSQL($tableName, $dataIndex);

            // formatting it for Redis
            if (empty($dataSQL)) {
                $dataSQL = null;
            }
            else {
                $formatType = $this->findFormatType($tableName);
                $dataSQL = match ($formatType) {
                    'formatScheduleDescription' => $this->formatScheduleDescription($dataSQL),
                    'oneStr' => $this->formatOneStr($dataSQL),
                    'multipleElements' => $this->formatMultipleElements($dataSQL),
                    'oneElement' => $this->formatOneElement($dataSQL),
                    'absentPeople' => self::formatAbsentPeople($dataSQL),
                    'absentRoomChanges' => self::formatAbsentRoomChanges($dataSQL),
                    'scheduleTable' => self::formatSchedules($dataSQL),
                    'facultyList' => self::formatSchoolFacultyList($dataSQL),
                    'formatInfoRoadmap' => self::formatInfoRoadmap($dataSQL),
                    'safeUserData' => self::formatSafeUserData($dataSQL),
                    'byFirstIndex' => self::formatByFirstIndex($dataSQL),
                    default => $dataSQL,
                };

                if ($json === true && !is_null($dataSQL)) {
                    $dataSQL = json_encode($dataSQL);
                }
            }
            if ($dataSQL)
            $cacheData = $dataSQL;

            // ================= record redis data =====================
            if ($cacheable === true) {
                if (is_null($cacheData)) {
                    $dataToRecord = '';
                }
                else {
                    $dataToRecord = $cacheData;
                    if (Utils::isJson($dataToRecord) === false){
                        $dataToRecord = json_encode($dataToRecord);
                    }
                }
                $this->updateCache($tableName, $dataIndex, $dataToRecord);
            }
            // ========================================================
        }
        return $cacheData;
    }
    private function formatDay($index) {return  ( '-' .(($index < 10) ? '0' . $index : $index));}

    private function fetchMonth($tableName, $currentMonthStr, $monthDays, $json = false)
    {
        $newArray = [];

        $currentMonthObject = new \DateTime($currentMonthStr . '-01');
        $nextMonthStr = (clone $currentMonthObject)->modify('+1 month')->format('Y-m');
        $prevMonthStr = ($newMonthObject = clone $currentMonthObject)->modify('-1 month')->format('Y-m');

        // prev month
        $lastDay = ($newMonthObject)->modify('last day of this month')->format('d');;
        for ($i = $lastDay - 5; $i <= $lastDay; $i++) {
            $index = $prevMonthStr . $this->formatDay($i);
            $newArray[$index] = $this->fetchSimple($tableName, $index, false);
        }

        // current month
        for ($i = 1; $i <= $monthDays; $i++) {
            $index = $currentMonthStr . $this->formatDay($i);
            $newArray[$index] = $this->fetchSimple($tableName, $index, false);
        }
        // next month
        for ($i = 1; $i < 14; $i++) {
            $index = $nextMonthStr . $this->formatDay($i);
            $newArray[$index] = $this->fetchSimple($tableName, $index, false);
        }

        if ($json) {
            $newArray = json_encode($newArray);
        }
        return $newArray;
    }
    private function findFormatType($table)
    {
        foreach ($this->formatterTypes as $formatIndex => $formatterType) {
            if (in_array($table, $formatterType)) {
                return $formatIndex;
            }
        }
    }
    private function fetchDataSQL($tableName, $dataIndex)
    {
        // analyzing data SQL
        if ($tableName === 'early_dismissal_schedules') {
            return $this->findEarlyDismissalSchedules();
        }
        if ($tableName === 'calendar_holidays'){
            return $this->analyzeHolidays();
        }
        if ($tableName === 'calendar_academics'){
            return $this->analyzeAcademics();
        }
        if ($tableName === 'early_dismissal_schedules_pack'){
            return $this->analyzeNextSchedules();
        }
        if ($tableName === 'is_day_over'){
            return $this->analyzeIsDayOver();
        }
        if ($tableName === 'users_chosen_people'){
            return $this->analyzeUsersChosenPeople();
        }

        // getting data from SQL
        $tableParams = $this->DBC->getTableParams($tableName, 'public');
        if ($dataIndex === null) {
            $filters = '';
            $arguments = [];
            if ($tableName === 'school_faculty_list'){
                $filters = ' ORDER BY `last_name` ASC';
            }
            $query = [
                'filters' => $filters,
                'arguments' => $arguments
            ];
        }
        else if (str_starts_with($dataIndex, 'next') ) {
            $query = [
                'filters' => ' WHERE `date` >= ? ',
                'arguments' => [$this->isAcademicDayOver ? date('Y-m-d', strtotime('+1 day')) : date('Y-m-d')]
            ];
            if (count(explode(':', $dataIndex)) > 1){
                $query['filters'] .= ' AND (';
                $dataIndex = str_replace('next:', '', $dataIndex);
                preg_match_all('/(.*?)(AND|OR|$)/', $dataIndex, $queryBlocks);

                foreach ($queryBlocks[1] as $index => $queryBlockGroupOne) {
                    if (empty($queryBlockGroupOne)) {
                        continue;
                    }

                    $queryConnector = $queryBlocks[2][$index];

                    preg_match('/(.*?)(!?[=><])(.*)/', $queryBlockGroupOne, $operators);
                    $column = $operators[1];
                    $operator = $operators[2];
                    $value = $operators[3];

                    $query['filters'] .= '`' . $column . '` ' . $operator . ' ?' . (($queryConnector !== '') ? ' ' . $queryConnector . ' ' : ')');
                    $query['arguments'][] = $value;
                }
            }
            $query['filters'] .= ' ORDER BY `date` ASC; LIMIT 1';
        }
        else {
            $query = [
                'filters' => ' WHERE ' . $tableParams['columnsArray'][array_key_first($tableParams['columnsArray'])] . ' = ?',
                'arguments' => [$dataIndex]
            ];
        }
        $dataSQL = $this->DBC->fetchAll(
            $this->DBC->query(
                'SELECT ' . $tableParams['columns'] .' FROM '. $tableName . $query['filters'], $query['arguments']
            )
        );
        return $dataSQL;
    }
    private function formatOneStr($array){
        $array = $array[0];
        unset($array[array_key_first($array)]);
        return $array[array_key_first($array)];
    }
    private function formatOneElement($array)
    {
        $array = $array[0];
        return $array;
    }
    private function formatMultipleElements($array)
    {
        foreach ($array as $index => $object) {
            unset($array[$index][array_key_first($object)]);
        }
        return $array;
    }
    private function formatSchedules($array)
    {
        $newArray = [];
        foreach ($array as $schedule) {
            $arrayToAdd = $schedule;
            unset($arrayToAdd['schedule_id']);
            unset($arrayToAdd['position']);
            unset($arrayToAdd['official']);
            $newArray[$schedule['official']][$schedule['schedule_id']][$schedule['position']] = $arrayToAdd;
        }
        $firstKey = array_key_first($newArray);
        $innerArray = $newArray[$firstKey] ?? null;

        if (is_array($innerArray) && count($newArray) === 1 && count($innerArray) === 1) {
            $innerKey = array_key_first($innerArray);
            return $innerArray[$innerKey];
        }

        return $newArray;
    }
    private function formatAbsentPeople($array)
    {
        $newArray = [];
        foreach ($array as $row) {
            $newArray[$row['teacher_id']][] = $row['period'];
        }
        return $newArray;
    }
    private function formatAbsentRoomChanges($array)
    {
        $newArray = [];
        foreach ($array as $row) {
            $newArray[$row['teacher_id']][$row['period']] = $row['location'];
        }
        return $newArray;
    }
    private function formatInfoRoadmap($array){
        function formatDate($str)
        {
            if (is_null($str)) {
                return $str;
            }
            $dateTime = \DateTime::createFromFormat('Y-m-d', $str);
            return $dateTime->format('F j, Y');
        }
        usort($array, function ($a, $b) {
            return $a["status"] <=> $b["status"];
        });

        $newArray = [];
        foreach ($array as $row) {
            $year = explode('-', $row['date_finish_planned'])[0];
            $row['color'] = match ($row['status']) {
                -1 => '#cb4335',
                0 => '#f1c40f',
                1 => '#2e86c1',
                2 => '#28b463',
            };
            $row['status'] = match ($row['status']){
                -1 => 'Planned',
                0 => 'In Progress',
                1 => 'Partially Ready',
                2 => 'Ready',
            };
            $row['date_finish_planned'] = formatDate($row['date_finish_planned']);
            $row['date_finish'] = formatDate($row['date_finish']);
            $newArray[$year][] = $row;
        }
        ksort($newArray);
        return $newArray;
    }
    private function formatScheduleDescription($array)
    {
        // TODO решить пиздец с форматировами
        $array = array_column($array, 'description', 'schedule');
        if (count($array) === 1) {
            return $array[array_key_first($array)];
        }
        return $array;
    }


    private function analyzeNextSchedules()
    {
        // load schedules data
        $arrayToReturn = [];
        $earlyDismissalData = $this->fetch('early_dismissal_schedules', null, false);
        $earlyDismissalSchedulesQueryString = array_map(fn($schedule) => 'schedule='.$schedule, array_keys($earlyDismissalData));
        $arrayToReturn['nextSchedule'] = [
            'nonA' => $this->fetch('calendar_schedules', 'next:schedule!=a', false),
            'earlyDismissal' => $this->fetch('calendar_schedules', 'next:' . join('OR', $earlyDismissalSchedulesQueryString), false),
        ];
        $arrayToReturn['earlyDismissalData'] = $earlyDismissalData;

        // load schedule table and remove if it is more than 30 days
        $earlyDismissal = null;
        foreach ($arrayToReturn['nextSchedule'] as $index => $schedule) {
            $scheduleDate = DateTime::createFromFormat('Y-m-d', $schedule['date']);
            if ((new DateTime('now'))->modify('+30 days') < $scheduleDate){
                $arrayToReturn['nextSchedule'][$index] = null;
                continue;
            }

            if (!is_null($schedule)) {
                if ($schedule['schedule'] !== null && $schedule['schedule'] !== 'special'){
                    // finding class length
                    $table = $this->fetch('bell_schedules', $schedule['schedule'], false);
                    $classLength = [];
                    foreach ($table as $period) {
                        if ($period['length'] !== null) {
                            $classLength[] = $period['length'];
                        }
                    }
                    $classLength = array_count_values($classLength);
                    asort($classLength);
                    $classLength = array_key_last($classLength);

                    $endTime = $table[array_key_last($table)]['end_time'];
                    $endUnix = $table[array_key_last($table)]['end_unix'];
                    $lastStartUnix = $table[array_key_last($table)]['start_unix'];
                    if ($endTime === null) {
                        $secondsEnd = $lastStartUnix + 3600;
                        $stringEnd = '≈ ' . (DateTime::createFromFormat('h:i a', $table[array_key_last($table)]['start_time'])->modify("+60 minutes"))->format('h:i a');
                        $accuracy = false;
                    }
                    else {
                        $stringEnd = $endTime;
                        $secondsEnd = $endUnix;
                        $accuracy = true;
                    }
                    if ($secondsEnd + 2400 < 51600) {
                        $earlyDismissal = true;
                    }
                    $endingTime = $stringEnd;
                    $endingDifference = Utils::secondsToTime(51600 - $secondsEnd, 'string', $accuracy);
                }
                else {
                    $endingTime = '?';
                    $endingDifference = null;
                    $table = null;
                    $classLength = "?";
                    $endTime = '?';
                }
                $arrayToReturn['nextSchedule'][$index]['classLength'] = $classLength;
                $arrayToReturn['nextSchedule'][$index]['scheduleTable'] = $table;
                $arrayToReturn['nextSchedule'][$index]['endTime'] = $endingTime;
                $arrayToReturn['nextSchedule'][$index]['timeDifference'] = $endingDifference;
            }
        }
        return $arrayToReturn;
    }

    private function analyzeIsDayOver(){
        $secondsFromMidnight = Utils::getSecondsFromMidnight();
        $dayEndSeconds = 51600;
        $currentSchedule = $this->fetch('calendar_schedules', date('Y-m-d'), false);
        if (!is_null($currentSchedule)){
            if (isset($currentSchedule['schedule'])){
                $result = $this->DBC->fetch(
                    $this->DBC->query(
                        'SELECT `start_unix`, `end_unix` FROM `bell_schedules` WHERE `schedule_id` = ? ORDER BY position DESC LIMIT 1;', [$currentSchedule['schedule']]
                    )
                );
                if ($result !== false){
                    if ($result['end_unix'] !== null) {
                        $dayEndSeconds = $result['end_unix'];
                    }
                    else {
                        $dayEndSeconds = $result['start_unix'] + 3600;
                    }
                }
            }
        }
        return $dayEndSeconds <= $secondsFromMidnight;
    }
    private function findEarlyDismissalSchedules()
    {
        $minimum = 2400;
//        'SELECT `start_unix`, `end_unix` FROM `bell_schedules` WHERE `schedule_id` = 'a' ORDER BY position DESC;'
        //'SELECT DISTINCT `schedule_id` FROM `bell_schedules`'
        $schedules = $this->DBC->fetchAll(
            $this->DBC->query(
                'SELECT DISTINCT `schedule_id` FROM `bell_schedules`'
            )
        );
        $schedules = array_map(function ($schedule) {
            return $schedule['schedule_id'];
        }, $schedules);

        $earlyDismissalSchedules = [];
        foreach ($schedules as $schedule) {
            $endTime = $this->DBC->fetchAll(
                $this->DBC->query(
                    'SELECT `start_unix`, `end_unix`, `start_time`, `end_time` FROM `bell_schedules` WHERE `schedule_id` = ? ORDER BY position DESC LIMIT 1;', [$schedule]
                )
            )[0];

            if ($endTime['end_unix'] === null) {
                $secondsEnd = $endTime['start_unix'] + 3600;
                $stringEnd = '≈ ' . date('h:i a', strtotime('+ 60 minutes', strToTime($endTime['start_time'])));
                $accuracy = false;
            }
            else {
                $stringEnd = $endTime['end_time'];
                $secondsEnd = $endTime['end_unix'];
                $accuracy = true;
            }
            if ($secondsEnd + $minimum < 51600) {
                $earlyDismissalSchedules[$schedule] = [
                    'end_unix' => Utils::secondsToTime(51600 - $secondsEnd, 'string', $accuracy),
                    'end_time' => $stringEnd,
                ];
            }
        }
        if (empty($earlyDismissalSchedules)) {
            return null;
        }
        return $earlyDismissalSchedules;
    }
    private function analyzeHolidays()
    {
        $dateNow = new DateTime('now');
        $result = $this->DBC->fetchAll(
            $this->DBC->query(
                'SELECT ' . $this->DBC->getTableParams('calendar_holidays')['columns'] . ' FROM `calendar_holidays`'
            )
        );
        $holidaysArray = [];
        foreach ($result as $row){
          if (count(explode('-', $row['date'])) === 3){

          }
          else if (count(explode('-', $row['date'])) === 2){
              $date = new DateTime(date('Y') . '-' . $row['date']);
              if ($date < $dateNow) {
                  $date->modify('+1 year');
              }
              $row['date'] = $date->format('Y-m-d');
          }
          else if($row['date'] === 'easter_date()'){
              $easter = easter_date($dateNow->format('Y'));
              if ($easter < $dateNow->getTimestamp()) {
                  $easter = easter_date($dateNow->format('Y') + 1);;
              }
              $row['date'] = date('Y-m-d', $easter);
          }
          else {
              $date = new DateTime($row['date'] . ' ' . date('Y'));
              if ($date < new DateTime('now')) {
                  $date = new DateTime($row['date'] . ' ' . ((int)date('Y') + 1));
              }
              $row['date'] = $date->format('Y-m-d');
          }
          $holidaysArray[Utils::assimilate($row['name'])] = [
              'date' => $row['date'],
              'name' => $row['name'],
          ];
        };
        usort($holidaysArray, function($a, $b) {
            return strtotime($a['date']) - strtotime($b['date']);
        });
        return $holidaysArray;
    }
//    private function analyzeUserSchoolFacultyList(){
//        $string = join(', ', array_map(fn($i) => '`p' . $i . '`', range(1, 8)));
//        $result = $this->DBC->fetch(
//            $this->DBC->query(
//               'SELECT `hr`, `custom`, ' . $string . '  FROM `users_accounts`'
//            )
//        );
//        $toReturn = [];
//        foreach ($result as $index => $row){
//            if (str_contains($index, 'p') && in_array((int)substr($index, -1, 1), range(1,8))){
//                $toReturn[substr($index, 1, 1)] = $row;
//            }
//            else {
//                $toReturn[$index] = $row;
//            }
//        }
//        return $toReturn;
//
//    }
    private function formatSchoolFacultyList($array)
    {
        $newArray = [];
        $newArray['t'] = [];
        $newArray['s'] = [];

        foreach ($array as $person) {

            $personToAdd = $person;
            $newArray[$person['position']][$person['teacher_id']] = $personToAdd;
        }
        return $newArray['t'] + $newArray['s'];
    }
    private function formatByFirstIndex($array)
    {
        $newArray = [];
        foreach ($array as $object){
            $dataToAdd = $object;
            unset($dataToAdd[array_key_first($dataToAdd)]);
            $newArray[$object[array_key_first($object)]][] = $dataToAdd;
        }
        krsort($newArray);
        return $newArray;
    }
    private function analyzeAcademics(){
        $result = $this->DBC->fetchAll(
            $this->DBC->query(
                'SELECT ' . $this->DBC->getTableParams('calendar_academics')['columns'] . ' FROM `calendar_academics`'
            )
        );
        $academicsArray = [];
        foreach ($result as $row){
            if (!isset($academicsArray[Utils::assimilate($row['name'])]['name'])){
                $academicsArray[Utils::assimilate($row['name'])]['name'] = $row['name'];
            }
            $academicsArray[Utils::assimilate($row['name'])][$row['status']] = $row['date'];
        }
//        echo '<pre>';
//        var_dump($academicsArray);
//        echo '</pre>';
//        die();
        return $academicsArray;
    }

    private function analyzeUsersChosenPeople()
    {
        $userId = Session::getAccountSessionData()['id'];
        $result = $this->DBC->fetch(
            $this->DBC->query(
                'SELECT * FROM `users_chosen_people` WHERE `user_id` = ? LIMIT 1', [$userId]
            )
        );
        return $result;
    }

    private function formatSafeUserData($array){
        unset($array['user_id']);
        return $array;
    }

    public function clearOldAbsentsCache($tableName, $dataIndex)
    {
        $this->deleteCache($tableName, $dataIndex);
    }

    private function deleteCache($tableName, $dataIndex)
    {
        return $this->redis->delete('cacheSQL:' . $tableName . ($dataIndex ? (':' . $dataIndex) : ''));
    }

    private function getCache($tableName, $dataIndex)
    {
//        return null;
        return $this->redis->get('cacheSQL:' . $tableName . ($dataIndex ? (':' . $dataIndex) : ''));
    }

    private function updateCache($tableName, $dataIndex, $str)
    {
        // TODO add cache redis and check <-- ????
        // some stuff here
        $this->redis->set('cacheSQL:' . $tableName . ':' . $dataIndex, $str, 300);
    }
}