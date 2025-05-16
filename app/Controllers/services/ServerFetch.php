<?php

namespace App\Controllers\services;

use App\Services\CacheOperator;
use App\Services\Controller;
use App\Services\DBC;
use App\Services\FetchHandler;
use App\Services\RedisOperator;
use App\Services\Session;
use App\Services\Utils;

class ServerFetch extends Controller
{
    public function default($type){
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $DBC = DBC::getInstance();
            $redis = RedisOperator::getInstance();
            $json = file_get_contents('php://input');
//            $json = json_encode([
//                'title' => 'iAmAlive',
//                'content' => null
//            ]);
            $fetchHandler = new FetchHandler($DBC, $redis, $json, $type);

            $fetchHandler->process();
        }
    }
    public function fetch()
    {
        $this->default('account');
    }
    public function request(){
        $this->default('none');

    }

//            error_reporting(E_ALL); // Report all errors
//            ini_set('display_errors', 1);
//            $DBC = DBC::getInstance();
//            $userData = Session::getAccountSessionData();

//            $data = json_decode(file_get_contents('php://input'), true);
//            $type = $data['type'];
//            $content = $data['content'];
//
//            if($type === 'notifyOn' || $type === 'notifyOff'){
//
//                  $query = 'UPDATE users_sessions SET `token_notification` = ?, `notify` = ? WHERE `user_id` = ? AND `token_cookie` = ?', [$content, ($type === 'notifyOn') ? 1 : 0, $userData['id'], $userData['token']]
//                );
//            }
//            $DBC->query($query);
//
//            header('Content-Type: application/json');
//            echo json_encode($data);
//            exit();

}