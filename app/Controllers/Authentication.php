<?php

namespace App\Controllers;

use App\Services\Accounts\Authentification;
use App\Services\Accounts\Checks;
use App\Services\Accounts\google\Redirect;
use App\Services\Controller;
use App\Services\DBC;
use App\Services\Session;
use App\Services\SmartyVars;

class Authentication extends Controller
{
    private function getMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
    }
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
        $smartyVars = new SmartyVars();
        if ($this->getMethod() === 'GET'){
            $authError = Session::getAuthError();
            $this->includer();
            $this->view('pages/authentication.tpl', [
                'method' => 'login',
                'emailError' => $authError['email'],
                'passwordError' => $authError['password'],
                'password2Error' => $authError['password2'],
            ] + $smartyVars->getVarsArray());
            Session::clearAuthError();
        }
        else {
            $auth = new Authentification();
            $auth->process();
        }
    }

    public function signup()
    {
        $smartyVars = new SmartyVars();
        if ($this->getMethod() === 'GET'){
            $authError = Session::getAuthError();
            $this->includer();
            $this->view('pages/authentication.tpl', [
                'method' => 'sign up',
                'emailError' => $authError['email'],
                'passwordError' => $authError['password'],
                'password2Error' => $authError['password2'],
            ] + $smartyVars->getVarsArray());
            Session::clearAuthError();
        }
        else {
            $auth = new Authentification();
            $auth->process();
        }
    }

    public function login_google()
    {
        echo 'login_google!';
    }
}