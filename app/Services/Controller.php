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
        $this->view->registerPlugin('function', 'unsetArrayObject', function ($data){
            if (!is_array($data['array']) || !isset($data['index'])) {
                return $data['array']; // Return unchanged if invalid input
            }

            $array = $data['array'];
            unset($array[$data['index']]); // Remove key
            return $array; // Return modified array
        });
        $this->view->registerPlugin("function", "uniqueId", function () {
            return md5(uniqid(rand(), true));
        });
        $this->view->registerPlugin('function', 'capitalize', function ($array){
            return ucwords($array['str']);
        });
        $this->view->registerPlugin('function', 'format', function ($array){
            return preg_replace('/\s+/', '', $array['str']);
        });
        $this->view->registerPlugin('function', 'periodToNumber', function ($array){
            $period = $array['p'];
            if (str_contains(strtolower($period), 'period')) {
                preg_match('/\d+/', $period, $matches);
                return (int)$matches[0];
            }
            else {
                return $period;
            }
        });
        $this->view->registerPlugin('function', 'array_keys', function ($array){
            $array = $array['array'];
            return array_keys($array);
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
        $this->view->registerPlugin("function", "gradeIntToClassStr", function ($array) {
            $grade = $array['grade'];
            return match ($grade) {
                9 => 'freshman',
                10 => 'sophomore',
                11 => 'junior',
                12 => 'senior',
                default => $grade,
            };
        });
        $this->view->registerPlugin("function", "gradeIntToStr", function ($array) {
            $grade = $array['grade'];
            $plural = $array['plural'];
            if ($plural === '1'){
                return match ($grade) {
                    9 => 'Freshmen',
                    10 => 'Sophomores',
                    11 => 'Juniors',
                    12 => 'Seniors',
                    default => $grade,
                };
            }
            else {
                return match ($grade) {
                    9 => 'Freshman',
                    10 => 'Sophomore',
                    11 => 'Junior',
                    12 => 'Senior',
                    default => $grade,
                };
            }
        });
        $this->view->registerPlugin("function", "dateYtsTojfY", function ($array) {
            $date = $array['date'];
            return (\DateTime::createFromFormat('Y-m-d', $date))->format('F j, Y');
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
        exit();
    }
    protected function render($template, $data = [])
    {
        $this->view->assign($data);
        return $this->view->fetch($template);
    }

    static function renderStatic($template, $data = [])
    {
        $smarty = new \Smarty();
        $smarty->setTemplateDir($_SERVER['DOCUMENT_ROOT'] . '/view/');
        $smarty->setCompileDir($_SERVER['DOCUMENT_ROOT'] . '/view/cache/templates_c/');
        $smarty->setConfigDir($_SERVER['DOCUMENT_ROOT'] . '/view/cache/configs/');
        $smarty->setCacheDir($_SERVER['DOCUMENT_ROOT'] . '/view/cache/cache/');

        $smarty->assign($data);
        return $smarty->fetch($template);
    }
}