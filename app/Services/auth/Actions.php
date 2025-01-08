<?php

namespace App\Services\auth;

use App\Services\DB_Connection;

class Actions
{
    // TODO email check
    static public function register($email, $password, $first_name = null, $last_name = null)
    {
        $pdo = DB_Connection::getPDO();

        if (preg_match('/\d/', $email)){
            $type = 'student';
        }
        else {
            $type = 'staff';
        }

        // creating user id until it going to be uniqe
        $result = true;
        while ($result !== false){
            $user_id = rand(1000000, 9999999);
            $query = 'SELECT * FROM users WHERE user_id = ?';
            $stmt = $pdo->prepare($query);
            $stmt->execute([$user_id]);
            $result = $stmt->fetch();
        }

        Actions::send_code($email);
        echo 'Code sent.';

        // creating account
        $query = 'INSERT INTO users (user_id, email, password, first_name, last_name, absent_data, type) VALUES (?, ?, ?, ?, ?, ?, ?)';
        $stmt = $pdo->prepare($query);
        $stmt->execute([$user_id, $email, $password, $first_name, $last_name, '', $type]);
    }
    static public function login($email, $keep_me_login)
    {
        $pdo = DB_Connection::getPDO();

        $query = 'SELECT user_id FROM users WHERE email = ?';
        $stmt = $pdo->prepare($query);
        $stmt->execute([$email]);
        $result = $stmt->fetch();

        $user_id = $result['user_id'];
        self::session_login($user_id);

        if ($keep_me_login){
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
            $token = base64_encode(random_bytes(256));

            $query = 'INSERT INTO users_sessions (user_id, user_agent, token) VALUES (?, ?, ?)';
            $stmt = $pdo->prepare($query);
            $stmt->execute([$user_id, $user_agent, $token]);
            $time =  time() + 60 * 60 * 24 * 30;
            setcookie('keep_me_login', $token, $time, '/');
            setcookie('user_id', $user_id, $time, '/');
        }
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
    static public function session_login($user_id){
        $_SESSION['user_id'] = $user_id;
        $_SESSION['logged_in'] = true;
    }
    static public function destroy_cookies(){
        setcookie('user_id', '', time(), '/');
        setcookie('keep_me_login', '', time(), '/');
    }
}