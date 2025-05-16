<?php

namespace App\Controllers;

use App\Services\CacheOperator;
use App\Services\Controller;
use App\Services\DBC;
use App\Services\RedisOperator;
use App\Services\Session;
use App\Services\SmartyVars;

class Settings extends Controller
{
    public function execute()
    {
        $smartyVars = new SmartyVars();
        $smartyVars->loadBase();
        $smartyVars->loadPages();
        $smartyVars->loadSchedules();

        $smartyVars->loadSettings();

        if (Session::isLogedIn()){
            $smartyVars->loadSchoolFaculty();
            $smartyVars->loadUserSchoolFaculty();
            $smartyVars->loadAdditionalAccountInfo();
        }

        // data
        $this->view('pages/settings.tpl', []
            + $smartyVars->getVarsArray()
        );
    }
}