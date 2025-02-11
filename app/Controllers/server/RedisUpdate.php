<?php

namespace App\Controllers\server;

use App\Services\DBC;
use App\Services\RedisOperator;

class RedisUpdate
{
    public function execute(){
        $DBC = DBC::getInstance();
        $redis = RedisOperator::getInstance();

        // calendar_schedules
        $query = $DBC->query('SELECT * FROM `calendar_schedules`');
    }
}