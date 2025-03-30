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

class Home extends Controller
{
    public function execute($date = null)
    {
//        $DBC = DBC::getInstance();
//        $redis = RedisOperator::getInstance();
//        $test = new CacheOperator($DBC, $redis);
//        echo '<pre>';
//        var_dump($test->fetch('calendar_schedules', '2025-12', false));
//        echo '</pre>';
//        die();

        $smartyVars = new SmartyVars($date);
        $smartyVars->loadBase();

        $smartyVars->loadDayInfo();
        $smartyVars->loadEvents();
        $smartyVars->loadSports();
        $smartyVars->loadDayOff();

        $smartyVars->loadAbsentAdditional();
        $smartyVars->loadAbsentPeople();
        $smartyVars->loadRoomChanges();


        $this->view('pages/home.tpl',
            [

            ] + $smartyVars->getVarsArray());

//        echo 'Logged In: ' . Session::isLogedIn();
//        echo '<br>';
//        echo 'Admin: ' . Session::isAdmin();
    }
}