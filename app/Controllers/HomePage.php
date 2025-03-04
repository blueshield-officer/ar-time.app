<?php

namespace App\Controllers;

use App\Services\CacheOperator;
use App\Services\Controller;
use App\Services\Data_Extraction\SQL\Calendar\Loader;
use App\Services\DBC;
use App\Services\RedisOperator;
use App\Services\Session;
use Predis\Client;

class HomePage extends Controller
{
    public function execute()
    {
        //
        $DBC = DBC::getInstance();
        $redis = RedisOperator::getInstance();
        $cacheOperator = new CacheOperator($DBC, $redis);


        // data
        $this->view('pages/home.tpl', [
            'calendar_schedules' => $cacheOperator->fetch('calendar_schedules', $_SESSION['date']),
            'calendar_sports' => $cacheOperator->fetch('calendar_sports', $_SESSION['date']),
            'calendar_events' => $cacheOperator->fetch('calendar_events', $_SESSION['date']),
            'calendar_day_off' => $cacheOperator->fetch('calendar_day_off', $_SESSION['date']),
        ]);


//        echo 'Logged In: ' . Session::isLogedIn();
//        echo '<br>';
//        echo 'Admin: ' . Session::isAdmin();
    }
}