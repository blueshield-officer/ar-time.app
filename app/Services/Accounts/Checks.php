<?php

namespace App\Services\Accounts;

use App\Services\DBC;

class Checks
{
    static private function connection()
    {
        return DBC::getPDO();
    }
    static public function password_valid($email, $password, $method){
        $password = trim($password);
        $email = strtolower(trim($email));
        $passwordLower = strtolower($password);

        $responses = [
            'register' => [
                'too_short' => 'Password must be at least 8 characters long.',
                'too_long' => 'Password must not exceed 64 characters.',
                'no_spaces' => 'Password cannot contain spaces.',
                'no_upper' => 'Password must include at least one uppercase letter.',
                'no_lower' => 'Password must include at least one lowercase letter.',
                'no_number' => 'Password must include at least one number.',
                'no_special' => 'Password must include at least one special character.',
                'email_in_password' => 'Password should not contain your email.',
            ],
            'login' => array_fill_keys(
                ['too_short', 'too_long', 'no_spaces', 'no_upper', 'no_lower', 'no_number', 'no_special', 'email_in_password'],
                'Password is incorrect.'
            ),
        ];

        return match (true) {
            // Length validation
            strlen($password) < 8  => $responses[$method]['too_short'],
            strlen($password) > 64 => $responses[$method]['too_long'],

            // No spaces allowed
            preg_match('/\s/', $password) => $responses[$method]['no_spaces'],

            // Complexity validation
            !preg_match('/[A-Z]/', $password) => $responses[$method]['no_upper'],
            !preg_match('/[a-z]/', $password) => $responses[$method]['no_lower'],
            !preg_match('/\d/', $password) => $responses[$method]['no_number'],
            !preg_match('/[\W]/', $password) => $responses[$method]['no_special'],

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
        $result = $DBC->fetch($DBC->query('SELECT * FROM users_accounts WHERE email = ?', [$email]));

        if ($method === 'login' && $result === false){
            return 'Email does not register.';
        }
        if ($method === 'register' && $result !== false){
            return 'Email already registered.';
        }
        return true;
    }

    static function check_login($DBC, $email, $password){
        $result = $DBC->fetch($DBC->query('SELECT password FROM users_accounts WHERE email = ?', [$email]));
        if ($result === false){
            return 'Email does not register.';
        }
        $password_legit = SecurityOperator::password_verify($password, $result['password']);
        if ($password_legit === false){
            return 'Password is incorrect.';
        }
        return true;
    }

    static function check_account_status($DBC){
        // session check
        session_start();
        if (isset($_SESSION['user_id']) && isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
            echo 'You are logged in. SESSION';
        }
        // login first time by cookies
        $user_id = $_COOKIE['user_id'] ?? '';
        $token = $_COOKIE['keep_me_login'] ?? '';
        if ($user_id !== '' && $token !== ''){
            $result =$DBC->fetch($DBC->query('SELECT * FROM users_sessions WHERE user_id = ? AND token = ?', [$user_id, $token]));
            if ($result !== false){
                Actions::session_login($user_id);
                echo 'You are logged in. COOKIES';
            }
            else {
                // destroy broken cookies
                Actions::destroy_cookies();
            }
        }
        else {
            // destroy broken cookies
            Actions::destroy_cookies();
        }
    }

    // TODO email check
}