<?php

namespace App\Controllers\server;

use App\Services\Controller;
use App\Services\ARHS_Data_Extractor\Staff\Loader;

class Staff_Update extends Controller
{
    public function execute(){
        Loader::load();
    }
}