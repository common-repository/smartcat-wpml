<?php

namespace Smartcat\Includes\Services\Mocks;

use Smartcat\Includes\Services\API\Contracts\HubClientInterface;
use Smartcat\Includes\Services\Mocks\Models\SmartcatProject;
use Smartcat\Includes\Services\Mocks\Models\SmartcatDocument;
use Ramsey\Uuid\Uuid;

class SmartcatHubClientMock implements HubClientInterface
{
    private SmartcatProject $project;

    /** @var SmartcatDocument[] */
    private array $documents = [];

    public function createIntegration($scAccountId, $scAccountKeyId, $projectId, $sourceLanguage, $targetLanguages, $url, $secret, $postIds = [], $wordPressMetadataKeys = [], $isPublicPosts = 'publish')
    {
        // TODO: Implement startIntegration() method.
    }

    public function startIntegration($integrationId, $mode = 3)
    {
        // TODO: Implement startIntegration() method.
    }

    public function createProject(string $name, string $sourceLanguage, array $sourceLanguages, array $workflowStages): array
    {
        $this->project = (new SmartcatProject())
            ->setId(Uuid::uuid4()->toString())
            ->setName($name)
            ->setSourceLocale($sourceLanguage)
            ->setTargetLocales($sourceLanguages);

        return [
            'id' => $this->project->getId(),
            'name' => $this->project->getName(),
        ];
    }

    public function importTranslations($postName, $postId, $projectId, $sourceLang, $targetLang, $items): array
    {
        $id = uniqid() . '_' . $targetLang;

        $this->documents[] = (new SmartcatDocument())
            ->setId($id)
            ->setPostName($postName)
            ->setPostId($postId)
            ->setProjectId($projectId)
            ->setSourceLocale($sourceLang)
            ->setTargetLocale($targetLang)
            ->setItems($items);

        return [
            'documentId' => $id
        ];
    }

    public function exportBegin($postId, $projectId, $targetLanguage)
    {
        // TODO: Implement exportBegin() method.
    }

    public function exportResult(array $exportInfo)
    {
        // TODO: Implement exportResult() method.
    }

    public function addCommentToDocument($documentId, $comment)
    {
        return true;
    }

    public function getProject($projectId, $sourceLanguage)
    {
        return (new SmartcatProject())
            ->setId($this->project->getId())
            ->setName($this->project->getName())
            ->setDocuments([]);
    }

    public function getProjectInstance(): SmartcatProject
    {
        return $this->project;
    }

    public function hasSmartcatDocumentId(string $id): bool
    {
        return count(
            array_filter($this->documents, function ($document) use ($id) {
                return $document->getId() === $id;
            })
        ) > 0;
    }

    public function hasPostWithLocale(int $postID, string $locale): bool
    {
        return count(
                array_filter($this->documents, function ($document) use ($postID, $locale) {
                    return $document->getPostId() === $postID
                        && $document->getTargetLocale() === $locale;
                })
            ) > 0;
    }
}