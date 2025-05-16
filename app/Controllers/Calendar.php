<?php

namespace App\Controllers;

use App\Services\Controller;
use App\Services\Data_Extraction\SQL\Schedule_Rarity\Loader;
use App\Services\SmartyVars;

class Calendar extends Controller
{
    public function execute($date = null)
    {
        $smartyVars = new SmartyVars($date, 'MY');
        $smartyVars->loadBase();
        $smartyVars->loadPages();

        $smartyVars->loadDayInfo(true);
        $smartyVars->loadEvents(true);
        $smartyVars->loadSports(true);
        $smartyVars->loadDayOffs(true);
        $smartyVars->loadRetreats(true);

        $this->view('pages/calendar.tpl',
            [

            ] + $smartyVars->getVarsArray());
    }
}