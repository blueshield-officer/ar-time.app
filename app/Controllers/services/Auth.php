<?php

namespace App\Controllers\services;

use App\Services\Accounts\Authentification;

class Auth
{
    public function execute(){
       Authentification::auth();
    }
}