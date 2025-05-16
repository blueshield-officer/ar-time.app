<?php

namespace App\Controllers;

use App\Services\CacheOperator;
use App\Services\Controller;
use App\Services\DBC;
use App\Services\RedisOperator;
use App\Services\SmartyVars;


class Development extends Controller
{
    public function execute()
    {
        $smartyVars = new SmartyVars();
        $smartyVars->loadBase();
        $smartyVars->loadPages();

        $smartyVars->loadRoadMap();
        $smartyVars->loadChangelog();

        // data
        $this->view('pages/development.tpl', []
            + $smartyVars->getVarsArray()
        );
    }
}