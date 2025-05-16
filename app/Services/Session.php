<?php

namespace App\Services;

use App\Services\Accounts\Actions;

class Session
{
    private $DBC;
    private $secureCookie;
    private static $authErrorTypes = ['email','password','password2'];
    public function __construct($DBC)
    {
        $this->DBC = $DBC;

        $this->secureCookie = new SecureCookie();

        session_start([
                'cookie_httponly' => true,
                'cookie_secure' => true,
                'use_strict_mode' => true
            ]
        );

        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = Utils::generateRandomSymbols(64);
        }
    }

    public function loadDate()
    {
        $tries = 0;
        $date = (new \DateTime('now'));
        $_SESSION['date'] = $date->format('Y-m-d');
        while (is_null($_SESSION['date']) && $tries < 10) {
            $_SESSION['date'] = $date->format('Y-m-d');
            usleep(100);
            $tries++;
        }
        if (is_null(self::getLocalDate())) {
            self::setLocalDate($date);
        }
    }

    static public function setLocalDate($date)
    {
        $_SESSION['localDate'] = $date;
    }

    static public function getLocalDate(){
        if (isset($_SESSION['localDate'])) {
            return $_SESSION['localDate'];
        }
        return null;
    }

    public function cookieLogin(){
        $this->userId = $_COOKIE['user_id'] ?? null;
        $this->authToken = $_COOKIE['auth_token'] ?? null;

        if (!is_null($this->userId) && !is_null($this->authToken)){
            $userSession = $this->DBC->fetch(
                $this->DBC->query(
                    'SELECT * FROM users_sessions WHERE user_id = ? AND token_cookie = ?', [$this->userId,  $this->authToken]
                )
            );
            if ($userSession !== false){
                $userData = $this->DBC->fetch(
                    $this->DBC->query(
                        'SELECT `email`, `name` FROM users_accounts WHERE user_id = ?', [$this->userId]
                    )
                );
                if ($userData !== false){
                    $this->setLogin(true, $userData['name'], $userData['email'], $this->userId, $this->authToken);
                    return;
                }
            }
        }

        $this->setLogin(false);
        $this->secureCookie->deleteCookie('user_id');
        $this->secureCookie->deleteCookie('auth_token');
    }

    private function setLogin($bool, $name = null, $email = null, $user_id = null, $token = null)
    {
        $_SESSION['loggedIn'] = $bool;
        $_SESSION['userId'] = $user_id;

        $_SESSION['userName'] = $name;

        $_SESSION['userEmail'] = $email;

        $_SESSION['token'] = $token;
    }

    private function setAdmin($bool)
    {
        $_SESSION['admin'] = $bool;
    }

    static function setAwaitCode($bool, $email = null)
    {
        if ($bool === true){
            self::setTempVar('awaitCode', [
                'status' => $bool,
                'email' => $email,
                'leftTries' => 3,
            ]);
        }
        else {
            self::deleteTempVar('awaitCode');
        }
    }

    static public function isAwaitCode(){
        $codeInfo = self::getTempVar('awaitCode');
        if (is_null($codeInfo)){
            return false;
        }
        return $codeInfo['status'];
    }

    static public function getAwaitCodeInfo(){
        $codeInfo = self::getTempVar('awaitCode');
        if ($codeInfo['leftTries'] <= 0){
           self::setAwaitCode(false);
           return null;
        }
        if (is_null($codeInfo)) return null;
        return [
            'email' => $codeInfo['email'],
            'expires' => $codeInfo['expires'] - time(),
            'leftTries' => $codeInfo['leftTries'],
        ];
    }

    static public function setTempVar($index, $value, $seconds = 300){
        $_SESSION['tempVars'][$index]['value'] = $value;
        $_SESSION['tempVars'][$index]['expires'] = time() + $seconds;
    }
    static public function getTempVar($index){
        if (isset($_SESSION['tempVars'][$index])){
            if ($_SESSION['tempVars'][$index]['expires'] < time()){
                unset($_SESSION['tempVars'][$index]);
            }
            else {
                return $_SESSION['tempVars'][$index]['value'] += ['expires' => $_SESSION['tempVars'][$index]['expires']];
            }
        }
        return null;
    }
    static public function deleteTempVar($index){
        unset($_SESSION['tempVars'][$index]);
    }

    static public function isLogedIn(){
        if (isset($_SESSION['loggedIn'])){
            if ($_SESSION['loggedIn'] === true){
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



    static public function setFormViolation($formType, $type, $str){
        $_SESSION['formViolations'][$formType][$type] = $str;
    }
    static public function getAuthError($formType){
        $arrayToReturn = [];
        if (isset($_SESSION['formViolations'][$formType])){
            foreach ($_SESSION['formViolations'][$formType] as $index => $type){
                if (isset($_SESSION['formViolations'][$formType][$index])){
                    $arrayToReturn[$index] = $type;
                }
                else {
                    $arrayToReturn[$index] = null;
                }
            }
        }
        return $arrayToReturn;
    }
    static public function clearAuthError($formType){
        if (isset($_SESSION['formViolations'][$formType])) {
            foreach ($_SESSION['formViolations'][$formType] as $index => $type) {
                $_SESSION['formViolations'][$formType][$index] = null;
            }
        }
    }
    static public function getAccountSessionData(){
        return [
            'token' => $_SESSION['token'],
            'id' => $_SESSION['userId'],
        ];
    }
}