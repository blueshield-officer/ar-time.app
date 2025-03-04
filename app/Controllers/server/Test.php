<?php

namespace App\Controllers\server;

use App\Services\CacheOperator;
use App\Services\ConfigTemp;
use App\Services\Controller;
use App\Services\DBC;
use App\Services\Notificator;
use App\Services\RedisOperator;
use App\Services\SQL;
use App\Services\WebhookDiscord;
use Minishlink\WebPush\VAPID;
use Redis;
use thiagoalessio\TesseractOCR\TesseractOCR;

class Test extends Controller
{
    public function execute()
    {
//        $this->view('debug.tpl', [
//            'name' => 'test',
//            'spent_time' => null,
//            'data' => [
//                'old_data' => [
//                    'data' => [
//                        1 => 'ГСБ',
//                        2 => 'ГВ'
//                    ],
//                    'important' => true
//                ],
//                'new_data' => [
//                    'data' => [
//                        1 => 'СИ',
//                        2 => 'НР'
//                    ],
//                    'important' => true
//                ]
//            ],
////            'local_date' => new \DateTime(),
//            'type' => 'compare'
//        ]);


//        $SQL = new SQL($DBS);
//        var_dump($SQL->getStaffData());

//        $redis = Redis_Operator::getInstance();
//        $redis->add_code('27.abushyk.denys@ryanhs.org', 123456, 5);
//        $redis->add_code('SIGMA.SIGMVICH@GMAIL.COM', 1488, 1);
//        var_dump($redis->get_codes());

//          $quarter = preg_replace('/[^0-9]/', '', 'trhrthrt');
//          var_dump($quarter === '');
//
//        echo '<pre>';
//        print_r(VAPID::createVapidKeys());
//        echo '</pre>';
//
//        die();
//        $DBC = DBC::getInstance();

////        $notificator->schedule_notification($DBC, new \DateTime('now'), 'You teacher is absent!', 'Yours teacher, Mr. Sigma is absent for today!', '/assets/img/school_staff_pictures/S.SIGMOVICH.png', '/', '{"endpoint":"https://fcm.googleapis.com/fcm/send/d3KvqR8Gxow:APA91bGqGbfpKfqvfqvhf3ScG5_AX0YSsunR4hz-ip51RlICag9EEbHCeiJOaEzXmkA1hbl7QBMhT9e-Yo5R8mcBnxJRJT4gBONspvdSkEgMkNYDQMNlBDyajnBK5mipUv6DYm0Oz0WW","expirationTime":null,"keys":{"p256dh":"BJeAJ9DF8-6WJB3JPntw3BKmBr9lADm6F2CkUOhhVTkzlc0CIWpFDhWtzeoWbom9VACz10uSoy-kzhfg08r1Rkc","auth":"qfgoraLAJspjw8DCJN_p6Q"}}');
//        $this->view('pages/test.tpl');
//        $notificator->send_notification('🟢 You teacher is absent!', 'Yours teacher, Mr. Sigma is absent for today!', '/assets/img/school_staff_pictures/S.SIGMOVICH.png', '/', '{"endpoint":"https://fcm.googleapis.com/fcm/send/d3KvqR8Gxow:APA91bGqGbfpKfqvfqvhf3ScG5_AX0YSsunR4hz-ip51RlICag9EEbHCeiJOaEzXmkA1hbl7QBMhT9e-Yo5R8mcBnxJRJT4gBONspvdSkEgMkNYDQMNlBDyajnBK5mipUv6DYm0Oz0WW","expirationTime":null,"keys":{"p256dh":"BJeAJ9DF8-6WJB3JPntw3BKmBr9lADm6F2CkUOhhVTkzlc0CIWpFDhWtzeoWbom9VACz10uSoy-kzhfg08r1Rkc","auth":"qfgoraLAJspjw8DCJN_p6Q"}}');
//
//        $notificator->send_notification('You teacher is absent!', 'Yours teacher, Mr. Sigma is absent for today!', '/assets/img/school_staff_pictures/S.SIGMOVICH.png', '/', '{"endpoint":"https://fcm.googleapis.com/fcm/send/d3KvqR8Gxow:APA91bGqGbfpKfqvfqvhf3ScG5_AX0YSsunR4hz-ip51RlICag9EEbHCeiJOaEzXmkA1hbl7QBMhT9e-Yo5R8mcBnxJRJT4gBONspvdSkEgMkNYDQMNlBDyajnBK5mipUv6DYm0Oz0WW","expirationTime":null,"keys":{"p256dh":"BJeAJ9DF8-6WJB3JPntw3BKmBr9lADm6F2CkUOhhVTkzlc0CIWpFDhWtzeoWbom9VACz10uSoy-kzhfg08r1Rkc","auth":"qfgoraLAJspjw8DCJN_p6H"}}');
//        $notificator->send_notification('You teacher is absent!', 'Yours teacher, Mr. Sigma is absent for today!', '/assets/img/school_staff_pictures/S.SIGMOVICH.png', '/', '{"endpoint":"https://fcm.googleapis.com/fcm/send/d3KvqR8Gxow:APA91bGqGbfpKfqvfqvhf3ScG5_AX0YSsunR4hz-ip51RlICag9EEbHCeiJOaEzXmkA1hbl7QBMhT9e-Yo5R8mcBnxJRJT4gBONspvdSkEgMkNYDQMNlBDyajnBK5mipUv6DYm0Oz0WW","expirationTime":null,"keys":{"p256dh":"BJeAJ9DF8-6WJB3JPntw3BKmBr9lADm6F2CkUOhhVTkzlc0CIWpFDhWtzeoWbom9VACz10uSoy-kzhfg08r1Rkc","auth":"qfgoraLAJspjw8DCJN_p6H"}}');
//        $notificator->send_notification('You teacher is absent!', 'Yours teacher, Mr. Sigma is absent for today!', '/assets/img/school_staff_pictures/S.SIGMOVICH.png', '/', '{"endpoint":"https://fcm.googleapis.com/fcm/send/d3KvqR8Gxow:APA91bGqGbfpKfqvfqvhf3ScG5_AX0YSsunR4hz-ip51RlICag9EEbHCeiJOaEzXmkA1hbl7QBMhT9e-Yo5R8mcBnxJRJT4gBONspvdSkEgMkNYDQMNlBDyajnBK5mipUv6DYm0Oz0WW","expirationTime":null,"keys":{"p256dh":"BJeAJ9DF8-6WJB3JPntw3BKmBr9lADm6F2CkUOhhVTkzlc0CIWpFDhWtzeoWbom9VACz10uSoy-kzhfg08r1Rkc","auth":"qfgoraLAJspjw8DCJN_p6H"}}');
//        $notificator->send_notification('You teacher is absent!', 'Yours teacher, Mr. Sigma is absent for today!', '/assets/img/school_staff_pictures/S.SIGMOVICH.png', '/', '{"endpoint":"https://fcm.googleapis.com/fcm/send/d3KvqR8Gxow:APA91bGqGbfpKfqvfqvhf3ScG5_AX0YSsunR4hz-ip51RlICag9EEbHCeiJOaEzXmkA1hbl7QBMhT9e-Yo5R8mcBnxJRJT4gBONspvdSkEgMkNYDQMNlBDyajnBK5mipUv6DYm0Oz0WW","expirationTime":null,"keys":{"p256dh":"BJeAJ9DF8-6WJB3JPntw3BKmBr9lADm6F2CkUOhhVTkzlc0CIWpFDhWtzeoWbom9VACz10uSoy-kzhfg08r1Rkc","auth":"qfgoraLAJspjw8DCJN_p6H"}}');
//        $notificator->send_notification('You teacher is absent!', 'Yours teacher, Mr. Sigma is absent for today!', '/assets/img/school_staff_pictures/S.SIGMOVICH.png', '/', '{"endpoint":"https://fcm.googleapis.com/fcm/send/d3KvqR8Gxow:APA91bGqGbfpKfqvfqvhf3ScG5_AX0YSsunR4hz-ip51RlICag9EEbHCeiJOaEzXmkA1hbl7QBMhT9e-Yo5R8mcBnxJRJT4gBONspvdSkEgMkNYDQMNlBDyajnBK5mipUv6DYm0Oz0WW","expirationTime":null,"keys":{"p256dh":"BJeAJ9DF8-6WJB3JPntw3BKmBr9lADm6F2CkUOhhVTkzlc0CIWpFDhWtzeoWbom9VACz10uSoy-kzhfg08r1Rkc","auth":"qfgoraLAJspjw8DCJN_p6H"}}');
//

//
//        $webhook = new WebhookDiscord('1328169148534227045/BDGkqF-vSv26Rz2rQw3FXZHI9j4MLmibyFvwVnY5HpnJNvb4-YMozAGWAL5sGLgUqaFt');
//        $webhook->sendWebhook('sigma');

//        $DBC = DBC::getInstance();
//        $DBC->downloadTable('school_staff_list', 'test');
//
//        $redis = RedisOperator::getInstance();
//
//        var_dump($redis->get('absents:data'));
//        echo '<br>';
//        var_dump($redis->get('absents:md5'));
//        echo '<br>';
//        var_dump($redis->get('absents:date'));


//        $dbArray = [
//            'localhost' => ConfigTemp::getDatabaseLocal(),
//            'server' => ConfigTemp::getDatabaseServer()
//        ];
//        echo '<pre>';
//        var_dump($dbArray);
//        echo '</pre>';
//        echo '<br>';
//        var_dump(json_encode($dbArray));

//
//        $notificator = new Notificator();
//        for ($i = 0; $i < 800; $i++) {
//            $notificator->send_notification('You teacher is absent!', 'Yours teacher, Mr. Sigma is absent for today!', '/assets/img/school_staff_pictures/S.SIGMOVICH.png', '/', '{"endpoint":"https://fcm.googleapis.com/fcm/send/f_k-5G3w0jc:APA91bF17naEFLcyZtexjNzUHaZv1rM5lhUIErQ6DKmlTYFHtx95pz9K7E_LQJcTgp2of-nrwXK8l7IYvaF1KeNL_7ONFjZjL8iewopXgeEm9gomH0S9gI2-uMGMwPOuOuTQeiQG3qhd","expirationTime":null,"keys":{"p256dh":"BI3v3t_iZdDUCvWpW-axNSfMIkQ6E6zvEWLiu-teDOMYVsD3FrBerKt995bdKOjqQBJCSiW09IXdGivddbvxbyg","auth":"pgYUWIhjFv8_MGi1hAp2lQ"}}');
//        }


        // Display the output (raw text from the table)
        //var_dump($lines);// Use nl2br to keep line breaks for easier reading
//
//        function test($hours)
//        {
//            $maxGoodHours = 168;
//            $percentagePerHour = 10 / $maxGoodHours;
//            $badPercentagePerHourMultiplyer = 0.01;
//
//            // if > 90%
//            if ($hours <= $maxGoodHours) {
//                $result = 100 - ($percentagePerHour * $hours);
//            }
//            else {
//                $result = 100 - ($percentagePerHour * $maxGoodHours);
//                $hours -= $maxGoodHours;
//                for ($i = 1; $i <= $hours; $i++) {
//                    $percentagePerHour += $badPercentagePerHourMultiplyer;
//                    $result -=  $percentagePerHour;
//                    if ($result <= 0) {
//                        $result = 0.01;
//                    }
//                }
//            }
//            return $result;
//        }
//        echo test(290) . '%';
//        $this->view('pages/test.tpl');

        //$notificator = new Notificator();
        //$notificator->send_notification('🟢 A. Finnegan is out!', 'Yours teacher, Mr. Sigma present in school, but he will not teach you, how lucky!', '/assets/img/school_staff_pictures/Unknown.png', '/', '{"endpoint":"https://fcm.googleapis.com/fcm/send/f_k-5G3w0jc:APA91bF17naEFLcyZtexjNzUHaZv1rM5lhUIErQ6DKmlTYFHtx95pz9K7E_LQJcTgp2of-nrwXK8l7IYvaF1KeNL_7ONFjZjL8iewopXgeEm9gomH0S9gI2-uMGMwPOuOuTQeiQG3qhd","expirationTime":null,"keys":{"p256dh":"BI3v3t_iZdDUCvWpW-axNSfMIkQ6E6zvEWLiu-teDOMYVsD3FrBerKt995bdKOjqQBJCSiW09IXdGivddbvxbyg","auth":"pgYUWIhjFv8_MGi1hAp2lQ"}}');

        // ===============================
        $DBC = DBC::getInstance();
        $redis = RedisOperator::getInstance();
        $cacheOperator = new CacheOperator($DBC, $redis);
        echo '<pre>';
        var_dump(json_decode($cacheOperator->updateAbsents('absent_additional', '2025-02-27'), true));
        echo '</pre>';
    }
}