<?php

namespace App\Services\Accounts;

use App\Services\Mailer;
use App\Services\SecureCookie;
use App\Services\Session;
use App\Services\Utils;

class AuthManager
{
    private $DBC;

    private $loggedIn = false;

    public function __construct($DBC)
    {
        $this->DBC = $DBC;
    }

    public function login($email, $googleId = null)
    {
        $secureCookie = new SecureCookie();
        $token = Utils::generateRandomSymbols(32);

        $userId = null;

        if (!is_null($googleId)) {
            $result = $this->DBC->fetch(
                $this->DBC->query(
                    'SELECT user_id FROM users_accounts WHERE google_id = ?',
                    [$googleId]
                )
            );
            if ($result !== false) {
                $userId = $result['user_id'];
            }
        }

        if (is_null($userId)) {
            $result = $this->DBC->fetch(
                $this->DBC->query(
                    'SELECT user_id FROM users_accounts WHERE email = ?',
                    [$email]
                )
            );

            if ($result !== false) {
                $userId = $result['user_id'];
                if (!is_null($googleId)) {
                    $this->DBC->query(
                        'UPDATE users_accounts SET google_id = ? WHERE user_id = ? LIMIT 1',
                        [$googleId, $userId]
                    );
                }
            }
        }


        $this->DBC->query(
            'INSERT INTO users_sessions (user_id, last_visit, user_agent, token_cookie) VALUES (?, ?, ?, ?)',
            [$userId, date('Y-m-d H:i:s'), $_SERVER['HTTP_USER_AGENT'], $token]
        );

        $secureCookie->setCookie('auth_token', $token);
        $secureCookie->setCookie('user_id', $userId);
    }

    public function registerAttempt($email, $password)
    {
        Session::setAwaitCode(true, $email);

        $code = $this->generateRandomNumber(6);

        $this->DBC->query(
            'INSERT INTO users_registration_codes (timestamp_expires, email, password, code) VALUES (?, ?, ?, ?)',
            [((new \DateTime())->modify('+5 minutes'))->format('Y-m-d H:i:s'), $email, $this->hashPassword($password), $code]
        );

        return ['code' => $code];
    }

    public function registerFromSessionCode(){
        $email = Session::getAwaitCodeInfo()['email'];
        $date = date('Y-m-d H:i:s');
        $password = $this->DBC->fetch(
            $this->DBC->query(
                'SELECT `password` FROM `users_registration_codes` WHERE `email` = ? AND timestamp_expires >= ? AND verified = 0 LIMIT 1', [$email, $date],
            )
        );
        if ($password !== false){
            $password = $password['password'];
            $this->DBC->query(
                'UPDATE users_registration_codes SET verified = 1 WHERE email = ? AND timestamp_expires >= ? AND verified = 0 LIMIT 1', [$email, $date],
            );

            $this->register($email, $password);

            Session::setAwaitCode(false);;
        }
    }

    public function register($email, $password = null, $name = null, $googleId = null)
    {
        $userData = $this->extractDataFromEmail($email);

        if (is_null($name)) {
            $name = $userData['name'];
        }
        if (is_null($password)) {
            $password = $this->hashPassword(Utils::generateRandomSymbols(64));
        }

        $this->DBC->query(
            'INSERT INTO users_accounts (google_id, email, password, name, graduation_year, type) VALUES (?, ?, ?, ?, ?, ?)',
            [$googleId, $email, $password, $name, $userData['gradYear'], $userData['type']]
        );
        $userID = $this->DBC->getLastInsertID();

        $this->DBC->query(
            'INSERT INTO users_chosen_people (user_id) VALUES (?)', [$userID]
        );
    }

    public function changePassword($userID, $password)
    {
        $this->DBC->query(
            'UPDATE users_change_password_tokens SET used = 1 WHERE user_id = ? AND timestamp_expires >= ? LIMIT 1', [$userID, date('Y-m-d H:i:s')]
        );
        $this->DBC->query(
            'UPDATE users_accounts SET password = ? WHERE user_id = ?', [$this->hashPassword($password), $userID]
        );
        $this->DBC->query(
            'DELETE FROM users_sessions WHERE user_id = ?', [$userID]
        );
    }

    public function logOut()
    {
        $accountData = Session::getAccountSessionData();
        $this->DBC->query(
            'DELETE FROM users_sessions WHERE user_id = ? AND token_cookie = ?', [$accountData['id'], $accountData['token']]
        );
    }

    public function googleAuth($google_id, $email, $name)
    {
        $accountExist = $this->DBC->fetch(
            $this->DBC->query(
                'SELECT * FROM users_accounts WHERE email = ? OR google_id = ?', [$email, $google_id]
            )
        );
        if ($accountExist === false){
            $this->register($email, null, $name, $google_id);
        }
        $this->login($email, $google_id);
    }


    private function hashPassword($password)
    {
        $salt = $_ENV['PASSWORD_SALT'];
        $options = [
            'cost' => 14
        ];
        return password_hash($salt . $password . $salt, PASSWORD_BCRYPT, $options);
    }

    private function generateRandomNumber($length): int
    {
        $min = pow(10, $length - 1);
        $max = pow(10, $length) - 1;
        return random_int($min, $max);
    }

    private function extractDataFromEmail($email)
    {
        $email = strtolower($email);
        $type = 'student';
        if (str_ends_with($email, '@arhs.org')) {
            preg_match('/(\w)([a-z-]+?)@/', $email, $matches);
            $firstName = $matches[1];
            $lastName = $matches[2];
            $type = 'staff';
        }
        else if (str_ends_with($email, '@ryanhs.org')) {
            if (str_contains(str_replace('@ryanhs.org', '', $email), '.')) {
                preg_match('/(\d+)\.([a-z-]+)\.([a-z-]+)@/', $email, $matches);
                $firstName = $matches[3] ?? null;
                $lastName = $matches[2] ?? null;
                $gradYear = $matches[1] ?? null;
            }
            else {
                preg_match('/(\w)([a-z-]+)(\d+)@/', $email, $matches);
                $firstName = $matches[1] ?? null;
                $lastName = $matches[2] ?? null;
                $gradYear = $matches[3] ?? null;
            }
            $gradYear = $gradYear ? ('20' . $gradYear) : null;

        }
        else {
            die();
        }
        return [
            'name' =>  trim(
                ucwords(
                    (isset($firstName) && strlen($firstName) === 1
                        ? $firstName . '.'
                        : $firstName
                    ) . ' ' . ($lastName ?? '')
                )
            ),
            'gradYear' => $gradYear ?? null,
            'type' => $type
        ];
    }
}