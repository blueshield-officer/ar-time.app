<?php

namespace App\Services\Accounts\validations;

use App\Services\Accounts\Accounts;
use App\Services\Session;

class AuthValidationPasswordChange
{
    private $DBC;
    private $accessToken;
    private $password;
    private $userID = null;
    public function __construct($DBC)
    {
        $this->DBC = $DBC;
    }
    public function process()
    {
        $formValidation = new FormValidation('passwordChange');
        $form = $formValidation->valid();

        if ($form !== false) {
            $this->accessToken = $form['accessToken'];
            $this->password = $form['password'];

            if ($this->validateToken() !== true) {
                return $this->addViolation('Invalid token. Please DO NOT try again.');
            }

            $passwordsMatch = AuthValidationChecks::validatePassword($this->DBC, $this->userID, $this->password);
            if ($passwordsMatch !== false){
                return $this->addViolation('New password cannot be the same as old one.');
            }

            if (str_contains(strtolower($this->password), strtolower(Accounts::getAccountEmail($this->DBC, $this->userID)))){
                return $this->addViolation('Password cannot contain your email.');
            }

            return true;

        }
        else {
            return $this->addViolation('Invalid form.');
        }
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getAccessToken()
    {
        return $this->accessToken;
    }

    public function getUserID(){
        return $this->userID;
    }

    private function validateToken(){
        $result = $this->DBC->fetch(
            $this->DBC->query(
                'SELECT `user_id` FROM users_change_password_tokens WHERE `token` = ? AND `timestamp_expires` >= ? AND `used` = ? LIMIT 1', [$this->accessToken, date('Y-m-d H:i:s'), 0]
            )
        );
        if ($result === false){
            return false;
        }
        $this->userID = $result['user_id'];
        return true;
    }

    private function addViolation($error)
    {
        Session::setFormViolation('passwordChange', 'general', $error);
        return false;
    }
}