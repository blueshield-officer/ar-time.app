<?php

namespace App\Services;

use App\Services\Accounts\Actions;

class Session
{
    private $DBC;
    public function __construct()
    {
        session_start();
        $this->DBC = DBC::getInstance();
        // check cookies
        $user_id = $_COOKIE['user_id'] ?? '';
        $token = $_COOKIE['keep_me_login'] ?? '';
        if ($user_id !== '' && $token !== ''){
            $result = $this->DBC->fetch($this->DBC->query('SELECT * FROM users_sessions WHERE user_id = ? AND token_cookie = ?', [$user_id, $token]));
            if ($result !== false){
                self::setLogin(true);
            }
            else {
                self::setLogin(false);
                $this->destroyCookies();
            }
        }
        else {
            if ($user_id !== $token){
                $this->destroyCookies();
            }
            $this->setLogin(false);
        }
    }
    private function destroyCookies(){
        setcookie('user_id', '', time(), '/');
        setcookie('keep_me_login', '', time(), '/');
    }
    static public function setLogin($bool)
    {
        $_SESSION['logged_in'] = $bool;
    }
    static public function setAdmin($bool)
    {
        $_SESSION['admin'] = $bool;
    }
    static public function isLogedIn(){
        if (isset($_SESSION['logged_in'])){
            if ($_SESSION['logged_in'] == true){
                return true;
            }
            return false;
        }
        return false;
    }
    static public function isAdmin(){
        if (isset($_SESSION['admin'])){
            if ($_SESSION['admin'] === true){
                return true;
            }
            return false;
        }
        return false;
    }
}