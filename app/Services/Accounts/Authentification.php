<?php

namespace App\Services\Accounts;

use App\Services\Accounts\Actions;
use App\Services\Accounts\Checks;
use App\Services\Accounts\SecurityOperator;
use App\Services\DBC;
use App\Services\Session;

class Authentification
{
    private $DBC;
    private $email;
    private $password;
    private $password2;
    private $method;

    public function __construct(){
        $this->DBC = DBC::getInstance();
        $this->email = $_POST['email'] ?? '';
        $this->password = $_POST['password'] ?? '';
        $this->password2 = $_POST['password2'] ?? '';
        $this->method = $_POST['method'] ?? '';
    }

    private function redirect(){
        header('Location: /' . $this->method);
        exit();
    }
    public function process(){
        if ($_SERVER['REQUEST_METHOD'] === 'POST'){
            // PASSWORD CHECK !!!!!!
            $password_valid = Checks::password_valid($this->email, $this->password, $this->method);
            if ($password_valid !== true){
                Session::setAuthError('password', 'Password invalid');
                $this->redirect();
            }
            if ($this->method === 'signup'){
                if ($this->password2 !== $this->password){
                    Session::setAuthError('password', '');
                    Session::setAuthError('password2', 'Passwords do not match');
                    $this->redirect();
                }
            }

            // EMAIL CHECK !!!!!!
            $email_valid = Checks::email_valid($this->email);
            if ($email_valid !== true){
                echo $email_valid;
                die();
            }
            // is email in db and etc
            $email_usable = Checks::email_usable($this->DBC, $this->email, $this->method);
            if ($email_usable !== true){
                Session::setAuthError('email', $email_usable);
                $this->redirect();
            }

            if ($this->method === 'login'){
                $check_login = Checks::check_login($this->DBC, $this->email, $this->password);
                if ($check_login !== true){
                    Session::setAuthError('email', '');
                    Session::setAuthError('password', $check_login);
                    header('Location: /' . $this->method);                }
                Actions::login($this->DBC, $this->email);
                echo 'You are logged in!';
                die();
            }
            else {
                // here must go register actions
                Actions::register($this->DBC, $this->email, SecurityOperator::password_hash($this->password));
                echo 'you are registered, now please login!';
                die();
            }
        }
        else {
            $this->redirect();
        }
    }
}