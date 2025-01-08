<?php

namespace App\Services\Data_Extraction\SQL\Schedule_Rarity;

class DataExtractor
{
    static public function extract_schedule($pdo)
    {
        $query = 'SELECT schedule FROM schedule_calendar_test WHERE school = 1';
        $stmt = $pdo->prepare($query);
        $stmt->execute();
        $result = $stmt->fetchAll();
        return $result;
    }
}