<?php

namespace App\Services;

use App\Controllers\Error;
use App\Controllers\Legacy;
use Dotenv\Dotenv;

class Router
{
    private static $list = [];
    public static function page($uri, $page_class, $security_level = 'server', $get_request = 'none')
    {
//        $page_class[0] = str_replace('\\', '/', $page_class[0]);
//        foreach (explode('/', $page_class[0]) as $directory) {
//            if ($directory ==)
//        }
//        $page_class[0] = str_replace('App/', 'app/', $page_class[0]);

        self::$list[] = [
            'uri' => $uri,
            'page_class' => $page_class,
            'security_level' => $security_level
        ];
    }

    public static function enable(){
        //  ----------------   time zone   ----------------
        date_default_timezone_set('America/New_York');
        //  -------------------------------------------------

        //  ------------------   errors   ------------------
        error_reporting(E_ALL);
        ini_set('display_errors','On');
//      ini_set('display_errors', 0);
        //  ------------------------------------------------

        //  --------------------   ENV   --------------------
        $dotenv = Dotenv::createImmutable('../');
        $dotenv->load();
        //  -------------------------------------------------

        //  -------------------   DBC   --------------------
        $DBC = DBC::getInstance();
        //  -------------------------------------------------

        //  -----------------   session   -----------------
        $session = new Session($DBC);
        $session->cookieLogin();
        $session->loadDate();
        //  ----------------------------------------------

        //  ----------------   cookies   ----------------
        self::setDefaultCookie('sideBar', 1);
        self::setDefaultCookie('theme', 'system');
        self::setDefaultCookie('tabCounter', false);
        self::setDefaultCookie('overlayOnSleep', false);
        self::setDefaultCookie('overlayOnSleepTime', 5);
        //  ---------------------------------------------
        

        //  ------------------   routing   ------------------
        $query = preg_replace('/\/+/', '/', $_SERVER['REQUEST_URI']);

        // TODO fix
        $query = parse_url($query, PHP_URL_PATH);

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

//                    if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/' . $controller_name . '.php')) {
//                        self::error_page(400);
//                        die();
//                    }

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

            //
            // Standard routing
            //
            if (is_array($route['uri'])){
                if (!in_array($query, $route['uri'])){
                    continue;
                }
            }
            // check is equal to string
            else if ($route['uri'] !== $query){
                continue;
            }

            // security levels check
            if ($route['security_level'] === 'account' && !Session::isLogedIn()){
                self::error_page(403);
                die();
            }
            if ($route['security_level'] === 'noAccount' && Session::isLogedIn()){
                self::main_page();
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

            $controller = new $controller_name();

            if (isset($action_name)){
                if (!method_exists($controller_name, $action_name)){
                    throw new \Exception('Action not found');
                }
                $controller->$action_name();
            }
            else {
                $controller->execute();
            }
            die();
        }
        self::error_page(404);
    }

    private static function setDefaultCookie($name, $value)
    {
        if (!isset($_COOKIE[$name])){
            $_COOKIE[$name] = $value;
        }
    }
    private static function main_page(){
        header('Location: /');
        exit();
    }
    private static function error_page($code)
    {
        http_response_code($code);
        $controller = new Error();
        $controller->execute($code);
    }
}