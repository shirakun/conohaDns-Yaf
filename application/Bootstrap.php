<?php

class Bootstrap extends Yaf\Bootstrap_Abstract
{
    private $config;

    public function _initConfig(Yaf\Dispatcher $dispatcher)
    {
        $this->config = Yaf\Application::app()->getConfig();
        Yaf\Registry::set("config", $this->config);
    }

    public function _initAutoload(yaf\Dispatcher $dispatcher)
    {
        if (file_exists(BASE_PATH . "vendor/autoload.php")) {
            Yaf\loader::import(BASE_PATH . "vendor/autoload.php");
        }
    }

}
