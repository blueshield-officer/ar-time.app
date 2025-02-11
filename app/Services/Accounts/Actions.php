<?php

namespace App\Services\Accounts;

use App\Services\DBC;
use App\Services\Session;

class Actions
{
    // TODO email check
    static public function register($DBC, $email, $password, $first_name = null, $last_name = null)
    {
        if (preg_match('/\d/', $email)){
            $type = 'student';
        }
        else {
            $type = 'staff';
        }

        // creating user id until it going to be uniqe
        $result = true;
        while ($result !== false){
            $user_id = rand(1000000000, 9999999999);
            $result = $DBC->fetch($DBC->query('SELECT * FROM users_accounts WHERE user_id = ?', [$user_id]));
        }
        Actions::send_code($email);
        echo 'Code sent.';

        // creating account
        $DBC->query('INSERT INTO users_accounts (user_id, email, password, first_name, last_name, type) VALUES (?, ?, ?, ?, ?, ?)', [$user_id, $email, $password, $first_name, $last_name, $type]);
        $DBC->query('INSERT INTO users_teachers (user_id) VALUES (?)', [$user_id]);
    }
    static public function login($DBC, $email)
    {
        $result = $DBC->fetch($DBC->query('SELECT user_id FROM users_accounts WHERE email = ?', [$email]));

        $user_id = $result['user_id'];

        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $token = base64_encode(random_bytes(256));

        $DBC->query('INSERT INTO users_sessions (user_id, user_agent, token_cookie) VALUES (?, ?, ?)', [$user_id, $user_agent, $token]);
        $time =  time() + 60 * 60 * 24 * 30;
        setcookie('user_id', $user_id, $time, '/');
        setcookie('keep_me_login', $token, $time, '/');
    }
    static public function send_code($email){
        $code = rand(100000, 999999);

        $to      = $email;
        $subject = 'Verification Code';
        $message = 'Here is your code: '.$code;
        $headers = array(
            'From' => 'ar-time <no-reply@ar-time.org>',
            'X-Mailer' => 'PHP/' . phpversion()
        );

        mail($to, $subject, $message, $headers);
    }
    static public function set_notification_token(){
        
    }
}