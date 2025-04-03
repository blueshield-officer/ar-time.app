<?php

namespace App\Controllers;

use App\Services\CacheOperator;
use App\Services\Controller;
use App\Services\DBC;
use App\Services\RedisOperator;
use App\Services\SmartyVars;


class Roadmap extends Controller
{
    public function execute()
    {
        $smartyVars = new SmartyVars();
        $smartyVars->loadBase();
        $smartyVars->loadPages();

        $smartyVars->loadRoadMap();

        // data
        $this->view('pages/roadmap.tpl', []
            + $smartyVars->getVarsArray()
        );
    }
}