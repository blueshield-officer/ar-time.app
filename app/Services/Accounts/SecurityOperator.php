<?php

namespace App\Services\Accounts;

class SecurityOperator
{
    static private function getSalt(){
        return 'Owx+u(2J2dQ6~04p';
    }
    static function password_hash($password){
        $options = [
            'cost' => 14
        ];
        $salt = self::getSalt();

        return password_hash($salt . $password . $salt, PASSWORD_BCRYPT, $options);
    }
    static function password_verify($password, $hash){
        // sigmaBoyBoy12!
        $salt = self::getSalt();
        return password_verify($salt . $password . $salt, $hash);
    }

    // sigmaBoyBoy12!
    static function createToken(){
        return bin2hex(random_bytes(16));
    }
}