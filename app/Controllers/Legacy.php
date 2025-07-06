<?php

namespace App\Controllers;

use App\Services\Controller;
use App\Services\Session;
use App\Services\SmartyVars;

class Legacy extends Controller
{
    public function execute()
    {
        $smartyVars = new SmartyVars();
        $smartyVars->setNextDateToLocalDate();

        $smartyVars->loadDayInfo();
        $smartyVars->loadDayOffs();

        $smartyVars->loadAbsentAdditional();
        $smartyVars->loadAbsentCombined();
        $smartyVars->loadSchoolFaculty();
        if (Session::isLogedIn()){
            $smartyVars->loadAdditionalAccountInfo();
            $smartyVars->loadUserSchoolFaculty();
            $smartyVars->loadSessionParams();
        }

        $this->view('pages/legacy.tpl',
            $smartyVars->getVarsArray()
        );
    }
}