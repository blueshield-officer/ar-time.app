<?php

namespace App\Controllers\admin;

use App\Services\Controller;

class Admin extends Controller
{
    public function execute(){
        $this->view('pages/admin.tpl');
    }
}