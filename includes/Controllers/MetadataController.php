<?php

namespace Smartcat\Includes\Controllers;

use Smartcat\Includes\Requests\MetadataListRequest;
use Smartcat\Includes\Services\Metadata\MetadataListService as MetadataService;
use Smartcat\Includes\Services\Metadata\MetadataDatabaseService;
use Smartcat\Includes\Services\Posts\DatabaseService as PostDatabaseService;
use WP_REST_Request;

class MetadataController
{
    /**
     * @var MetadataService
     */
    private $metadataService;

    public function __construct()
    {
        $this->metadataService = new MetadataService(
            new PostDatabaseService(),
            new MetadataDatabaseService()
        );
    }

    public function list(WP_REST_Request $request): array
    {
        return $this->metadataService->getMetadataFields(new MetadataListRequest($request));
    }
}