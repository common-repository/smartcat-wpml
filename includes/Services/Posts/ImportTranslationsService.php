<?php

namespace Smartcat\Includes\Services\Posts;

use Smartcat\Includes\Services\Elementor\Document;
use Smartcat\Includes\Services\Interfaces\PostsDatabaseInterface;
use Smartcat\Includes\Services\Interfaces\MetadataServiceInterface;
use Smartcat\Includes\Services\Interfaces\WpmlInterface;

class ImportTranslationsService
{
    /**
     * @var WpmlInterface
     */
    private $wpmlService;

    /**
     * @var MetadataServiceInterface
     */
    private $metadataService;

    /**
     * @var PostsDatabaseInterface
     */
    private $postsDatabase;

    public function __construct(
        WpmlInterface            $wpmlService,
        MetadataServiceInterface $metadataService,
        PostsDatabaseInterface   $postsDatabase
    )
    {
        $this->wpmlService = $wpmlService;
        $this->metadataService = $metadataService;
        $this->postsDatabase = $postsDatabase;
    }

    public function import($originalPostId, $lang, $data)
    {
        $postType = get_post_type($originalPostId);

        $translatedPostId = $this->wpmlService->getTargetElementId(
            $originalPostId,
            $lang,
            "post_$postType"
        );

        $translatedData = $this->mapTranslatedData($data, $originalPostId);

        if (!empty($translatedPostId)) {
            smartcat_logger()->info('Post will be updated', [
                'translated_post_id' => $translatedPostId,
                'original_post_id' => $originalPostId,
                'translated_data' => $translatedData,
            ]);
            $this->updatePost($originalPostId, $translatedPostId, $translatedData);
            return $translatedPostId;
        } else {
            smartcat_logger()->info('Post will be added', [
                'original_post_id' => $originalPostId,
                'translated_data' => $translatedData,
            ]);
            return $this->addPost($originalPostId, $translatedData, $lang, $postType);
        }
    }

    private function updatePost($originalPostId, int $postId, array $postData)
    {
        $originalPost = get_post($originalPostId);

        if (is_elementor_installed()) {
            $mayBeElementorOriginalPost = new Document($originalPost->ID);

            if ($mayBeElementorOriginalPost->isBuiltWithElementor()) {
                // updating target post data of original post
                $mayBeElementorTargetPost = new Document($postId);
                $mayBeElementorTargetPost->save(
                    $mayBeElementorOriginalPost->getElements(true)
                );

                $mayBeElementorTargetPost = new Document($postId);
                $mayBeElementorTargetPost->updateElementsData($postData['elementor']);
            } else {
                $this->postsDatabase->updatePostContent($postId, $postData['content']);
            }
        } else {
            $this->postsDatabase->updatePostContent($postId, $postData['content']);
        }

        wp_update_post([
            'ID' => $postId,
            'post_title' => $postData['title'],
            'post_status' => 'draft'
        ]);

        $this->metadataService->addMetaDataToPost($postId, $postData['metadata']);
    }

    private function addPost($originalPostId, array $postData, $targetLanguage, $postType)
    {
        $originalPost = get_post($originalPostId);
        $id = smartcat_wpml()->makeDuplicate($originalPostId, $targetLanguage);

        if (is_elementor_installed()) {
            $mayBeElementorPost = new Document($originalPost->ID);

            if ($mayBeElementorPost->isBuiltWithElementor()) {
                // updating target post data of original post
                (new Document($id))->save(
                    $mayBeElementorPost->getElements(true)
                );

                $targetPost = new Document($id);
                $targetPost->updateElementsData($postData['elementor']);
            } else {
                $this->postsDatabase->updatePostContent($id, $postData['content']);
            }
        } else {
            $this->postsDatabase->updatePostContent($id, $postData['content']);
        }

        smartcat_logger()->info('Created new post', [
            'created_post_id' => $id,
        ]);

        wp_update_post([
            'ID' => $id,
            'post_title' => $postData['title'],
        ]);

        $this->metadataService->addMetaDataToPost($id, $postData['metadata']);

        return $id;
    }

    private function mapTranslatedData($data, $originalPostId): array
    {
        $originalPost = get_post($originalPostId);

        $translatedData = [
            'title' => $this->getValue($data, 'post_title'),
        ];

        $blocks = $this->findBlocks($data);
        $blocks = $this->mapBlocks($blocks);

        if (sc_built_with_bakery($originalPostId)) {
            $blocks = array_map(function ($block) {
                $block['content'] = sc_bakery_builder()->encode($block['content']);
                return $block;
            }, $blocks);
        }

        $translatedData['content'] = BlocksService::applyTranslatedBlocks(
            $originalPost->post_content,
            $blocks
        );

        $metadata = $this->findMetaData($data);
        $metadata = $this->mapMetaData($metadata);

        $translatedData['metadata'] = $metadata;

        $elementorData = $this->findElementorData($data);
        $elementorData = $this->mapElementorData($elementorData);

        $translatedData['elementor'] = array_values($elementorData);

        return $translatedData;
    }

    private function mapMetaData($data): array
    {
        $metadata = [];
        foreach ($data as $item) {
            $metadata[$item['id']] = [
                'content' => $item['translation']
            ];
        }
        return $metadata;
    }

    private function mapElementorData($data): array
    {
        return array_map(function ($item) {
            return [
                'key' => $item['id'],
                'context' => 'elementor',
                'content' => $item['translation'],
            ];
        }, $data);
    }

    private function findMetaData($data): array
    {
        return array_filter($data, function ($item) {
            return $item['properties']['context'] === 'metadata';
        });
    }

    private function findElementorData($data): array
    {
        return array_filter($data, function ($item) {
            return $item['properties']['context'] === 'elementor';
        });
    }

    private function mapBlocks($data): array
    {
        return array_map(function ($item) {
            return [
                'key' => $item['id'],
                'content' => $item['translation'],
            ];
        }, $data);
    }

    private function findBlocks($data): array
    {
        return array_filter($data, function ($item) {
            return $item['properties']['context'] === 'block';
        });
    }

    private function getValue($data, $key)
    {
        $filteredData = array_filter($data, function ($item) use ($key) {
            return $item['id'] === $key;
        });

        $item = array_shift($filteredData);

        return $item['translation'];
    }

    private function handleCustomFields($originalPostId, int $postId, array $postData)
    {
        $this->metadataService->updateMetaDataByOriginalPost($originalPostId, $postId);
        $this->metadataService->addMetaDataToPost($postId, $postData['metadata']);
    }
}