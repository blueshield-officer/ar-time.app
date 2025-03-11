<?php

namespace App\Controllers;

use App\Services\CacheOperator;
use App\Services\Controller;
use App\Services\DBC;
use App\Services\RedisOperator;

class Roadmap extends Controller
{
    public function execute()
    {
        $DBC = DBC::getInstance();
        $redis = RedisOperator::getInstance();
        $cacheOperator = new CacheOperator($DBC, $redis);

        $infoRoadmap = $cacheOperator->fetch('info_roadmap');
        if ($infoRoadmap !== '') {
            $infoRoadmap = json_decode($infoRoadmap, true);
        }

        // data
        $this->view('pages/roadmap.tpl', [
            'info_roadmap' => $infoRoadmap,
        ]);
    }
}