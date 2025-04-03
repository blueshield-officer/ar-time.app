<?php

namespace App\Services;

use DateTime;

class CacheOperator
{
    private $redis;
    private $DBC;
    private $config = [
        'regular' => [
            'school' => 1000,
            'non_school' => 1000,
        ],
        'static' => 3600
    ];
    private $formatterTypes = [

        'oneStr' => ['bell_schedules_descriptions'],

        'oneElement' => ['calendar_schedules', 'calendar_day_off'],

        'multipleElements' => ['absent_additional', 'calendar_events', 'calendar_sports'],

        'absentPeople' => ['absent_people'],

        'absentRoomChanges' => ['absent_room_changes'],

        'scheduleTable' => ['bell_schedules'],

        'schoolStaffList' => ['school_staff_list'],

        'formatInfoRoadmap' => ['info_roadmap'],

        'fullTable' => ['bell_schedules_descriptions', 'info_testdays_classes', 'calendar_countdowns', 'calendar_holidays']

    ];

    public function __construct($DBC, $redis)
    {
        $this->DBC = $DBC;
        $this->redis = $redis;
    }
    public function fetch($tableName, $dataIndex = null, $json = true, $cacheable = true)
    {
        if (!is_null($dataIndex) && !str_starts_with($dataIndex, 'next')){
            $dateObject = explode('-', $dataIndex);
            if (count($dateObject) === 2) {
                $monthDays = cal_days_in_month(CAL_GREGORIAN, $dateObject[1], $dateObject[0]);
                if (is_int($monthDays)) {
                    return $this->fetchMonth($tableName, $dataIndex, $monthDays);
                }
            }
        }
        return $this->fetchSimple($tableName, $dataIndex, $json, $cacheable);
    }
    private function fetchSimple($tableName, $dataIndex = null, $json = true, $cacheable = true)
    {
        if ($cacheable === true){
            $cacheData = $this->redis->get('cacheSQL:' . $tableName . ($dataIndex ? (':' . $dataIndex) : ''));
            if ($cacheData === '') {
                return null;
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
                    'oneStr' => $this->formatOneStr($dataSQL),
                    'multipleElements' => $this->formatMultipleElements($dataSQL),
                    'oneElement' => $this->formatOneElement($dataSQL),
                    'absentPeople' => self::formatAbsentPeople($dataSQL),
                    'absentRoomChanges' => self::formatAbsentRoomChanges($dataSQL),
                    'scheduleTable' => self::formatSchedules($dataSQL),
                    'schoolStaffList' => self::formatSchoolStaffList($dataSQL),
                    'formatInfoRoadmap' => self::formatInfoRoadmap($dataSQL),
                    default => $dataSQL,
                };
                if ($json) {
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
        // TODO Когда будет проверка редис если '' то ретурнит null
        return $cacheData;
    }
    private function fetchMonth($tableName, $dataIndex, $monthDays)
    {
        $newArray = [];
        for ($i = 1; $i <= $monthDays; $i++) {
            $index = $dataIndex . '-' . (($i < 10) ? '0' . $i : $i);
            $newArray[$index] = $this->fetchSimple($tableName, $index, false);
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
        // getting data from SQL
        $tableParams = $this->DBC->getTableParams($tableName, 'public');
        if ($dataIndex === null) {
            $query = [
                'filters' => '',
                'arguments' => []
            ];
        }
        else if (str_starts_with($dataIndex, 'next') ) {
            $query = [
                'filters' => ' WHERE `date` >= ? ',
                'arguments' => [date('Y-m-d')]
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
    private function formatSchoolStaffList($array)
    {
        $newArray = [];
        foreach ($array as $person) {
            $shortName = $person['short_name'];
            unset($person['short_name']);
            $newArray[$shortName] = $person;
        }
        return $newArray;
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
        return $newArray;
    }
    private function formatAbsentPeople($array)
    {
        $newArray = [];
        foreach ($array as $row) {
            $newArray[$row['type']][$row['period']][] = $row['name'];
        }
        return $newArray;
    }
    private function formatAbsentRoomChanges($array)
    {
        $newArray = [];
        foreach ($array as $row) {
            $newArray[$row['period']][$row['name']] = $row['location'];
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
    private function formatByIndex($array)
    {
        // TODO решить пиздец с форматировами
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
              if ($date < new DateTime('now')) {
                  $date->modify('+1 year');
              }
              $row['date'] = $date->format('Y-m-d');
          }
          else if($row['date'] === 'easter_date()'){
              $row['date'] = date('Y-m-d', easter_date());
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



    private function updateCache($tableName, $dataIndex, $str)
    {
        // TODO add cache redis and check <-- ????
        // some stuff here
    }
    public function updateAbsents($tableName, $dataIndex)
    {
        $dataSQL = $this->fetchDataSQL($tableName, $dataIndex);
        $formatType = $this->findFormatType($tableName);
        if (empty($dataSQL)) {
            $dataSQL = [];
        }
        else {
            $dataSQL = match ($formatType) {
                'absentPeople' => self::formatAbsentPeople($dataSQL),
                'absentRoomChanges' => self::formatAbsentRoomChanges($dataSQL),
                'multipleElements' => self::formatMultipleElements($dataSQL),
            };
            $dataSQL = json_encode($dataSQL);
        }
        //return $dataSQL;
//        var_dump(json_decode($dataSQL, true));
    }
}