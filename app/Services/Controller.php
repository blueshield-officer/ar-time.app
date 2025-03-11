<?php

namespace App\Services;


class Controller
{
    protected $view;
    public function __construct()
    {
        $this->view = new \Smarty();
        $this->view->setTemplateDir($_SERVER['DOCUMENT_ROOT'] . '/view/');
        $this->view->setCompileDir($_SERVER['DOCUMENT_ROOT'] . '/cache/templates_c/');
        $this->view->setConfigDir($_SERVER['DOCUMENT_ROOT'] . '/cache/configs/');
        $this->view->setCacheDir($_SERVER['DOCUMENT_ROOT'] . '/cache/cache/');

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