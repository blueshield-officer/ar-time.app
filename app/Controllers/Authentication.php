<?php

namespace App\Controllers;

use App\Services\Accounts\Checks;
use App\Services\Accounts\google\Redirect;
use App\Services\Controller;
use App\Services\DBC;

class Authentication extends Controller
{
    private function includer() : void
    {
        $DBC = DBC::getInstance();
        Redirect::redirect();
        Checks::check_account_status($DBC);
    }
    public function login(){
        $this->includer();
        $this->view('pages/authentication.tpl', ['method' => 'login']);
    }
    public function register(){
        $this->includer();
        $this->view('pages/authentication.tpl', ['method' => 'register']);
    }
    public function login_google(){
        echo 'login_google!';
    }
}