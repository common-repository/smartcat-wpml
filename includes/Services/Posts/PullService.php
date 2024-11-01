<?php

namespace Smartcat\Includes\Services\Posts;

use Smartcat\Includes\Requests\PullPostsRequest;
use Smartcat\Includes\Services\Elementor\Document;
use Smartcat\Includes\Services\Interfaces\PostsDatabaseInterface;
use Smartcat\Includes\Services\Interfaces\PostTypeInterface;
use Smartcat\Includes\Services\Interfaces\MetadataServiceInterface;
use Smartcat\Includes\Services\Interfaces\WpmlInterface;
use Smartcat\Includes\Services\Tools\Logger;

class PullService
{
    /**
     * @var PostsDatabaseInterface
     */
    private $postsDatabaseService;

    /**
     * @var WpmlInterface
     */
    private $smartcatWpmlService;

    /**
     * @var MetadataServiceInterface
     */
    private $metadataService;

    /** @var PostTypeInterface */
    private $postTypesService;

    public function __construct(
        PostsDatabaseInterface   $postsDatabaseService,
        WpmlInterface            $smartcatWpmlService,
        MetadataServiceInterface $metadataService,
        PostTypeInterface        $postTypeService
    )
    {
        $this->postsDatabaseService = $postsDatabaseService;
        $this->smartcatWpmlService = $smartcatWpmlService;
        $this->metadataService = $metadataService;
        $this->postTypesService = $postTypeService;
    }

    /**
     * @param PullPostsRequest $request
     * @return array
     * @throws \Exception
     */
    public function getPosts(PullPostsRequest $request): array
    {
        $this->smartcatWpmlService->switchLang($request->getLang());
        $args = $this->getArgs($request);
        $posts = $this->postsDatabaseService->getPosts($args);
        return $this->map($posts, $request->getMeta());
    }

    /**
     * @param PullPostsRequest $request
     * @return array
     */
    private function getArgs(PullPostsRequest $request): array
    {
        $args = [
            'post_type' => $this->postTypesService->getTranslatableTypes(),
            'numberposts' => $request->getLimit(),
            'offset' => $request->getOffset(),
            'suppress_filters' => false,
        ];

        if (!empty($request->getDate()) && empty($request->getIds())) {
            $args['date_query'] = [
                'column' => 'post_modified',
                'after' => $request->getDate(),
                'before' => current_time('Y-m-d H:i:s')
            ];
        }

        if (!empty($request->getIds())) {
            $args['numberposts'] = -1;
            $args['offset'] = 0;
            $args['include'] = implode(',', $request->getIds());
        }

        return $args;
    }

    /**
     * @throws \Exception
     */
    private function map(array $posts, array $metaKeys = []): array
    {
        return array_map(function ($post) use ($metaKeys) {

            $isElementorDocument = false;
            $elementorData = [];

            if (is_elementor_installed()) {
                try {
                    $elementorDocument = new Document($post->ID);
                    $isElementorDocument = $elementorDocument->isBuiltWithElementor();
                    $elementorData = $elementorDocument->getData();
                } catch (\Throwable $exception) {
                    $isElementorDocument = false;
                    smartcat_logger()->warn('Error Elementor post. Content will be exported of Gutenberg editor.', [
                        'postId' => $post->ID,
                        'error' => $exception->getMessage(),
                        'stackTrace' => $exception->getTraceAsString()
                    ]);
                }
            }

            $blocks = $isElementorDocument
                ? $elementorData
                : BlocksService::parseContentBlocks($post->post_content);

            if (!$isElementorDocument && sc_built_with_bakery($post->ID)) {
                $blocks = array_map(function ($block) {
                    $block['content'] = sc_bakery_builder()->decode($block['content']);
                    return $block;
                }, $blocks);
            }

            return [
                'id' => $post->ID,
                'title' => $post->post_title,
                'postName' => $this->postsDatabaseService->normalizePostName($post->ID, $post->post_name),
                'slug' => urldecode($post->post_name),
                'content' => "",
                'acf' => [],
                'blocks' => $blocks,
                'type' => $post->post_type,
                'categories' => $this->postsDatabaseService->getCategoriesIds($post->ID),
                'metadata' => $this->metadataService->getFields($post->ID, $metaKeys)
            ];
        }, $posts);
    }
}