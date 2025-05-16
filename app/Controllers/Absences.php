<?php

namespace App\Controllers;

use App\Services\Controller;
use App\Services\Data_Extraction\SQL\Schedule_Rarity\Loader;
use App\Services\Session;
use App\Services\SmartyVars;

class Absences extends Controller
{
    public function execute($date = null)
    {
        $smartyVars = new SmartyVars($date, 'MDY');
        $smartyVars->loadBase();
        $smartyVars->loadPages();

        $smartyVars->loadAbsentAdditional();
        $smartyVars->loadAbsentCombined();

        $smartyVars->loadSchoolFaculty();

        if (Session::isLogedIn()){
            $smartyVars->loadUserSchoolFaculty();
        }

        $this->view('pages/absences.tpl',
            [

            ] + $smartyVars->getVarsArray());
    }
}