<?php

namespace App\Services;

class DatabaseRequests
{
    private $DBC;
    public function __construct($DBC)
    {
        $this->DBC = $DBC;
    }

    public function schoolStaffInsertPerson($person)
    {
        $honorific = $person['honorific'] ?? null;
        $first_name = $person['first_name'] ?? null;
        $last_name = $person['last_name'] ?? null;
        $short_name = $person['short_name'] ?? null;
        $email = $person['email'] ?? null;
        $room = $person['room'] ?? null;
        $position = $person['position'] ?? null;
        $position_full = $person['position_full'] ?? null;


        $query = 'INSERT INTO `school_staff_list` (`teacher_id`, `honorific`, `first_name`, `last_name`, `short_name`, `email`, `room`, `position`, `position_full`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $result = null;
        while ($result !== false){
            $id = random_int(1000, 9999);
            $result = $this->DBC->fetch($this->DBC->query('SELECT * FROM `school_staff_list` WHERE `teacher_id` = ?', [$id]));
        }
        $params = [$id, $honorific, $first_name, $last_name, $short_name, $email, $room, $position, $position_full];
        return $this->DBC->query($query, $params);
    }
}