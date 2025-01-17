<?php

namespace App\Services\ARHS_Data_Extractor\Calendar;

class Database_Requests {
    static function getTableInfo($table)
    {
        return match ($table){
            'calendar_schedules' => ['date', 'schedule', 'description', 'testDay', 'school'],
            'calendar_events' => ['date', 'title', 'description', 'code', 'important', 'md5_key'],
            'calendar_grade_off' => ['date', '9', '10', '11', '12'],
            'calendar_sports' => ['date', 'sport', 'gender', 'side', 'varsity', 'opponent', 'description', 'time', 'location', 'special', 'md5_key'],
            'calendar_education' => ['date', 'quarter', 'status'],
        };
    }
    static function keep_update($DBC, $date)
    {
        $result = $DBC->fetch($DBC->query('SELECT `keep_update` FROM `calendar_schedules` WHERE `date` = ?', [$date]));
        if ($result !== false){
            $result = $result['keep_update'];
            if ($result === 1){
                return true;
            }
            return false;
        }
        else {
            return true;
        }
    }
    static function getOldData($DBC, $table, $dateStart, $difficult){
        $fields = join(', ', array_map(fn($element) => '`' . $element . '`', self::getTableInfo($table)));
        $query = 'SELECT ' . $fields . ' FROM ' . $table . ' WHERE date >= ?';
        $oldSchedule = $DBC->fetchAll($DBC->query($query, [$dateStart]));
        $oldData = [];
        if ($difficult === true){
            foreach ($oldSchedule as $item) {
                $oldData[$item['date']][] = $item;
            }
        }
        else {
            foreach ($oldSchedule as $item) {
                $oldData[$item['date']] = $item;
            }
        }
        return $oldData;
    }
    static function insertNewData($DBC, $table, $data){
        $fields = array_map(fn($element) => '`' . $element . '`', self::getTableInfo($table));
        $params = self::getTableInfo($table);
        $query = 'INSERT INTO ' . $table . ' (' . join(', ', $fields) . ') VALUES ('. join(', ', array_fill(0, count($fields), '?')) .')';
        foreach ($data as $element){
            if (Database_Formatters::containsArray($element)){
                foreach ($element as $item){
                    $values = array_map(fn($field) => $item[$field] ?? null, $params);
                    $DBC->query($query, array_values($values));
                }
            }
            else {
                $values = array_map(fn($field) => $element[$field] ?? null, $params);
                $DBC->query($query, array_values($values));
            }
        }
    }
    static function deleteOldData($DBC, $table, $dateArray){
        $placeholders = join(', ', array_fill(0, count($dateArray), '?'));
        $query = 'DELETE FROM ' . $table . ' WHERE `date` IN ('.$placeholders.')';
        $DBC->query($query, array_values($dateArray));
    }
}
