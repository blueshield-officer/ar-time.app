<?php

namespace App\Controllers;

use App\Services\Controller;
use App\Services\SmartyVars;

class Legacy extends Controller
{
    public function execute()
    {
        $smartyVars = new SmartyVars();

        $this->view('pages/legacy.tpl',
            $smartyVars->getVarsArray()
        );
    }
}