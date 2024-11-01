<?php

namespace Smartcat\Includes\Controllers;

use Smartcat\Includes\Requests\PullCategoriesRequest;
use Smartcat\Includes\Requests\PushCategoriesRequest;
use Smartcat\Includes\Services\Categories\DatabaseService;
use Smartcat\Includes\Services\Categories\PullService as PullService;
use Smartcat\Includes\Services\Categories\PushService as PushService;
use Smartcat\Includes\Services\Wpml;
use WP_REST_Request;

class CategoriesController
{
    /**
     * @var PullService
     */
    private $pullService;

    private $pushService;

    public function __construct()
    {
        $this->pullService = new PullService(
            new Wpml(),
            new DatabaseService()
        );
        $this->pushService = new PushService(
            new Wpml(),
            new DatabaseService()
        );
    }

    public function pull(WP_REST_Request $request): array
    {
        return $this->pullService->getCategories(new PullCategoriesRequest($request));
    }

    public function push(WP_REST_Request $request)
    {
        $this->pushService->push(new PushCategoriesRequest($request));
        return [];
    }
}