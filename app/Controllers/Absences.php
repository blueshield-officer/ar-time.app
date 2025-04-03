<?php

namespace App\Controllers;

use App\Services\Controller;
use App\Services\Data_Extraction\SQL\Schedule_Rarity\Loader;
use App\Services\SmartyVars;

class Absences extends Controller
{
    public function execute($date = null)
    {
        $smartyVars = new SmartyVars();
        $smartyVars->loadBase($date);
        $smartyVars->loadPages();

        $this->view('pages/absences.tpl',
            [

            ] + $smartyVars->getVarsArray());
    }
}