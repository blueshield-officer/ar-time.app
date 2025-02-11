<?php

namespace App\Controllers;

use App\Services\Controller;
use App\Services\Data_Extraction\SQL\Calendar\Loader;
use App\Services\Session;
use Predis\Client;

class HomePage extends Controller
{
    public function execute()
    {
        $this->view('pages/home_page.tpl');
        echo 'Logged In: ' . Session::isLogedIn();
        echo '<br>';
        echo 'Admin: ' . Session::isAdmin();
    }
}