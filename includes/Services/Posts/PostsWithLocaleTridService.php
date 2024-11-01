<?php

namespace Smartcat\Includes\Services\Posts;

use Smartcat\Includes\Services\Interfaces\PostsDatabaseInterface;
use Smartcat\Includes\Services\Interfaces\WpmlInterface;

class PostsWithLocaleTridService
{
    /**
     * @var WpmlInterface
     */
    private $wpmlService;

    /**
     * @var PostsDatabaseInterface
     */
    private $postsDatabaseService;

    public function __construct(
        WpmlInterface          $wpmlService,
        PostsDatabaseInterface $postsDatabaseService
    )
    {
        $this->wpmlService = $wpmlService;
        $this->postsDatabaseService = $postsDatabaseService;
    }

    public function getPosts(): array
    {
        $posts = $this->postsDatabaseService->getPosts($this->getArgs());
        return $this->map($posts);
    }

    private function map(array $posts): array
    {
        return array_map(function ($post) {
            return [
                'id' => $post->ID,
                'locale' => $this->wpmlService->getPostLocale($post->ID),
                'groupId' => (int)$this->wpmlService->getTrid($post->ID, "post_$post->post_type") // FIXME: mb trid??
            ];
        }, $posts);
    }

    private function getArgs(): array
    {
        return [
            'post_type' => ['post', 'page'],
            'numberposts' => -1,
            'suppress_filters' => true,
        ];
    }
}