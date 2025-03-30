<?php

namespace App\Services;

use App\Services\Accounts\Actions;

class Session
{
    private $DBC;
    private static $authErrorTypes = ['email','password','password2'];
    public function __construct()
    {
        session_start();
        $this->DBC = DBC::getInstance();
        // check cookies
        $user_id = $_COOKIE['user_id'] ?? '';
        $token = $_COOKIE['token'] ?? '';
        if ($user_id !== '' && $token !== ''){
            $result = $this->DBC->fetch($this->DBC->query('SELECT * FROM users_sessions WHERE user_id = ? AND token_cookie = ?', [$user_id, $token]));
            if ($result !== false){
                $userInfo = $this->DBC->fetch($this->DBC->query('SELECT `email`, `first_name`, `last_name` FROM users_accounts WHERE user_id = ?', [$user_id]));
                $name = null;
                $email = null;
                if (isset($userInfo['first_name']) &&  isset($userInfo['last_name'])){
                    $name = $userInfo['first_name'] . ' ' . $userInfo['last_name'];
                }
                if (isset($userInfo['email'])){
                    $email = $userInfo['email'];
                }
                self::setLogin(true, $name, $email);
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

        // setting data
        if (is_null($_SESSION['date'])){
            $_SESSION['date'] = (new \DateTime('now'))->format('Y-m-d');
        }
    }
    private function destroyCookies(){
        setcookie('user_id', '', time(), '/');
        setcookie('token', '', time(), '/');
    }
    static public function setLogin($bool, $name = null, $email = null)
    {
        $_SESSION['logged_in'] = $bool;
        if ($bool === true){
            $_SESSION['name'] = $name;
            $_SESSION['email'] = $email;
        }
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


    static public function setAuthError($type, $str){
        if (!in_array($type, Session::$authErrorTypes)){
            echo 'WRONG AUTH ERROR TYPE';
            die();
        }
        $_SESSION['auth_error'][$type] = $str;
    }
    static public function getAuthError(){
        $arrayToReturn = [];
        foreach (Session::$authErrorTypes as $type){
            if (isset($_SESSION['auth_error'][$type])){
                $arrayToReturn[$type] = $_SESSION['auth_error'][$type];
            }
            else {
                $arrayToReturn[$type] = null;
            }
        }
        return $arrayToReturn;
    }
    static public function clearAuthError(){
        foreach (Session::$authErrorTypes as $type){
            $_SESSION['auth_error'][$type] = null;
        }
    }

    static public function setLocaleDate($date)
    {
        $_SESSION['date'] = $date;
    }
}