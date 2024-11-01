<?php

namespace Smartcat\Includes\Services\Posts;

use Smartcat\Includes\Requests\AllPostsRequest;
use Smartcat\Includes\Services\Interfaces\PostsDatabaseInterface;
use Smartcat\Includes\Services\Interfaces\PostTypeInterface;
use Smartcat\Includes\Services\Interfaces\WpmlInterface;

class PostsForVerifyService
{
    /**
     * @var WpmlInterface
     */
    private $wpmlService;

    /**
     * @var PostsDatabaseInterface
     */
    private $postsDatabaseService;

    /** @var PostsDatabaseInterface */
    private $postTypeService;

    public function __construct(
        WpmlInterface          $wpmlService,
        PostsDatabaseInterface $postsDatabaseService,
        PostTypeInterface      $postTypeService
    )
    {
        $this->wpmlService = $wpmlService;
        $this->postsDatabaseService = $postsDatabaseService;
        $this->postTypeService = $postTypeService;
    }

    public function getPosts(AllPostsRequest $request): array
    {
        $this->wpmlService->switchLang($request->getLang());
        $posts = $this->postsDatabaseService->getPosts($this->getArgs());
        return $this->map($posts, $request->getLang());
    }

    private function map(array $posts, string $sourceLang): array
    {
        return array_map(function ($post) use ($sourceLang) {
            return [
                'id' => $post->ID,
                'type' => $post->post_type,
                'lang' => $sourceLang
            ];
        }, $posts);
    }

    private function getArgs(): array
    {
        return [
            'post_type' => $this->postTypeService->getTranslatableTypes(),
            'numberposts' => -1,
            'suppress_filters' => false,
        ];
    }
}