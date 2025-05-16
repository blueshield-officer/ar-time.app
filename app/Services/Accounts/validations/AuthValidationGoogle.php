<?php

namespace App\Services\Accounts\validations;

use App\Services\Session;

class AuthValidationGoogle
{
    private $email;

    public function __construct($email)
    {
        $this->email = $email;
    }

    public function process(){
        if ($this->validateEmail() !== true){
            return false;
        };
        return true;
    }

    private function validateEmail(){
        if (!str_ends_with($this->email, '@ryanhs.org') && !str_ends_with($this->email, '@arhs.org')){
            return $this->addViolation('Email must be @ryanhs.org or @arhs.org type.');
        }
        return true;
    }

    private function addViolation($error)
    {
        Session::setFormViolation('login', 'google', $error);
        return false;
    }
}