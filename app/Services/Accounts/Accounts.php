<?php

namespace App\Services\Accounts;

class Accounts
{
    static function getAccountID($DBC, $email){
        $result = $DBC->fetch(
            $DBC->query(
                'SELECT `user_id` FROM `users_accounts` WHERE `email` = ?', [$email]
            )
        );
        if ($result === false){
            return false;
        }
        return $result['user_id'];
    }

    static function getAccountEmail($DBC, $userID){
        $result = $DBC->fetch(
            $DBC->query(
                'SELECT `email` FROM `users_accounts` WHERE `user_id` = ?', [$userID]
            )
        );
        if ($result === false){
            return false;
        }
        return $result['email'];
    }
}