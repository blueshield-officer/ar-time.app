<?php

namespace App\Services;

use PDO;

class DBC
{
    private static $instance = null;
    private $pdo;

    private function __construct()
    {
        try {
            $config = json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/../DBconfig.json'), true);
            $type = 'localhost';
//            $type = 'server';
            $dsn = 'mysql:dbname=' . $config[$type]['name'] . ';host=' . $config[$type]['host'];
            $user = $config[$type]['user'];
            $password = $config[$type]['password'];
            $this->pdo = new \PDO($dsn, $user, $password, [
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
            ]);
        }
        catch (PDOException $e) {
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
    }
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    public function query($query, $params = array())
    {
        $success = false;
        $stmt = $this->exec($query, $success, $params);
        return $success ? $stmt : false;
    }
    public function fetch($stmt){
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    public function fetchAll($stmt){
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    public function downloadTable($table, $filename)
    {
        var_dump($_SERVER['DOCUMENT_ROOT'] . '/sql_csv/' . $filename . '.csv');
        $filePath = $_SERVER['DOCUMENT_ROOT'] . '/sql_csv/' . $filename . '.csv';
        $output = fopen($filePath, 'w');

        $result = $this->query('SHOW COLUMNS FROM ' . $table);

        if ($result->rowCount() > 0) {
            $columns = [];
            while ($row = $this->fetch($result)) {
                $columns[] = $row['Field'];
            }
            fputcsv($output, $columns);
        }

        $result = $this->query('SELECT * FROM ' . $table);

        if ($result->rowCount() > 0) {
            while ($row = $this->fetch($result)) {
                fputcsv($output, $row);
            }
        }
        fclose($output);
    }




    private function exec($sql, &$success, $params = array())
    {
        $stmt = $this->pdo->prepare($sql);

        if (is_array($params)) {
            for ($i = 0; $i < count($params); $i++) {
                $stmt->bindParam($i + 1, $params [$i], $this->paramType($params [$i]));
            }
        }
        try {
            $success = $stmt->execute();
        } catch (\PDOException $e) {
            $success = false;
            throw new \PDOException($e->getMessage());
        }
        return $stmt;
    }
    private function paramType($param)
    {

        if (is_numeric($param)) {
            if (is_int($param)) {
                return PDO::PARAM_INT;
            } else {
                return PDO::PARAM_STR;
            }
        } elseif (is_null($param)) {
            return PDO::PARAM_NULL;
        } elseif (is_bool($param)) {
            return PDO::PARAM_BOOL;
        }

        return PDO::PARAM_STR;
    }
}
