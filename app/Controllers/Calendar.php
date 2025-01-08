<?php

namespace App\Controllers;

use App\Services\Controller;
use App\Services\Data_Extraction\SQL\Schedule_Rarity\Loader;

class Calendar extends Controller
{
    public function execute()
    {
        echo '<pre>';
        var_dump(Loader::load());
        echo '</pre>';
    }
}