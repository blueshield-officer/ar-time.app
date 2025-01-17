<?php

namespace App\Services;

use Predis\Client;

class RedisOperator
{
    static private $instance = null;
    private $redis;

    private function __construct()
    {
        try {
            $this->redis = new Client([
                'host' => 'Redis-7.2'
            ]);
        } catch (\RedisException $e) {
            throw new \RedisException('Unable to connect to: ' . $e->getMessage());
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    public function add($key, $value){
        $this->redis->set($key, $value);
    }

    public function add_object($key, $value, $objectName, $lifetime = null)
    {
        $this->redis->hset($objectName . ':test', $key, $value);

        if ($lifetime != null) {
            $this->redis->setex($objectName . $key . ':expiry', $lifetime, $value);
        }
    }
    public function get($key, $objectName = null){
        if (is_null($objectName)) {
            return $this->redis->get($key);
        }
        else {
            return $this->redis->get($objectName . $key);
        }
    }
    public function delete($key){
        $this->redis->del($key);
    }
    public function get_codes()
    {
        $codes = $this->redis->hgetall('codes');
        foreach ($codes as $email => $code) {
            $expiryKey = 'codes:' . $email . ':expiry';
            if (!$this->redis->exists($expiryKey)) {
                $this->redis->hdel('codes', $email);
            }
        }
        return $this->redis->hgetall('codes');
    }
}