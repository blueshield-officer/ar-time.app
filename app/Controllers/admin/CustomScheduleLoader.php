<?php

namespace App\Controllers\admin;

use App\Services\Controller;
class CustomScheduleLoader extends Controller
{
    public function execute()
    {
        $this->view('pages/admin/schedule.tpl');
    }
}