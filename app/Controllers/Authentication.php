<?php

namespace App\Controllers;

use App\Services\Accounts\AuthManager;
use App\Services\Accounts\GoogleAuthRedirect;
use App\Services\Accounts\validations\AuthValidationCode;
use App\Services\Accounts\validations\AuthValidationDefault;
use App\Services\Accounts\validations\AuthValidationGoogle;
use App\Services\Accounts\validations\AuthValidationPasswordChange;
use App\Services\Controller;
use App\Services\DBC;
use App\Services\Mailer;
use App\Services\Session;
use App\Services\SmartyVars;

class Authentication extends Controller
{
    private $googleAuthRedirect = null;
    private function getMethod()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    private function loadGoogleAuthRedirect()
    {
        if (is_null($this->googleAuthRedirect)){
            $this->googleAuthRedirect = new GoogleAuthRedirect();
        }
    }

    private function authSame($method){
        $smartyVars = new SmartyVars();
        $this->loadGoogleAuthRedirect();
        if ($this->getMethod() === 'GET'){
            $params = [
                'pageId' => 'authentication',
            ];
            $authError = Session::getAuthError($method);

            if (Session::isAwaitCode() === false){
                // regular auth
                $params += [
                    'method' => $method,
                    'googleError' => $authError['google'] ?? null,
                    'generalError' => $authError['general'] ?? null,
                    'emailError' => $authError['email'] ?? null,
                    'passwordError' => $authError['password'] ?? null,
                    'googleAuthRedirectLink' => $this->googleAuthRedirect->getRedirectUrl()
                ];
                if ($method === 'sign up'){
                    $params['password2Error'] = $authError['password2'] ?? null;
                }

                $this->view('pages/authentication.tpl',
                    $params + $smartyVars->getVarsArray()
                );
            }
            else {
                // code verification
                $params += Session::getAwaitCodeInfo();
                $this->view('pages/authentication-code.tpl',
                    $params + $smartyVars->getVarsArray() + [
                        'registered' => false,
                        'pageTitle' => 'Code Verification',
                        'generalError' => $authError['general'] ?? null,
                    ]
                );
            }
            Session::clearAuthError($method);
        }
        else {
            $DBC = DBC::getInstance();
            if (Session::isAwaitCode() === false){

                $authValidation = new AuthValidationDefault($DBC, $method);
                $this->redirectOnNonTrue($authValidation->process());

                $authManager = new AuthManager($DBC);
                $email = $authValidation->getEmail();
                $password = $authValidation->getPassword();

                if ($method === 'login'){
                    $authManager->login($email);
                    $this->redirect('/');
                }
                else if ($method === 'sign up'){
                    $mailer = new Mailer($this->render('mail/code.tpl', $authManager->registerAttempt($email, $password)));
                    $mailer->sendMail($email, 'ar-time Verification Code');
                    $this->redirect('/signup');
                }
            }
            else {
                $codeValidation = new AuthValidationCode($DBC);
                $this->redirectOnNonTrue($codeValidation->process());

                $authManager = new AuthManager($DBC);
                $authManager->registerFromSessionCode();

                $smartyVars = new SmartyVars();
                $this->view('pages/authentication-code.tpl', $smartyVars->getVarsArray() +[
                        'registered' => true,
                        'pageId' => 'authentication',
                        'pageTitle' => 'Registered',
                    ]
                );
                Session::clearAuthError('sign up');
            }
        }
    }

    private function redirectOnNonTrue($boolean, $url = null){
        if ($boolean !== true){
            if (is_null($url)){
                $url = $_SERVER['REQUEST_URI'];
            }
           $this->redirect($url);
        }
    }

    public function login()
    {
        $this->authSame('login');
    }

    public function signup()
    {
        $this->authSame('sign up');
    }

    public function googleAuth()
    {
        if (Session::isAwaitCode() === true) {
            $this->redirect('/signup');
        }
            $this->loadGoogleAuthRedirect();
        try {
            $userData = $this->googleAuthRedirect->analyzeCode();
        }
        catch (\Exception $e){
            $this->redirect('/login');
        }

        $authValidationGoogle = new AuthValidationGoogle($userData['email']);
        $this->redirectOnNonTrue($authValidationGoogle->process());

        $DBC = DBC::getInstance();
        $authManager = new AuthManager($DBC);
        $authManager->googleAuth($userData['id'], $userData['email'], $userData['name']);

        $this->redirect('/');
    }

    public function changePassword()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST'){
            $DBC = DBC::getInstance();
            $authValidationPasswordChange = new AuthValidationPasswordChange($DBC);
            if ($authValidationPasswordChange->process() === true){
                $smartyVars = new SmartyVars();

                $authManager = new AuthManager($DBC);
                $authManager->changePassword($authValidationPasswordChange->getUserID(), $authValidationPasswordChange->getPassword());

                $this->view('pages/password-change.tpl', [
                        'changed' => true,
                        'pageId' => 'authentication',
                        'pageTitle' => 'Password Changed',
                    ] + $smartyVars->getVarsArray()
                );
                exit();
            }
            else {
                $this->redirect('/password-change?token=' . $authValidationPasswordChange->getAccessToken());
            }
        }
        else if ($_SERVER['REQUEST_METHOD'] === 'GET'){
            if (isset($_GET['token'])){
                $DBC = DBC::getInstance();

                $result = $DBC->fetch(
                    $DBC->query(
                        'SELECT `email` FROM `users_change_password_tokens` WHERE `token` = ? AND `timestamp_expires` >= ? AND `used` = ?',
                        [$_GET['token'], date('Y-m-d H:i:s'), 0]
                    )
                );
                if ($result !== false){
                    $smartyVars = new SmartyVars();
                    $authError = Session::getAuthError('passwordChange');
                    $params = [
                        'changed' => false,
                        'pageId' => 'authentication',
                        'pageTitle' => 'Change Password',
                        'email' => $result['email'],
                        'generalError' => $authError['general'] ?? null,
                        'passwordError' => $authError['password'] ?? null,
                        'password2Error' => $authError['password2'] ?? null
                    ];

                    $this->view('pages/password-change.tpl', $params + $smartyVars->getVarsArray());

                    Session::clearAuthError('passwordChange');

                    exit();
                }
            }
        }
        $this->redirect('/');
    }
}