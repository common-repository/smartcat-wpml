<?php

namespace Smartcat\Includes\Controllers\Traits;

use Smartcat\Includes\Services\API\HubClient;
use Smartcat\Includes\Services\API\SmartcatClient;
use Smartcat\Includes\Services\App\ContentService;
use Smartcat\Includes\Services\App\DocumentsService;
use Smartcat\Includes\Services\App\SmartcatProjectFactory;
use Smartcat\Includes\Services\App\TranslationRequestService;
use Smartcat\Includes\Services\Elementor\ElementorService;
use Smartcat\Includes\Services\Metadata\MetadataService;
use Smartcat\Includes\Services\Posts\DatabaseService as PostsDatabase;

trait HasTranslationRequest
{
    /** @var TranslationRequestService */
    protected $translationRequest;

    public function initTranslationRequest()
    {
        try {
            $this->translationRequest = new TranslationRequestService(
                new ContentService(
                    new MetadataService(),
                    new PostsDatabase(),
                    new ElementorService()
                ),
                new SmartcatProjectFactory(
                    new HubClient(),
                    new SmartcatClient()
                ),
                new HubClient(),
                new SmartcatClient(),
                new DocumentsService()
            );
        } catch (\Throwable $exception) {
            sc_log()->error($exception->getMessage(), [
                'trace' => $exception->getTraceAsString()
            ]);
        }
    }
}