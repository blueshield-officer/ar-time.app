<?php

namespace App\Controllers;

use App\Services\CacheOperator;
use App\Services\Controller;
use App\Services\DBC;
use App\Services\RedisOperator;
use App\Services\SmartyVars;

class About extends Controller
{
    public function execute()
    {
        $smartyVars = new SmartyVars();

        // data
        $this->view('pages/about.tpl', []
            + $smartyVars->getVarsArray()
        );
    }
}