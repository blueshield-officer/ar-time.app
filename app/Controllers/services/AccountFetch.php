<?php

namespace App\Controllers\services;

use App\Services\Controller;
use App\Services\DBC;
use App\Services\Session;

class AccountFetch extends Controller
{
    function execute()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $DBC = DBC::getInstance();
            $userData = Session::getAccountSessionData();



            $data = json_decode(file_get_contents('php://input'), true);
            header('Content-Type: application/json');
            echo json_encode($data);
            exit();
        }
    }
}