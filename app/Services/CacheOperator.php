<?php

namespace App\Services;

class CacheOperator
{
    private $redis;
    private $DBC;
    private $config = [
        'absent_additional' => [
            'school' => 1000,
            'non_school' => 1000,
        ],

    ];
    private $formatterTypes = [
        'oneElement' => ['calendar_schedules', 'calendar_day_off'],

        'multipleElements' => ['absent_additional', 'calendar_events', 'calendar_sports'],

        'absentPeople' => ['absent_people'],

        'absentRoomChanges' => ['absent_room_changes'],

        'scheduleTable' => ['schedule_custom', 'schedule_default'],

        'schoolStaffList' => ['school_staff_list'],

        'fullTable' => ['schedule_descriptions', 'info_testdays_classes', 'info_roadmap', 'calendar_countdowns', 'calendar_holidays']
    ];

    public function __construct($DBC, $redis)
    {
        $this->DBC = $DBC;
        $this->redis = $redis;
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
        $tableParams = $this->DBC->getTableParams($tableName, 'public');
        if ($dataIndex === null) {
            $dataSQL = $this->DBC->fetchAll(
                $this->DBC->query(
                    'SELECT ' . $tableParams['columns'] .' FROM '. $tableName
                )
            );
        }
        else {
            $dataSQL = $this->DBC->fetchAll(
                $this->DBC->query(
                    'SELECT ' . $tableParams['columns'] .' FROM '. $tableName . ' WHERE ' . $tableParams['columnsArray'][array_key_first($tableParams['columnsArray'])] . ' = ?', [$dataIndex]
                )
            );
        }
        return $dataSQL;
    }
    private function formatOneElement($array)
    {
        $array = $array[0];
        unset($array[array_key_first($array)]);
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
            $newArray[$schedule['schedule_id']][$schedule['position']] = $arrayToAdd;
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
    public function fetch($tableName, $dataIndex = null)
    {
        $cacheData = $this->redis->get('cacheSQL:' . $tableName . ($dataIndex ? (':' . $dataIndex) : ''));
        if (is_null($cacheData)) {
            // get data form SQL
            $dataSQL = $this->fetchDataSQL($tableName,$dataIndex);
            // formatting it for Redis
            if (empty($dataSQL)) {
                $dataSQL = '';
            }
            else {
                $formatType = $this->findFormatType($tableName);
                $dataSQL = match ($formatType) {
                    'multipleElements' => $this->formatMultipleElements($dataSQL),
                    'oneElement' => $this->formatOneElement($dataSQL),
                    'absentPeople' => self::formatAbsentPeople($dataSQL),
                    'absentRoomChanges' => self::formatAbsentRoomChanges($dataSQL),
                    'scheduleTable' => self::formatSchedules($dataSQL),
                    'schoolStaffList' => self::formatSchoolStaffList($dataSQL),
                    default => $dataSQL,
                };
                $dataSQL = json_encode($dataSQL);
            }
            $cacheData = $dataSQL;
        }
        return $cacheData;
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
        var_dump(json_decode($dataSQL, true));
    }
}