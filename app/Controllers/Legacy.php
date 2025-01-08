<?php

namespace App\Controllers;

use App\Services\Controller;

class Legacy extends Controller
{
    public function execute()
    {
        $this->view('pages/legacy.tpl');
    }
}