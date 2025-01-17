<?php

namespace App\Services\Accounts;

use App\Services\Accounts\Actions;
use App\Services\Accounts\Checks;
use App\Services\Accounts\SecurityOperator;
use App\Services\DBC;

class Authentification
{
    static public function auth(){
        // TODO перенести в норм класс как страницу
        if ($_SERVER['REQUEST_METHOD'] === 'POST'){
            // METHOD CHECK
            if (isset($_POST['method']) && ($_POST['method'] === 'login' || $_POST['method'] === 'register')){
                $method = $_POST['method'];
            }
            else {
                echo 'Method not filled in';
                die();
            }

            // DATA RECORD
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $keep_me_login = $_POST['keep_me_login'] ?? '';

            // PASSWORD CHECK !!!!!!
            $password_valid = Checks::password_valid($email, $password, $method);
            if ($password_valid !== true){
                echo $password_valid;
                die();
            }
            if ($method === 'register'){
                $password_repeat = $_POST['password_repeat'] ?? '';
                if ($password_repeat !== $password){
                    echo 'Passwords do not match';
                    die();
                }
            }

            // EMAIL CHECK !!!!!!
            $email_valid = Checks::email_valid($email);
            if ($email_valid !== true){
                echo $email_valid;
                die();
            }
            $DBC = DBC::getInstance();
            // is email in db and etc
            $email_usable = Checks::email_usable($DBC, $email, $method);
            if ($email_usable !== true){
                echo $email_usable;
                die();
            }

            if ($method === 'login'){
                $check_login = Checks::check_login($DBC, $email, $password);
                if ($check_login !== true){
                    echo $check_login;
                    die();
                }
                Actions::login($DBC, $email, $keep_me_login);
                echo 'You are logged in!';
                die();
            }
            else {
                // here must go register actions
                Actions::register($DBC, $email, SecurityOperator::password_hash($password));
                echo 'you are registered, now please login!';
                die();
            }
        }
        else {
            header('Location: /');
        }
    }
}