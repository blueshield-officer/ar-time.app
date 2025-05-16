<?php

namespace App\Services\Accounts\validations;

use App\Services\Session;

class FormValidation
{
    private $formType;
    private $valid = true;

    public function __construct($formType){
        $this->formType = $formType;
    }

    // returns true if valid, and if not, the error string with the field were it occured
    public function valid(){
        if ($_SERVER['REQUEST_METHOD'] === 'POST'){
            if ($this->validateCSRFToken() !== true){
                return false;
            }

            $returnDataArray = [];

            // ============= login / sign up ===============
            if ($this->formType === 'login' || $this->formType === 'sign up'){
                if (!isset($_POST['email']) || !isset($_POST['password']) || ($this->formType === 'sign up' && !isset($_POST['password2']))) {
                    return false;
                }
                else {
                    $email = $_POST['email'];
                    $password = $_POST['password'];

                    $emailValid = $this->validateEmail($email);
                    if ($emailValid !== true){
                        $this->addFormViolation('email', $emailValid);
                    }
                    else {
                        $returnDataArray['email'] = $email;
                    }

                    $passwordValid = $this->validatePassword($password, $email);
                    if ($passwordValid !== true){
                        $this->addFormViolation('password', $passwordValid);
                    }
                    else {
                        $returnDataArray['password'] = $password;
                    }

                    if ($this->formType === 'signup'){
                        $password2 = $_POST['password2'] ?? null;
                        $password2Valid = $this->validatePassword2($password, $password2);
                        if ($password2Valid !== true){
                            $this->addFormViolation('password2', $password2Valid);
                        }
                        else {
                            $returnDataArray['password2'] = $password2;
                        }
                    }
                }
            }

            // ============= code verification ===============
            else if ($this->formType === 'codeVerification'){
                for ($i = 1; $i <= 6; $i++){
                    if (isset($_POST['int' . $i])){
                        $int = $_POST['int' . $i];
                        if (is_numeric($int) && strlen($int) === 1){
                            $returnDataArray[] = $int;
                            continue;
                        }
                    }
                    return false;
                }
                $returnDataArray = (int)join('', $returnDataArray);
            }

            // ============= password change ===============
            else if ($this->formType === 'passwordChange'){
                if (!isset($_POST['password']) || !isset($_POST['access_token'])) {
                    return false;
                }
                else {
                    $password = $_POST['password'];
                    $accessToken = $_POST['access_token'];

                    $passwordValid = $this->validatePassword($password);
                    if ($passwordValid !== true){
                        $this->addFormViolation('password', $passwordValid);
                    }
                    else {
                        $returnDataArray['password'] = $password;
                    }

                    $accessTokenValid = $this->validateToken($accessToken, 128);
                    if ($accessTokenValid !== true){
                        $this->addFormViolation('general', 'Invalid access token.');
                    }
                    else {
                        $returnDataArray['accessToken'] = $accessToken;
                    }
                }
            }

            // ==============================================

            if ($this->valid === false){
                return false;
            }
            return $returnDataArray;
        }
        else {
            return false;
        }
    }

    private function validateEmail($email){
        $email = trim(strtolower($email));
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
            return 'Invalid email.';
        }
        if (!str_ends_with($email, '@ryanhs.org') && !str_ends_with($email, '@arhs.org')){
            return 'Email must be @ryanhs.org or @arhs.org type.';
        }
        return true;
    }

    private function validatePassword($password, $email = null){
        $password = trim($password);
        if (!is_null($email)){
            $email = strtolower(trim($email));
        }
        $passwordLower = strtolower($password);

        $responses = [
            'signupRequest' => [
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

        $tooShort = strlen($password) < 8;
        $tooLong = strlen($password) > 64;
        $hasSpaces = preg_match('/\s/', $password) > 0;
        $emailInPassword = (!is_null($email) && str_contains($passwordLower, $email));

        return match (true) {
            $tooShort    => $responses[$this->formType]['too_short'],
            $tooLong     => $responses[$this->formType]['too_long'],
            $hasSpaces   => $responses[$this->formType]['no_spaces'],
            $emailInPassword => $responses[$this->formType]['email_in_password'],
            default      => true,
        };
    }

    private function validateToken($token, $length)
    {
        if (is_string($token) && strlen($token) === $length) {
            return true;
        }
        return false;
    }

    private function validatePassword2($password1, $password2){
        if ($password1 !== $password2){
            return 'Repeat password does not match.';
        }
        return true;
    }

    private function validateCSRFToken()
    {
        if (isset($_POST['csrf_token'])) {
            $token = $_POST['csrf_token'];
            if ($token === $_SESSION['csrf_token']) {
                return true;
            }
        }
        return false;
    }

    private function addFormViolation($field, $error)
    {
        $this->valid = false;
        Session::setFormViolation($this->formType, $field, $error);;
    }
}