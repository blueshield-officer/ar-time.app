<?php

namespace App\Controllers\admin;

use App\Services\Controller;
use App\Services\SmartyVars;

class CustomScheduleLoader extends Controller
{
    public function execute()
    {
        $smartyVars = new SmartyVars();

        // data
        $this->view('pages/scheduleUpload.tpl',
            $smartyVars->getVarsArray()
        );
    }
}