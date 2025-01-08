<?php

namespace App\Controllers;

use App\Services\Controller;
use App\Services\Data_Extraction\SQL\Calendar\Loader;
use Predis\Client;

class HomePage extends Controller
{
    public function execute()
    {
        $redis = new Client([
            'host' => 'Redis-7.2'
        ]);
        $redis->set('foo', null);
        $value = $redis->get('2532');
        var_dump($value);

//        var_dump(RedisCache::get(1));
//        echo '<pre>';
//        var_dump(Loader::load());
//        echo '</pre>';
//        $this->view('pages/home_page.tpl');
    }
}