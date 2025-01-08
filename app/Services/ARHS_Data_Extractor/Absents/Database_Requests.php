<?php

namespace App\Services\ARHS_Data_Extractor\Absents;

class Database_Requests
{
    static public function short_names_to_respectively ($pdo, $short_name)
    {
        $query = 'SELECT `honorific`, `last_name` FROM `school_staff_list` WHERE `short_name` = ?';
        $stmt = $pdo->prepare($query);
        $stmt->execute([$short_name]);
        $result = $stmt->fetch();
        if ($result !== false) {
            return [$result['honorific'] . '. ' . $result['last_name']];
        }
        return null;
    }
    static public function operate_absents($pdo, $date, $period, $object, $type, $method){
        $type_array = [
            'people' => [
                'table' => 'absent_people_test',
                'data' => ['date', 'period', 'name', 'type']
            ],
            'room_changes' => [
                'table' => 'room_changes_test',
                'data' => ['date', 'period', 'name', 'location']
            ]
        ];
        $method_array = [
            0 => 'DELETE FROM `' . $type_array[$type]['table'] . '` WHERE ' . join(' AND ', array_map(fn($data) => '`' . $data . '` = ?', $type_array[$type]['data'])),
            1 => 'INSERT INTO `' . $type_array[$type]['table'] . '` (' . join(', ', $type_array[$type]['data']) . ') VALUES (' . join(', ', array_fill(0, count($type_array[$type]['data']), '?')) . ')',
        ];
        $query = $method_array[$method];
        $stmt = $pdo->prepare($query);
        $params = [];
        $params[] = $date;
        $params[] = $period;
        foreach ($type_array[$type]['data'] as $parameter) {
            if ($parameter !== 'date' && $parameter !== 'period') {
                $params[] = $object[$parameter];
            }
        }
        $stmt->execute($params);
    }
}