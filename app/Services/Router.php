<?php

namespace App\Services;

use App\Controllers\Error;

class Router
{
    private static $list = [];
    public static function page($uri, $page_class)
    {
        self::$list[] = [
            'uri' => $uri,
            'page_class' => $page_class
        ];
    }

    public static function enable(){
        session_start();
        $query = $_SERVER['REQUEST_URI'];
        foreach (self::$list as $route) {
            // check is in array
            if (is_array($route['uri'])){
                if (!in_array($query, $route['uri'])){
                    continue;
                }
            }
            // check is equal to string
            else if ($route['uri'] !== $query){
                continue;
            }
            $controller_name = $route['page_class'][0];
            // If more than 2 arguments, error and die
            if (count($route['page_class']) > 2){
                self::error_page(500);
                die();
            }
            if (isset($route['page_class'][1])){
                $action_name = $route['page_class'][1];
            }
            if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $controller_name . '.php')){
                self::error_page(404);
                die();
            }
            $controller = new $controller_name();
            if (isset($action_name)){
                if (!method_exists($controller_name, $action_name)){
                    throw new \Exception('Action not found');
                }
                $controller->$action_name();
            }
            else{
                $controller->execute();
            }
            die();
        }
        self::error_page(404);
    }
    private static function error_page($code)
    {
        $controller = new Error();
        $controller->execute($code);
    }
}