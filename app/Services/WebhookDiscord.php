<?php

namespace App\Services;

class WebhookDiscord
{
    private $webhookUrl;
    public function __construct($url)
    {
        $this->webhookUrl = 'https://discordapp.com/api/webhooks/' . $url;
    }
    public function sendWebhook($text){
        $msg = ["content" => "Hi, this is the message sent from php"];

        $headers = array('Content-Type: application/json');

        $ch = curl_init();
        curl_setopt( $ch,CURLOPT_URL, $this->webhookUrl );
        curl_setopt( $ch,CURLOPT_POST, true );
        curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $msg ) );
        $response = curl_exec( $ch );
        curl_close( $ch );

        echo $response;
    }
}