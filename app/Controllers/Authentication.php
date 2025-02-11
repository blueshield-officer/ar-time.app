<?php

namespace App\Controllers;

use App\Services\Accounts\Checks;
use App\Services\Accounts\google\Redirect;
use App\Services\Controller;
use App\Services\DBC;
use App\Services\Session;

class Authentication extends Controller
{
    private function includer(): void
    {
        if (Session::isLogedIn()) {
            header('Location: /');
            exit();
        }
        Redirect::redirect();
    }

    public function login()
    {
        $this->includer();
        $this->view('pages/authentication.tpl', ['method' => 'login']);
    }

    public function register()
    {
        $this->includer();
        $this->view('pages/authentication.tpl', ['method' => 'register']);
    }

    public function login_google()
    {
        echo 'login_google!';
    }
}