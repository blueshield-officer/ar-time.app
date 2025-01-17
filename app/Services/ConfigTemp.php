<?php

namespace App\Services;

class ConfigTemp
{
    public static function getDatabaseLocal(){
        return [
            'db' => [
                'host' => 'MySQL-5.5',
                'name' => 'artime',
                'user' => 'root',
                'password' => '',
                'port' => '3306'
            ]
        ];
    }
    public static function getDatabaseServer(){
        return [
            'db' => [
                'host' => 'localhost',
                'name' => 'ar_time_org',
                'user' => 'ar_time_org',
                'password' => '8NBXX75IrbQ2ZtSi',
                'port' => '3306'
            ]
        ];
    }
    public static function getRedisLocal(){
        return [
            'host' => 'Redis-7.2'
        ];
    }
}