<?php

namespace Smartcat\Publicly;
use Smartcat\Includes\Services\Plugin\Router;

class SmartcatPublic
{
    private $router;

    public function __construct()
    {
        $this->router = new Router();
    }

    public function routes()
    {
        $this->router->registerRoutes();
    }
}
