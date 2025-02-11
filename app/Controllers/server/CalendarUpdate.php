<?php

namespace App\Controllers\server;

use App\Services\Controller;
use App\Services\ARHS_Data_Extractor\Calendar;
class CalendarUpdate extends Controller
{
    public function execute()
    {
        Calendar\Loader::load();
    }
}