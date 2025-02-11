<?php

namespace App\Controllers\server;

use App\Services\ARHS_Data_Extractor\Schedule\Loader;
use App\Services\Controller;

class ScheduleDefault extends Controller
{
    public function execute()
    {
        Loader::load();
    }
}