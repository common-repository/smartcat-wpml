<?php

namespace Smartcat\Includes\Services\Interfaces;

interface MetadataDatabaseInterface
{
    public function getPostMetadata(int $postId, string $key = '', bool $isSingle = false);

    public function getCustomFieldKeys(): array;

    public function getCustomFields(): array;
}