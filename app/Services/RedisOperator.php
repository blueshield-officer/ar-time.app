<?php

namespace App\Services;

use Predis\Client;

class RedisOperator
{
    static private $instance = null;
    private $redis;

    private $websiteKey = 'ar_time';

    private function __construct()
    {
        try {
            $this->redis = new Client([
                'host' => $_ENV['REDIS_HOST'],
                'port' => $_ENV['REDIS_PORT'],
            ]);
            $this->redis->select($_ENV['REDIS_DB']);
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
    public function set($key, $value, $lifetime = null){
        if ($lifetime === null) {
            $this->redis->set($this->websiteKey . ':' . $key, $value);
        }
        else {
            $this->redis->setex($this->websiteKey . ':' . $key, $lifetime, $value);
        }
    }

    public function set_object($key, $value, $objectName, $lifetime = null)
    {
        $this->redis->hset($this->websiteKey . ':' . $objectName . ':test', $key, $value);

        if ($lifetime != null) {
            $this->redis->setex($this->websiteKey . ':' . $objectName . $key . ':expiry', $lifetime, $value);
        }
    }
    public function get($key, $objectName = null){
        if (is_null($objectName)) {
            return $this->redis->get($this->websiteKey . ':' . $key);
        }
        else {
            return $this->redis->get($this->websiteKey . ':' . $objectName . ':' . $key);
        }
    }
    public function delete($key){
        $this->redis->del($this->websiteKey . ':' . $key);
    }
}