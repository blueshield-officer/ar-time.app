<?php

namespace App\Services;

use App\Controllers\Error;

class Router
{
    private static $list = [];
    public static function page($uri, $page_class, $security_level = 'server')
    {
        self::$list[] = [
            'uri' => $uri,
            'page_class' => $page_class,
            'security_level' => $security_level
        ];
    }

    public static function enable(){
        new Session();

        // /////////////////////////// cookies ////////////////////////////////
        if (!isset($_COOKIE['sideBar'])){
            $_COOKIE['sideBar'] = 1;
        }
        $_COOKIE['sideBar'] = (int)$_COOKIE['sideBar'];

        if (!isset($_COOKIE['theme'])){
            $_COOKIE['theme'] = 'light';
        }

        if (!isset($_COOKIE['tabCounter'])){
            $_COOKIE['tabCounter'] = false;
        }
        ////////////////////////////////////////////////////////////////


        $query = preg_replace('/\/+/', '/', $_SERVER['REQUEST_URI']);
        if ($query !== '/'){
            $query = rtrim($query, '/');
        }
        foreach (self::$list as $route) {
            // =======================
            // Flexible routing (dates)
            // =======================

            if (preg_match('/\{(date(MDY|MY))\}/', $route['uri'], $matchesDateType) === 1) {
                $dateType = "date" . $matchesDateType[2];
                $patterns = [
                    '{dateMDY}' => '(\d{1,2}-\d{1,2}-\d{4})',
                    '{dateMY}'  => '(\d{1,2}-\d{4})'
                ];
                $regex = str_replace(array_keys($patterns), array_values($patterns), $route['uri']);

                if (preg_match("#^$regex$#", $query, $matches)) {
                    $controller_name = $route['page_class'][0];
                    $date = $matches[1];

                    $dateObject = explode('-', $date);
                    if ($dateType === 'dateMDY') {
                        if (!checkdate($dateObject[0], $dateObject[1], $dateObject[2])){
                            self::error_page(400);
                            die();
                        }
                    }
                    else {
                        if (!checkdate($dateObject[0], 1, $dateObject[1])){
                            self::error_page(400);
                            die();
                        }
                    }

                    if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $controller_name . '.php')) {
                        self::error_page(400);
                        die();
                    }

                    $controller = new $controller_name();
                    if (isset($route['page_class'][1])) {
                        $action_name = $route['page_class'][1];
                        if (!method_exists($controller_name, $action_name)) {
                            throw new \Exception('Action not found');
                        }
                        $controller->$action_name($date);
                    } else {
                        $controller->execute($date);
                    }
                    die();
                }
            }

            // =======================
            // Standard routing
            // =======================
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
            // security levels check
            if ($route['security_level'] === 'account' && !Session::isLogedIn()){
                self::error_page(403);
                die();
            }
            //
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
        http_response_code($code);
        $controller = new Error();
        $controller->execute($code);
    }
}