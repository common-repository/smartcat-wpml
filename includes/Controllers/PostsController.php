<?php

namespace Smartcat\Includes\Controllers;

use Smartcat\Includes\Requests\AllPostsRequest;
use Smartcat\Includes\Requests\ImportPostsRequest;
use Smartcat\Includes\Requests\PullPostsRequest;
use Smartcat\Includes\Requests\PushPostsRequest;
use Smartcat\Includes\Services\Categories\DatabaseService as CategoriesDatabaseService;
use Smartcat\Includes\Services\Posts\PostExportService;
use Smartcat\Includes\Services\Posts\PostsForVerifyService as PostsForVerifyService;
use Smartcat\Includes\Services\Posts\ImportService as ImportService;
use Smartcat\Includes\Services\Posts\DatabaseService as PostsDatabase;
use Smartcat\Includes\Services\Posts\PostTypeService as PostTypeService;
use Smartcat\Includes\Services\Posts\PullService as PullService;
use Smartcat\Includes\Services\Posts\PushService as PushService;
use Smartcat\Includes\Services\Posts\PostsWithLocaleTridService as PostsWithLocaleTridService;
use Smartcat\Includes\Services\Metadata\MetadataService;
use Smartcat\Includes\Services\Wpml as WpmlService;
use Smartcat\Includes\Services\Categories\ImportService as CategoriesImportService;
use WP_REST_Request;

class PostsController
{
    /**
     * @var PullService
     */
    private $pullService;

    /**
     * @var PushService
     */
    private $pushService;

    /**
     * @var ImportService
     */
    private $importService;

    /**
     * @var CategoriesImportService
     */
    private $categoriesImportService;

    /**
     * @var PostExportService
     */
    private $allPostsService;

    /**
     * @var PostsForVerifyService
     */
    private $verifyPostsService;

    /**
     * @var PostsWithLocaleTridService
     */
    private $withLocaleTridService;

    public function __construct()
    {
        $this->pullService = new PullService(
            new PostsDatabase(),
            new WpmlService(),
            new MetadataService(),
            new PostTypeService()
        );
        $this->pushService = new PushService(
            new WpmlService(),
            new MetadataService(),
            new PostsDatabase()
        );
        $this->importService = new ImportService(
            new WpmlService(),
            new PostsDatabase(),
            new CategoriesDatabaseService(),
            new PostTypeService(),
            new MetadataService()
        );
        $this->categoriesImportService = new CategoriesImportService(
            new WpmlService(),
            new CategoriesDatabaseService()
        );
        $this->allPostsService = new PostExportService(
            new WpmlService(),
            new PostsDatabase(),
            new PostTypeService()
        );
        $this->verifyPostsService = new PostsForVerifyService(
            new WpmlService(),
            new PostsDatabase(),
            new PostTypeService()
        );
        $this->withLocaleTridService = new PostsWithLocaleTridService(
            new WpmlService(),
            new PostsDatabase()
        );
    }

    /**
     * @throws \Exception
     */
    public function pull(WP_REST_Request $request): array
    {
        return [
            'lastModifiedDate' => current_time('Y-m-d H:i:s'),
            'posts' => $this->pullService->getPosts(new PullPostsRequest($request))
        ];
    }

    public function push(WP_REST_Request $request): array
    {
        $this->pushService->push(new PushPostsRequest($request));
        return [];
    }

    public function import(WP_REST_Request $request): array
    {
        return [
            'documents' => $this->importService->import(new ImportPostsRequest($request)),
            'categories' => [] // $this->categoriesImportService->import(new ImportPostsRequest($request))
        ];
    }

    public function all(WP_REST_Request $request): array
    {
        return $this->allPostsService->getPosts(new AllPostsRequest($request));
    }

    public function originalPostsForVerify(WP_REST_Request $request): array
    {
        return $this->verifyPostsService->getPosts(new AllPostsRequest($request));
    }

    public function withLocaleTrid(): array
    {
        return $this->withLocaleTridService->getPosts();
    }

    public function translatableTypes(): array
    {
        return [
            'all' => get_post_types(),
            'only_public' => get_post_types(['public' => true,]),
            'smartcat_detected' => smartcat_post_type()->getTranslatableTypes()
        ];
    }
}