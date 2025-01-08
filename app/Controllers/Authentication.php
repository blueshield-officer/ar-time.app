<?php

namespace App\Controllers;

use App\Services\auth\Checks;
use App\Services\auth\google\Redirect;
use App\Services\Controller;

class Authentication extends Controller
{
    public function login(){
        Redirect::redirect();
        Checks::check_account_status();
        $this->view('pages/authentication.tpl', ['method' => 'login']);
    }
    public function register(){
        Redirect::redirect();
        Checks::check_account_status();
        $this->view('pages/authentication.tpl', ['method' => 'register']);
    }
    public function login_google(){
        echo 'login_google!';
    }
}