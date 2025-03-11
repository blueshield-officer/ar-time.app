<?php

namespace App\Services\Accounts;

use App\Services\DBC;

class Checks
{
    static public function password_valid($email, $password, $method){
        $password = trim($password);
        $email = strtolower(trim($email));
        $passwordLower = strtolower($password);

        $responses = [
            'signup' => [
                'too_short' => 'Password must be at least 8 characters long.',
                'too_long' => 'Password must not exceed 64 characters.',
                'no_spaces' => 'Password cannot contain spaces.',
//                'no_number' => 'Password must include at least one number.',
                'email_in_password' => 'Password should not contain your email.',
            ],
            'login' => array_fill_keys(
                ['too_short', 'too_long', 'no_spaces', 'email_in_password'],
                'Password or email is incorrect.'
            ),
        ];

        return match (true) {
            // Length validation
            strlen($password) < 8  => $responses[$method]['too_short'],
            strlen($password) > 64 => $responses[$method]['too_long'],

            // No spaces allowed
            preg_match('/\s/', $password) => $responses[$method]['no_spaces'],

            // Complexity validation
//            !preg_match('/\d/', $password) => $responses[$method]['no_number'],

            // Email check
            strpos($passwordLower, $email) !== false => $responses[$method]['email_in_password'],

            // If all checks pass
            default => true,
        };
    }
    static public function email_valid($email){
        $email = trim(strtolower($email));
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            return 'Invalid email.';
        }
        // TODO НЕЗАБУДЬ ВКЛЮЧИТЬ, ну потом
//        if (!str_ends_with($email, '@ryanhs.org')){
//            return 'Email must be @ryanhs.org';
//        }
        return true;
    }
    static public function email_usable($DBC, $email, $method){
        $result = $DBC->fetchAll($DBC->query('SELECT * FROM users_accounts WHERE email = ?', [$email]));
        var_dump($result);
        if ($method === 'login' && empty($result)){
            return 'Email does not register.';
        }
        if ($method === 'signup' && !empty($result)){
            return 'Email already registered.';
        }
        return true;
    }

    static function check_login($DBC, $email, $password){
        $result = $DBC->fetch($DBC->query('SELECT password FROM users_accounts WHERE email = ?', [$email]));
        if ($result === false){
            return 'Password or email is incorrect.';
        }
        $password_legit = SecurityOperator::password_verify($password, $result['password']);
        if ($password_legit === false){
            return 'Password or email is incorrect.';
        }
        return true;
    }

    // TODO email check
}