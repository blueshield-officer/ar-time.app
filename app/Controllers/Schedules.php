<?php

namespace App\Controllers;

use App\Services\CacheOperator;
use App\Services\Controller;
use App\Services\DBC;
use App\Services\RedisOperator;

class Schedules extends Controller
{
    public function execute()
    {
        $DBC = DBC::getInstance();
        $redis = RedisOperator::getInstance();
        $cacheOperator = new CacheOperator($DBC, $redis);

        $schedulesOfficial = $cacheOperator->fetch('schedule_default');
        if ($schedulesOfficial !== '') {
            $schedulesOfficial = json_decode($schedulesOfficial, true);
        }
        $schedulesNonofficial = $cacheOperator->fetch('schedule_custom');
        if ($schedulesNonofficial !== '') {
            $schedulesNonofficial = json_decode($schedulesNonofficial, true);
        }


        // data
        $this->view('pages/schedules.tpl', [
            'schedules_official' => $schedulesOfficial,
            'schedules_nonofficial' => $schedulesNonofficial,
        ]);
    }
}