<?php
class App
{
  private static $PDO = null;
  public static function getPDO(){
    if (self::$PDO == null){
      $config = self::getConfig();
      $dsn = 'mysql:dbname='.$config['db']['name'].';host='.$config['db']['host'];
      $user = $config['db']['user'];
      $password = $config['db']['password'];
      self::$PDO = new PDO($dsn, $user, $password, [
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
      ]);
    }
    return self::$PDO;
  }

  public static function getConfig(){
    $config = require_once __DIR__.'/../config.php';
    return $config;
  }
}