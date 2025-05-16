<?php

namespace App\Services\Accounts\validations;

use App\Services\Accounts\Accounts;
use App\Services\DBC;
use App\Services\Session;
use App\Services\Utils;

class AuthValidationDefault
{
    private $DBC;
    private $email;
    private $password;
    private $method;
    private $salt;
    private $minSecondsBetweenAuth = 3;

    public function __construct($DBC, $method){
        $this->DBC = $DBC;
        $this->method = $method;
    }

    public function process(){
        $formValidation = new FormValidation($this->method);
        $form = $formValidation->valid();

        if ($form !== false){
            $this->email = $form['email'];
            $this->password = $form['password'];

            if ($this->method === 'sign up'){
                $result = $this->DBC->fetch(
                    $this->DBC->query(
                        'SELECT * FROM users_registration_codes WHERE email = ? AND timestamp_expires >= ? AND verified = ? LIMIT 1', [$this->email, date('Y-m-d H:i:s'), 0]
                    )
                );
                if ($result !== false){
                    return $this->addViolation('general', 'This emails is already going throw verification process. Please try again later.');
                }
            }
            if ($this->checkLogAuthReadiness() === false){
                return $this->addViolation('general', 'Not that fast, please try again in a few seconds.');
            }

            $this->log();

            $emailValidity = $this->validateEmail();
            if ($emailValidity !== true){
                return false;
            }

            if ($this->method === 'login'){
                $userID = Accounts::getAccountID($this->DBC, $this->email);
                $passwordValidity = AuthValidationChecks::validatePassword($this->DBC, $userID, $this->password);
                if ($passwordValidity !== true){
                    return $this->addViolation('general', 'Invalid email or password. Please try again.');
                }
            };

            return true;
        }
        else {
            return $this->addViolation('general', 'Invalid form.');
        }
    }

    public function getEmail(){
        return $this->email;
    }

    public function getPassword()
    {
        return $this->password;
    }

    private function validateEmail(){
        $result = $this->DBC->fetch(
            $this->DBC->query(
                'SELECT * FROM users_accounts WHERE email = ?', [$this->email]
            )
        );

        if ($this->method === 'login' && $result === false){
            return $this->addViolation('general', 'Invalid email or password. Please try again.');
        }
        if ($this->method === 'sign up' && $result !== false) {
            return $this->addViolation('email', 'An account with this email address already exists. Please sign in or use YOURS email.');
        }
        return true;
    }

    private function addViolation($field, $error)
    {
        Session::setFormViolation($this->method, $field, $error);
        return false;
    }

    private function checkLogAuthReadiness(){
        $lastAuth = $this->DBC->fetch(
            $this->DBC->query(
                'SELECT `timestamp` FROM `logs_auth` WHERE `email` = ? AND `type` = ? ORDER BY `timestamp` DESC LIMIT 1', [$this->email, $this->method]
            )
        );
        if ($lastAuth !== false){
            $lastAuth = strtotime($lastAuth['timestamp']);
            $now = strtotime(date('Y-m-d H:i:s'));
            if ($now - $lastAuth < $this->minSecondsBetweenAuth){
                return false;
            }
        }
        return true;
    }
    private function log(){
        $this->DBC->query(
            'INSERT INTO logs_auth (`timestamp`, `ip`, `email`, `type`) VALUES (?, ?, ?, ?)', [date('Y-m-d H:i:s'), Utils::getClientIP(), $this->email, $this->method]
        );
    }
}