<?php

namespace App\Services;


class Controller
{
    protected $view;
    public function __construct()
    {
        $this->view = new \Smarty();
        $this->view->setTemplateDir($_SERVER['DOCUMENT_ROOT'] . '/view/');
        $this->view->setCompileDir($_SERVER['DOCUMENT_ROOT'] . '/view/cache/templates_c/');
        $this->view->setConfigDir($_SERVER['DOCUMENT_ROOT'] . '/view/cache/configs/');
        $this->view->setCacheDir($_SERVER['DOCUMENT_ROOT'] . '/view/cache/cache/');

        // plugins
        $this->view->registerPlugin("function", "uniqueId", function () {
            return md5(uniqid(rand(), true));
        });
        $this->view->registerPlugin('function', 'capitalize', function ($array){
            return ucwords($array['str']);
        });
        $this->view->registerPlugin('function', 'format', function ($array){
            return preg_replace('/\s+/', '', $array['str']);
        });
        $this->view->registerPlugin('function', 'findBellTime', function ($array){
            $bells = $array['bells'];
            $type = $array['type'];
            if ($type === 'start'){
                $lastPeriod = $bells[array_key_first($bells)];
                if (!is_null($lastPeriod['start_unix'])){
                    $unix = $lastPeriod['start_unix'];
                }
                else {
                    $unix = $lastPeriod['end_unix'];
                }
            }
            else {
                $lastPeriod = $bells[array_key_last($bells)];
                if (!is_null($lastPeriod['end_unix'])){
                    $unix = $lastPeriod['end_unix'];
                }
                else {
                    $unix = $lastPeriod['start_unix'];
                }
            }
            $hour = $unix / 60 / 60;
            return ($type === 'start') ? floor($hour) : ceil($hour);
        });
        $this->view->registerPlugin('function', 'gradeToClass', function ($array){
            $str = $array['str'];
            if ($str === 'freshmen'){
                return 'freshman';
            }
            return strtolower(substr($str, 0, -1));
        });
    }
    protected function assign($key, $value)
    {
        $this->view->assign($key, $value);
    }
    protected function view($template, $data = [])
    {
        $this->view->assign($data);
        $this->view->display($template);
    }
    protected function redirect($url){
        header('Location: ' . $url);
    }
    protected function render($template, $data = [])

    {}
}