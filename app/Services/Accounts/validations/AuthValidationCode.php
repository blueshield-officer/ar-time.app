<?php

namespace App\Services\Accounts\validations;

use App\Services\Session;

class AuthValidationCode
{
    private $DBC;
    private $email;
    private $code;
    private $leftSeconds;
    public function __construct($DBC)
    {
        $this->DBC = $DBC;
        $this->email = $_SESSION['tempVars']['awaitCode']['value']['email'];
        $this->leftSeconds = $_SESSION['tempVars']['awaitCode']['value']['expires'] - time();
    }
    public function process()
    {
        $formValidation = new FormValidation('codeVerification');
        $form = $formValidation->valid();

        if ($form !== false){
            $userInfo = Session::getAwaitCodeInfo();
            $this->email = $userInfo['email'];
            $this->code = $form;

            if ($this->validateCode() === true){
                return true;
            }
            else {
                $_SESSION['tempVars']['awaitCode']['value']['leftTries'] -= 1;
                return $this->addViolation('Invalid code. Please try again.');
            }
        }
        else {
            return false;
        }
    }

    private function validateCode(){
        $result = $this->DBC->fetch(
            $this->DBC->query(
                'SELECT * FROM users_registration_codes WHERE email = ? AND code = ? AND timestamp_expires >= ? AND verified = ? LIMIT 1', [$this->email, $this->code, date('Y-m-d H:i:s'), 0]
            )
        );
        if ($result === false){
            return false;
        }
        return true;
    }

    private function addViolation($error)
    {
        Session::setFormViolation('sign up', 'general', $error);
        return false;
    }
}