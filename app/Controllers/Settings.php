<?php

namespace App\Controllers;

use App\Services\CacheOperator;
use App\Services\Controller;
use App\Services\DBC;
use App\Services\RedisOperator;
use App\Services\SmartyVars;
use WhichBrowser\Parser;

class Settings extends Controller
{
    public function execute()
    {
        $smartyVars = new SmartyVars();
        $smartyVars->loadBase();
        $smartyVars->loadPages();
        $smartyVars->loadSchedules();

        $smartyVars->loadSettings();

        // data
        $this->view('pages/settings.tpl', []
            + $smartyVars->getVarsArray()
        );
    }
}