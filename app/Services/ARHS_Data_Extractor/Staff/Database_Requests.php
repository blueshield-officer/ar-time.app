<?php

namespace App\Services\ARHS_Data_Extractor\Staff;

class Database_Requests
{
    static function insertPeople($pdo, $people)
    {
        $query = 'INSERT INTO `school_staff_list` (`honorific`, `first_name`, `last_name`, `short_name`, `position`, `position_full`) VALUES (?, ?, ?, ?, ?, ?)';
        $statement = $pdo->prepare($query);
        foreach ($people as $element){
            $statement->execute([$element['honorific'], $element['first_name'], $element['last_name'], $element['short_name'], $element['position'], $element['position_full']]);
        }
    }
    static function getOldPeople($pdo)
    {
        $query = 'SELECT `honorific`, `first_name`, `last_name`, `short_name`, `position`, `position_full` FROM `school_staff_list`';
        $statement = $pdo->prepare($query);
        $statement->execute();
        $people = $statement->fetchAll();
        return $people;
    }
    static function deleteOldData($pdo, $data)
    {
        $placeholders = join(', ', array_fill(0, count($data), '?'));
        $query = 'DELETE FROM `school_staff_list` WHERE `short_name` NOT IN (' . $placeholders . ')';
        $statement = $pdo->prepare($query);
        $statement->execute($data);
    }
    static function get_short_names($pdo)
    {
        $query = 'SELECT `short_name` FROM `school_staff_list`';
        $statement = $pdo->prepare($query);
        $statement->execute();
        return $statement->fetchAll();
    }
}