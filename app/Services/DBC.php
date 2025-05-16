<?php

namespace App\Services;

use PDO;
use phpseclib3\Math\BigInteger\Engines\GMP\DefaultEngine;

class DBC
{
    private static $instance = null;
    private $pdo;
    private $ignoreColumns = [
        'default' => ['id'],
        'public' => ['id', 'keep_update', 'md5_key']
    ];

    private function __construct()
    {
        try {
            $dsn = 'mysql:dbname=' . $_ENV['DB_NAME'] . ';host=' . $_ENV['DB_HOST'];
            $user = $_ENV['DB_USER'];
            $password = $_ENV['DB_PASSWORD'];
            $this->pdo = new \PDO($dsn, $user, $password, [
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
            ]);
        }
        catch (PDOException $e) {
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
    }
    public function getTableParams($tableName, $type = 'default')
    {
        $result = $this->fetchAll(
            $this->query('SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = ?', [$tableName])
        );
        $tableColumns = [];
        foreach ($result as $columnHash) {
            $columnName = $columnHash['COLUMN_NAME'];
            if (!in_array($columnName, $this->ignoreColumns[$type])) {
                $tableColumns[] = $columnName;
            }
        }
        return [
            'columnsArray' => $tableColumns,
            'columns' => join(', ', array_map(function ($column) {
                return '`' . $column . '`';
            }, $tableColumns)),
            'placeholders' => $type === 'default' ? join(', ', array_fill(0, count($tableColumns), '?')) : null,
        ];
    }
    public function getLastInsertID(){
        return $this->pdo->lastInsertId();
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
