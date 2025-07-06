<?php

namespace App\Services\Accounts\validations;

use App\Services\Session;
use App\Services\Utils;

class AuthValidationGoogle
{
    private $email;
    private $DBC;

    public function __construct($DBC, $email)
    {
        $this->DBC = $DBC;
        $this->email = $email;
    }

    public function process(){
        $this->log();

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

    private function log(){
        $this->DBC->query(
            'INSERT INTO logs_auth (`timestamp`, `ip`, `email`, `type`) VALUES (?, ?, ?, ?)', [date('Y-m-d H:i:s'), Utils::getClientIP(), $this->email, 'google']
        );
    }
}