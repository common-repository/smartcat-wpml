<?php

namespace Smartcat\Includes\Services\Posts;

use Smartcat\Includes\Requests\AllPostsRequest;
use Smartcat\Includes\Services\Interfaces\PostsDatabaseInterface;
use Smartcat\Includes\Services\Interfaces\PostTypeInterface;
use Smartcat\Includes\Services\Interfaces\WpmlInterface;

class PostExportService
{
    /**
     * @var WpmlInterface
     */
    private $wpmlService;

    /**
     * @var PostsDatabaseInterface
     */
    private $postsDatabaseService;

    /** @var PostTypeInterface */
    private $postTypesService;

    public function __construct(
        WpmlInterface          $wpmlService,
        PostsDatabaseInterface $postsDatabaseService,
        PostTypeInterface      $postTypeService
    )
    {
        $this->wpmlService = $wpmlService;
        $this->postsDatabaseService = $postsDatabaseService;
        $this->postTypesService = $postTypeService;
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
                'type' => "post_$post->post_type",
                'trid' => $this->wpmlService->getTrid($post->ID, "post_$post->post_type"),
                'title' => $post->post_title,
                'languages' => $this->wpmlService->getElementLanguages($post->ID, [$sourceLang], "post_$post->post_type")
            ];
        }, $posts);
    }

    private function getArgs(): array
    {
        return [
            'post_type' => $this->postTypesService->getTranslatableTypes(),
            'numberposts' => -1,
            'suppress_filters' => false,
        ];
    }
}