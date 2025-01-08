<?php

namespace App\Services\ARHS_Data_Extractor\Calendar;

class Database_Requests {
    static function keep_update($pdo, $date)
    {
        $query = 'SELECT `keep_update` FROM `schedule_calendar_test` WHERE `date` = ?';
        $stmt = $pdo->prepare($query);
        $stmt->execute([$date]);
        $result = $stmt->fetch();
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
    static function getOldData($pdo, $database, $dateStart, $difficult){
        $select = match ($database){
            'schedule_calendar_test' => '`date`, `schedule`, `description`, `testDay`, `school`',
            'event_calendar_test' => '`date`, `title`, `description`, `code`, `important`, `md5_key`',
            'grade_off_test' => '`date`, `grade`',
            'sport_calendar_test' => '`date`, `sport`, `gender`, `side`, `varsity`, `opponent`, `description`, `time`, `location`, `special`, `md5_key`'
        };
        $query = 'SELECT ' . $select . ' FROM ' . $database . ' WHERE date >= ?';
        $statement = $pdo->prepare($query);
        $statement->execute([$dateStart]);
        $oldSchedule = $statement->fetchAll();
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
    static function insertNewData($pdo, $database, $data){
        $queries = [
            'schedule_calendar_test' => [
                'sql' => 'INSERT INTO schedule_calendar_test (`date`, `schedule`, `description`, `testDay`, `school`) VALUES (?, ?, ?, ?, ?)',
                'fields' => ['date', 'schedule', 'description', 'testDay', 'school']
            ],
            'grade_off_test' => [
                'sql' => 'INSERT INTO grade_off_test (`date`, `grade`) VALUES (?, ?)',
                'fields' => ['date', 'grade']
            ],
            'event_calendar_test' => [
                'sql' => 'INSERT INTO event_calendar_test (`date`, `title`, `description`, `code`, `important`, `md5_key`) VALUES (?, ?, ?, ?, ?, ?)',
                'fields' => ['date', 'title', 'description', 'code', 'important', 'md5_key']
            ],
            'sport_calendar_test' => [
                'sql' => 'INSERT INTO sport_calendar_test (`date`, `sport`, `gender`, `side`, `varsity`, `opponent`, `description`, `time`, `location`, `special`, `md5_key`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                'fields' => ['date', 'sport', 'gender', 'side', 'varsity', 'opponent', 'description', 'time', 'location', 'special', 'md5_key']
            ]
        ];
        $query = $queries[$database]['sql'];
        $fields = $queries[$database]['fields'];

        $statement = $pdo->prepare($query);
        foreach ($data as $element){
            if (Database_Formatters::containsArray($element)){
                foreach ($element as $item){
                    $values = array_map(fn($field) => $item[$field] ?? null, $fields);
                    $statement->execute(array_values($values));
                }
            }
            else {
                $values = array_map(fn($field) => $element[$field] ?? null, $fields);
                $statement->execute(array_values($values));
            }
        }
    }
    static function deleteOldData($pdo, $database, $dateArray){
        $placeholders = join(', ', array_fill(0, count($dateArray), '?'));
        $query = 'DELETE FROM ' . $database . ' WHERE `date` IN ('.$placeholders.')';
        $statement = $pdo->prepare($query);
        $statement->execute($dateArray);
    }
}
