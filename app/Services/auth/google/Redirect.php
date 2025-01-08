<?php

namespace App\Services\auth\google;

use Google\Service\Exception;
use Google_Client;

class Redirect
{
    /**
     * @throws Exception
     */
    static public function redirect()
    {
        // init configuration
        $clientID = '128345250549-e5fm7bp6mj7vbo18fnnm0vda7pov0nc4.apps.googleusercontent.com';
        $clientSecret = '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';
        $redirectUri = 'https://ar-time.com/login/google';

        // create Client Request to access Google API
        $client = new Google_Client();
        $client->setClientId($clientID);
        $client->setClientSecret($clientSecret);
        $client->setRedirectUri($redirectUri);
        $client->addScope("email");
        $client->addScope("profile");

        // authenticate code from Google OAuth Flow
        if (isset($_GET['code'])) {
            $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
            $client->setAccessToken($token['access_token']);

            // get profile info
            $google_oauth = new \Google_Service_Oauth2($client);
            $google_account_info = $google_oauth->userinfo->get();
            $email = $google_account_info->email;
            $name = $google_account_info->name;

            // now you can use this profile info to create account in your website and make user logged in.
        } else {
            echo "<a href='" . $client->createAuthUrl() . "'>Google Login</a>";
        }

    }
}