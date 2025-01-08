<?php

namespace App\Services;

class Controller
{
    protected $view;
    public function __construct()
    {
        $this->view = new \Smarty();
        $this->view->setTemplateDir($_SERVER['DOCUMENT_ROOT'] . '/templates/');
        $this->view->setCompileDir($_SERVER['DOCUMENT_ROOT'] . '/cache/templates_c/');
        $this->view->setConfigDir($_SERVER['DOCUMENT_ROOT'] . '/cache/configs/');
        $this->view->setCacheDir($_SERVER['DOCUMENT_ROOT'] . '/cache/cache/');
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