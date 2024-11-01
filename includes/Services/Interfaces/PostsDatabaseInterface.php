<?php

namespace Smartcat\Includes\Services\Interfaces;

interface PostsDatabaseInterface
{
    public function getPosts(array $args = []): array;

    public function normalizePostName(int $postId, string $originalPostName): string;

    public function getCategoriesIds(int $postId): array;

    public function insertPost(array $postData): int;

    public function updatePostContent(int $postId, $content);
}