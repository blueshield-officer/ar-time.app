<?php

namespace App\Services\Accounts;

use App\Services\DBC;
use App\Services\Session;

class Actions
{
    // TODO email check
    static public function register($DBC, $email, $password, $first_name = null, $last_name = null, $graduation_year = null, $student = true)
    {
        if ($student === true) {
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
        $DBC->query('INSERT INTO users_accounts (user_id, graduation_year, email, password, first_name, last_name, type) VALUES (?, ?, ?, ?, ?, ?, ?)', [$user_id, $graduation_year, $email, $password, $first_name, $last_name, $type]);
        $DBC->query('INSERT INTO users_teachers (user_id) VALUES (?)', [$user_id]);
    }
    static public function login($DBC, $email)
    {
        $result = $DBC->fetch($DBC->query('SELECT user_id FROM users_accounts WHERE email = ?', [$email]));

        $user_id = $result['user_id'];

        $user_agent = $_SERVER['HTTP_USER_AGENT'];
        $token = base64_encode(random_bytes(256));

        $DBC->query('INSERT INTO users_sessions (user_id, user_agent, token_cookie, login_datetime) VALUES (?, ?, ?, ?)', [$user_id, $user_agent, $token, date('Y-m-d H:i:s')]);
        $time =  time() + 60 * 60 * 24 * 30;
        setcookie('user_id', $user_id, $time, '/');
        setcookie('token', $token, $time, '/');
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
    static public function analyzeEmail($email)
    {
        if (str_ends_with($email, '@arhs.org')){
            preg_match('/(\w)(\w+?)@/', $email, $matches);
            $firstName = $matches[1];
            $lastName = $matches[2];
            $mortal = false;
        }
        else if (str_ends_with($email, '@ryanhs.org')) {
            if (str_contains($email, '.')){
                preg_match('/(\d+)\.(\w+)\.(\w+)@/', $email, $matches);
                $gradYear = $matches[1];
                $firstName = $matches[2];
                $lastName = $matches[3];
            }
            else {
                preg_match('/(\w)(\w+?)(\d+)@/', $email, $matches);
                $gradYear = $matches[3];
                $firstName = $matches[1];
                $lastName = $matches[2];
            }
            $gradYear = 20 . $gradYear;
        }
        else {
            // TODO рубильник или чё
            die();
        }
        return [
            'firstName' => $firstName ?? null,
            'lastName' => $lastName ?? null,
            'gradYear' => $gradYear ?? null,
            'mortal' => $mortal ?? true
        ];
    }
}