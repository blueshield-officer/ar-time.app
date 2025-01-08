<?php

namespace App\Services;

use Predis\Client;

class Redis_Operator
{
    static private function connect_to_redis()
    {
        $redis = new Client([
            'host' => 'Redis-7.2'
        ]);
        return $redis;
    }
    static public function get_old_md5(){
        $redis = self::connect_to_redis();
        return $redis->get('absents_md5');
    }
    static public function write_new_data($data_json){
        $redis = self::connect_to_redis();
        $data_to_record = [
            'absents_data' => $data_json,
            'absents_update_date' => (new \DateTime('now'))->format('h:i a, F d'),
            'absents_md5' => md5($data_json),
        ];
        foreach ($data_to_record as $key => $value) {
            $redis->set($key, $value);
        }
        echo '<br>';
        echo 'Redis:';
        echo '<br>';
        echo $redis->get('absents_update_date') . '<br>';
        echo $redis->get('absents_md5') . '<br>';
        echo $redis->get('absents_data') . '<br>';
    }
}