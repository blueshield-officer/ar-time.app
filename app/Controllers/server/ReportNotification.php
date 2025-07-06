<?php

namespace App\Controllers\server;

use App\Services\CacheOperator;
use App\Services\DBC;
use App\Services\Notificator;
use App\Services\RedisOperator;
use App\Services\Utils;
use DateTime;
use Google\Service\AdMob\Date;

class ReportNotification
{
    public function execute(){
        $DBC = DBC::getInstance();
        $redis = RedisOperator::getInstance();
        $cacheOperator = new CacheOperator($DBC, $redis);

        $nextSchedule = $cacheOperator->fetch('calendar_schedules', 'next', false);
        $currentTime = date('H:i') . ':00';


        if (date('Y-m-d', strtotime('+1 day')) === $nextSchedule['date']){
            $notificator = new Notificator();

            $result = $DBC->fetchAll(
                $DBC->query(
                    'SELECT `notification_token` FROM `users_sessions` WHERE 
                                                      `notification` = ? AND 
                                                      `notification_report` = ? AND 
                                                      `notification_report_time` = ? AND
                                                      `last_visit` > ?',
                    [1, 1, $currentTime, Notificator::getMinVisitDate()]
                )
            );
            foreach ($result as $object) {
                $token = $object['notification_token'];

                $notificator->sendNotification(
                    $token,
                    (DateTime::createFromFormat('Y-m-d', $nextSchedule['date']))->format('l, F j'),
                    'Tomorrow is ' . strtoupper($nextSchedule['schedule']) . ' Schedule' . (($nextSchedule['testDay'] !== null) ? ' and a Day '. $nextSchedule['testDay'] : ''),
                );
            }
        }
    }
}