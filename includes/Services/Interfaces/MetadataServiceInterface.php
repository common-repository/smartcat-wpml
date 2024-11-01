<?php

namespace Smartcat\Includes\Services\Interfaces;

interface MetadataServiceInterface
{
    public function getFields(int $postId, array $keys = []): array;

    public function updateMetaDataByOriginalPost(int $originalPostId, int $targetPostId);

    /**
     * @param int $postId
     * @param array $metaData
     * @return void
     */
    public function addMetaDataToPost(int $postId, array $metaData);
}