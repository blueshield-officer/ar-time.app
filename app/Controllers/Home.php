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
use Minishlink\WebPush\VAPID;
use Predis\Client;
use Redis;
use Smarty;

class Home extends Controller
{
    public function execute($date = null)
    {
        $smartyVars = new SmartyVars($date);
        $smartyVars->loadBase();
        $smartyVars->loadPages();

        $smartyVars->loadDayInfo();
        $smartyVars->loadEvents();
        $smartyVars->loadSports();
        $smartyVars->loadDayOffs();
        $smartyVars->loadRetreats();


        if (Session::isLogedIn()){
            $smartyVars->loadAbsentCombined();

            $smartyVars->loadSchoolFaculty();

            $smartyVars->loadUserSchoolFaculty();
        }

        $this->view('pages/home.tpl',
            [

            ] + $smartyVars->getVarsArray());


    }
}