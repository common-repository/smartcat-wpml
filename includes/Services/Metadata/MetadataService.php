<?php

namespace Smartcat\Includes\Services\Metadata;

use Smartcat\Includes\Services\Interfaces\MetadataServiceInterface;
use Smartcat\Includes\Services\Tools\JsonMagician;

class MetadataService implements MetadataServiceInterface
{
    /** @var MetadataDatabaseService */
    private $metadataDatabaseService;

    public function __construct()
    {
        $this->metadataDatabaseService = new MetadataDatabaseService();
    }

    public function getFields(int $postId, array $keys = []): array
    {
        $postMetaFields = $this->getPostMetaFields($postId);

        $fields = [];
        $magicFields = new JsonMagician($postMetaFields);

        if (!in_array('smartcat-no-metadata', $keys)) {
            if (is_array($keys) && empty($keys)) {
                $customFieldKeys = $this->metadataDatabaseService->getCustomFields();
                $fields = $magicFields->extract($customFieldKeys);
            } elseif (is_array($keys) && count($keys) > 0) {
                $fields = $magicFields->extract($keys);
            }
        }

        return $this->normalizeFields($fields);
    }

    public function updateMetaDataByOriginalPost(int $originalPostId, int $targetPostId)
    {
        $originalMetaData = $this->getPostMetaData($originalPostId);
        foreach ($originalMetaData as $key => $value) {
            update_post_meta($targetPostId, $key, $value);
        }
    }

    public function addMetaDataToPost(int $postId, array $metaData)
    {
        $mappedMetaData = [];

        foreach ($metaData as $key => $item) {
            if (is_string($item['content']) && !empty($item['content'])) {
                $mappedMetaData[$key] = $item['content'];
            }
        }

        $postMetaData = $this->getPostMetaData($postId);

        $filteredPostMetaData = [];

        foreach ($postMetaData as $key => $value) {
            if (!sc_str_contains($key, '_elementor')) {
                $filteredPostMetaData[$key] = $value;
            }
        }

        $postMetaData = $filteredPostMetaData;

        $jsonMagician = new JsonMagician($postMetaData);
        $jsonWithDots = $jsonMagician->getJson(true);
        foreach ($mappedMetaData as $key => $value) {
            $jsonWithDots[$key] = is_array($value) && empty($value) ? '' : $value;
        }
        $undotNewMetaData = $jsonMagician->undot($jsonWithDots);

        foreach ($undotNewMetaData as $key => $value) {
            update_post_meta($postId, $key, $value);
        }
    }

    private function getPostMetaData($postId): array
    {
        $metaData = [];
        $postMeta = get_post_meta($postId);
        foreach ($postMeta as $key => $value) {
            $metaData[$key] = get_post_meta($postId, $key, true);
        }
        return $metaData;
    }

    private function getPostMetaFields(int $postId): array
    {
        $metaData = [];
        $postMeta = get_post_meta($postId);
        foreach ($postMeta as $key => $value) {
            $metaData[$key] = get_post_meta($postId, $key, true);
        }
        return $metaData;
    }

    private function normalizeFields(array $fields): array
    {
        return array_map(function ($field) {
            return ['content' => $field];
        }, $fields);
    }
}