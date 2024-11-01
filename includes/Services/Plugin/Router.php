<?php

namespace Smartcat\Includes\Services\Plugin;

class Router
{
    public function registerRoutes()
    {
        require_once plugin_dir_path(dirname(__FILE__)) . '../../routes.php';
    }

    public function route(string $method, string $uri, string $controller, string $action, string $middleware = '', array $validationParams = [])
    {
        $routeData = [
            'methods' => $method,
            'callback' => [new $controller(), $action],
            'args' => [],
            'permission_callback' => '__return_true'
        ];

        foreach ($validationParams as $key => $validateCallback) {
            $routeData['args'][$key] = [
                'validate_callback' => $validateCallback
            ];
        }

        register_rest_route(SMARTCAT_API_PREFIX, "/$uri", $routeData);

        return $this;
    }
}