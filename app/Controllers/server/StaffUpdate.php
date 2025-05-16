<?php

namespace App\Controllers\server;

use App\Services\Controller;
use App\Services\ARHS_Data_Extractor\Staff\StaffManager;
use App\Services\DBC;

class StaffUpdate extends Controller
{
    public function execute(){
        $DBC = DBC::getInstance();

        $staffManager = new StaffManager($DBC);

        $staffManager->loadHTMLPage();

        $staffManager->loadPeople();
    }
}