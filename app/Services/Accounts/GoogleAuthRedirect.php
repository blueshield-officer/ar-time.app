<?php

namespace App\Services\Accounts;

use Google\Service\Exception;
use Google_Client;

class GoogleAuthRedirect
{
    private $clientID = '128345250549-kr7oh6un87ci7vgj1e1ci8qvcg3du5qb.apps.googleusercontent.com';
    private $clientSecret = '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';
    private $redirectUri = 'https://sandbox.ar-time.org/google-auth';
    private $client;
    public function __construct()
    {
        $this->client = new Google_Client();
        $this->client->setClientId($this->clientID);
        $this->client->setClientSecret($this->clientSecret);
        $this->client->setRedirectUri($this->redirectUri);
        $this->client->addScope("email");
        $this->client->addScope("profile");
        $this->client->setPrompt('select_account');
    }

    public function getRedirectUrl()
    {
        return $this->client->createAuthUrl();
    }

    public function analyzeCode()
    {
        if (isset($_GET['code'])) {
            $token = $this->client->fetchAccessTokenWithAuthCode($_GET['code']);
            $this->client->setAccessToken($token['access_token']);


            // get profile info
            $google_oauth = new \Google_Service_Oauth2($this->client);
            $google_account_info = $google_oauth->userinfo->get();
            $id = $google_account_info->id;
            $email = $google_account_info->email;
            $name = $google_account_info->name;

            return [
                'id' => $id,
                'email' => $email,
                'name' => $name,
            ];
        }
        else {
            return null;
        }
    }
}