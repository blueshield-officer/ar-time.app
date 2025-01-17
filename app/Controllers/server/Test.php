<?php

namespace App\Controllers\server;

use App\Services\ConfigTemp;
use App\Services\Controller;
use App\Services\DBC;
use App\Services\Notificator;
use App\Services\RedisOperator;
use App\Services\SQL;
use App\Services\WebhookDiscord;
use Minishlink\WebPush\VAPID;
use Redis;

class Test extends Controller
{
    public function execute(){
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
//        $notificator = new Notificator();
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
//        $notificator->send_notification('You teacher is absent!', 'Yours teacher, Mr. Sigma is absent for today!', '/assets/img/school_staff_pictures/S.SIGMOVICH.png', '/', '{"endpoint":"https://fcm.googleapis.com/fcm/send/d3KvqR8Gxow:APA91bGqGbfpKfqvfqvhf3ScG5_AX0YSsunR4hz-ip51RlICag9EEbHCeiJOaEzXmkA1hbl7QBMhT9e-Yo5R8mcBnxJRJT4gBONspvdSkEgMkNYDQMNlBDyajnBK5mipUv6DYm0Oz0WW","expirationTime":null,"keys":{"p256dh":"BJeAJ9DF8-6WJB3JPntw3BKmBr9lADm6F2CkUOhhVTkzlc0CIWpFDhWtzeoWbom9VACz10uSoy-kzhfg08r1Rkc","auth":"qfgoraLAJspjw8DCJN_p6Q"}}');


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

        $DBC = DBC::getInstance();
    }
}