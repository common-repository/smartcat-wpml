<?php

namespace Smartcat\Includes\Services\Metadata;

use Smartcat\Includes\Requests\MetadataListRequest;
use Smartcat\Includes\Services\Interfaces\MetadataDatabaseInterface;
use Smartcat\Includes\Services\Interfaces\PostsDatabaseInterface;

class MetadataListService
{
    private $postsDatabaseService;

    private $metadataDatabaseService;

    public function __construct(
        PostsDatabaseInterface    $postsDatabaseService,
        MetadataDatabaseInterface $metadataDatabaseService
    )
    {
        $this->postsDatabaseService = $postsDatabaseService;
        $this->metadataDatabaseService = $metadataDatabaseService;
    }

    public function getMetadataFields(MetadataListRequest $request): array
    {
        $customFields = $this->metadataDatabaseService->getCustomFields();

        return array_map(function ($field) {
            return [
                'key' => $field,
                'displayed' => $field,
            ];
        }, $customFields);
    }
}