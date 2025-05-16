<?php

namespace App\Services;

use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class Notificator
{
    private $webPush;

    public function __construct()
    {
        $auth = [
            'VAPID' => [
                'subject' => 'mailto:admin@ar-time.org', // Use a valid email address
                'publicKey' => 'BPmY3aRTv3WMzUk87saQ_ecjhzlhKq8FtdX9j-_dJuwCnePGRgXSBRlhgEnmgNTUvgKF6Xoo5zqcd5ZSPXD3Bt8',
                'privateKey' => 'SqwO8nCnTi3qKhfNiO7L_YnFs6JboXyxVOQYw2TMw3E',
            ],
        ];
        $this->webPush = new WebPush($auth);
    }
    public function scheduleNotification($DBC, $timestamp, $title, $body, $icon, $url, $recipientToken)
    {
        $timestamp = $timestamp->format('Y-m-d H:i:s');
        $payload = $this->encodePayload($title, $body, $icon, $url);
        $DBC->query('INSERT INTO `notifications_scheduled` (timestamp, payload, recipient_token) VALUES (?, ?, ?)', [$timestamp, $payload, $recipientToken]);
    }
    public function sendNotification($recipientToken, $title, $body, $icon = null, $url = null)
    {
        if (is_null($icon)){
            $icon = '/frontend/assets/img/website-icons/ar-time-bar-icon.png';
        }
        if (is_null($url)){
            $url = '/';
        }

        $payload = $this->encodePayload($title, $body, $icon, $url);
        $subscriptionData = json_decode($recipientToken, true);
        if (!$subscriptionData) {
//            echo 'Invalid recipient token provided.';
            return false;
        }
        $subscription = Subscription::create($subscriptionData);
        $report = $this->webPush->sendOneNotification($subscription, $payload);

        if (!$report->isSuccess()) {
//            echo "Notification sent successfully to {$report->getEndpoint()}.\n";
            return true;
        }
        return false;
    }
    public function sendNotifictionAll($recipientTokens, $title, $body, $icon, $url)
    {
        $payload = $this->encodePayload($title, $body, $icon, $url);
        foreach ($recipientTokens as $recipientToken) {
            $subscriptionData = json_decode($recipientToken, true);
            $subscription = Subscription::create($subscriptionData);
            $this->webPush->queueNotification($subscription, $payload);
        }
    }
    public function testNotification($recipientToken)
    {
        $this->sendNotification($recipientToken, 'This Is Test Notification', 'Yours notification are on and fully working. Enjoy them.', null, '/notifications');
    }
    private function encodePayload($title, $body, $icon, $url)
    {
        return json_encode([
            'title' => $title,
            'body' => $body,
            'icon' => $icon,
            'url' => $url,
        ]);
    }
}
//     'publicKey' => 'BFl0KXWYVr8dPCxMz2hNhP-qObaXFlmE-dTk08xgafS3vwQZVJbxzpVlKEw2bIMXM27vQu0adHgx517m3XsUliw',
//      'privateKey' => 'GMDrk1S9pgNausPrAW_416-G27IFW_kpPgFHqiJqErk',
