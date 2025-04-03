<?php

namespace App\Controllers;

use App\Services\CacheOperator;
use App\Services\Controller;
use App\Services\DBC;
use App\Services\RedisOperator;
use App\Services\SmartyVars;

class Schedules extends Controller
{
    public function execute()
    {
        $smartyVars = new SmartyVars();
        $smartyVars->loadBase();
        $smartyVars->loadPages();
        $smartyVars->loadSchedules();

        // data
        $this->view('pages/schedules.tpl', []
            + $smartyVars->getVarsArray()
        );
    }
}