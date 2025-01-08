<?php

namespace App\Controllers;

use App\Services\Controller;

class Error extends Controller
{
    public function execute($code)
    {
        $this->view('error.tpl', ['ERROR' => $code]);
    }
}