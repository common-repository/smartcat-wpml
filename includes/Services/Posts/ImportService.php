<?php

namespace Smartcat\Includes\Services\Posts;

use Smartcat\Includes\Requests\ImportPostsRequest;
use Smartcat\Includes\Services\Elementor\Document;
use Smartcat\Includes\Services\Interfaces\CategoriesDatabaseInterface;
use Smartcat\Includes\Services\Interfaces\PostsDatabaseInterface;
use Smartcat\Includes\Services\Interfaces\PostTypeInterface;
use Smartcat\Includes\Services\Interfaces\WpmlInterface;
use Smartcat\Includes\Services\Interfaces\MetadataServiceInterface;

class ImportService
{
    /**
     * @var WpmlInterface
     */
    private $wpmlService;

    /**
     * @var PostsDatabaseInterface
     */
    private $postsDatabase;

    /** @var CategoriesDatabaseInterface */
    private $categoriesDatabase;

    /** @var PostTypeInterface */
    private $postTypesService;

    /**
     * @var MetadataServiceInterface
     */
    private $metadataService;

    public function __construct(
        WpmlInterface               $wpmlService,
        PostsDatabaseInterface      $postsDatabase,
        CategoriesDatabaseInterface $categoriesDatabase,
        PostTypeInterface           $postTypeService,
        MetadataServiceInterface $metadataService
    )
    {
        $this->wpmlService = $wpmlService;
        $this->postsDatabase = $postsDatabase;
        $this->categoriesDatabase = $categoriesDatabase;
        $this->postTypesService = $postTypeService;
        $this->metadataService = $metadataService;
    }

    public function import(ImportPostsRequest $request)
    {
        if (!$request->isIdsEmpty()) {
            $posts = [];
            foreach ($request->getIds() as $id) {
                $targetPostIds = $this->wpmlService->getIdsBySourceElements($id, $request->getTargetLanguages());
                foreach ($targetPostIds as $targetPostId) {
                    $posts[] = get_post($targetPostId);
                }
            }
        } else {
            $postIds = $this->wpmlService->getTranslatedPostIds(
                $this->postTypesService->getTranslatableTypes(),
                $request->getTargetLanguages()
            );

            $posts = [];
            foreach ($postIds as $postId) {
                $p = get_post($postId);
                if (empty($p)) {
                    continue;
                }
                $posts[] = get_post($postId);
            }
        }

        return $this->map($posts, $request->getSourceLang(), $request->getMetaDataKeys());
    }

    private function getArgs(): array
    {
        return [
            'post_type' => $this->postTypesService->getTranslatableTypes(),
            'numberposts' => -1,
            'suppress_filters' => false,
        ];
    }

    private function map(array $posts, string $sourceLang, $metaDataKeys): array
    {
        return array_map(function ($post) use ($sourceLang, $metaDataKeys) {
            $trid = $this->wpmlService->getTrid($post->ID, "post_$post->post_type");
            $translations = $this->wpmlService->getTranslations($trid, "post_$post->post_type");
            $sourcePost = get_post($translations[$sourceLang]->element_id) ?? $post;

            $isElementorDocument = false;
            $elementorData = [];

            if (is_elementor_installed()) {
                $elementorDocument = new Document($post->ID);
                $isElementorDocument = $elementorDocument->isBuiltWithElementor();
                $elementorData = $elementorDocument->getData();
            }

            return [
                'id' => $sourcePost->ID,
                'targetPostId' => $post->ID,
                'title' => $post->post_title,
                'postName' => $this->postsDatabase->normalizePostName($sourcePost->ID, $sourcePost->post_name),
                'slug' => urldecode($sourcePost->post_name),
                'content' => "", // $post->post_content,
                'blocks' => $isElementorDocument ? $elementorData : BlocksService::parseContentBlocks($post->post_content),
                'lang' => $this->wpmlService->getPostLocale($post->ID),
                'type' => $post->post_type,
                'categories' => $this->postsDatabase->getCategoriesIds($post->ID),
                'metadata' => $this->metadataService->getFields($post->ID, $metaDataKeys)
            ];
        }, $posts);
    }
}