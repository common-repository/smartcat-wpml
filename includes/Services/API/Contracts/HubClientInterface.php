<?php

namespace Smartcat\Includes\Services\API\Contracts;

use Smartcat\Includes\Services\API\Models\Project;
use Smartcat\Includes\Services\API\Models\TranslatedItem;
use Smartcat\Includes\Services\Errors\SmartcatWpError;

interface HubClientInterface
{
    public function createProject(string $name, string $sourceLanguage, array $sourceLanguages, array $workflowStages);

    public function importTranslations($postName, $postId, $projectId, $sourceLang, $targetLang, $content);

    public function exportBegin($postId, $projectId, $targetLanguage);

    /**
     * @param array $exportInfo
     * @return array|TranslatedItem[]|SmartcatWpError
     */
    public function exportResult(array $exportInfo);

    public function addCommentToDocument($documentId, $comment);

    /**
     * @param $projectId
     * @param $sourceLanguage
     * @return SmartcatWpError|Project
     */
    public function getProject($projectId, $sourceLanguage);

    public function sendMixpanelEvent($event, $status = null);
}