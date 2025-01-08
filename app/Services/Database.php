<?php

namespace App\Services;

class Database
{
    public static function getInfoLocal(){
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
    public static function getInfoServer(){
        return [
            'db' => [
                'host' => 'localhost',
                'name' => 'ar_time_org',
                'user' => 'ar_time_org',
                'password' => 'dvcd4pzI1TwnQ8kT',
                'port' => '3306'
            ]
        ];
    }
}