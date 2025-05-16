<?php

namespace App\Services\Accounts\validations;

class AuthValidationChecks
{
    static function validatePassword($DBC, $userID, $password){
        $salt = $_ENV['PASSWORD_SALT'];;

        $result = $DBC->fetch(
            $DBC->query(
                'SELECT `password` FROM `users_accounts` WHERE `user_id` = ?', [$userID]
            )
        );
        if ($result !== false){
            $hash = $result['password'];
            if (password_verify($salt . $password . $salt, $hash)){
                return true;
            }
        }
        return false;
    }
}