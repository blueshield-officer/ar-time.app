<?php

namespace App\Controllers;

use App\Services\CacheOperator;
use App\Services\Controller;
use App\Services\Data_Extraction\SQL\Calendar\Loader;
use App\Services\DBC;
use App\Services\Utils;
use App\Services\RedisOperator;
use App\Services\Session;
use App\Services\SmartyVars;
use Predis\Client;
use Redis;
use Smarty;

class Countdowns extends Controller
{
    public function execute()
    {
        $smartyVars = new SmartyVars();
        $smartyVars->loadBase();
        $smartyVars->loadPages();

        $smartyVars->loadNextDayOff();
        $smartyVars->loadNextSchedules();
        $smartyVars->loadHolidays();
        $smartyVars->loadAcademics();


        $this->view('pages/countdowns.tpl',
            [

            ] + $smartyVars->getVarsArray());
    }
}