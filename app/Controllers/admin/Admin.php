<?php

namespace App\Controllers\admin;

use App\Services\Controller;
use App\Services\DBC;

class Admin extends Controller
{
    public function execute()
    {
        $DBC = DBC::getInstance();
        $modules = $DBC->fetchAll($DBC->query('SELECT * FROM `modules`'));
        $this->view('pages/admin.tpl', ['modules' => $modules]);
    }
}