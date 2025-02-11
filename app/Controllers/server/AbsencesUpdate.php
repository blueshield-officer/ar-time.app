<?php

namespace App\Controllers\server;

use App\Services\Controller;
use App\Services\ARHS_Data_Extractor\Absents\Loader;

class AbsencesUpdate extends Controller
{
    public function execute()
    {
        $this->view('debug.tpl', Loader::load());
    }
}