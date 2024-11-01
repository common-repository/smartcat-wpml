<?php

namespace Smartcat\Includes\Services\App;

use phpDocumentor\Reflection\Types\This;
use Smartcat\Includes\Services\API\Contracts\HubClientInterface;
use Smartcat\Includes\Services\API\Contracts\SmartcatClientInterface;
use Smartcat\Includes\Services\API\Models\Project;
use Smartcat\Includes\Services\App\Includes\TranslationRequestCreator;
use Smartcat\Includes\Services\App\Includes\DocumentsQueryBuilder;
use Smartcat\Includes\Services\App\Includes\TranslationRequestFinder;
use Smartcat\Includes\Services\App\Includes\TranslationRequestState;
use Smartcat\Includes\Services\App\Models\Document;
use Smartcat\Includes\Services\App\Models\TranslationRequest;
use Smartcat\Includes\Services\Errors\SmartcatWpError;

class TranslationRequestService
{
    use TranslationRequestState, TranslationRequestCreator, DocumentsQueryBuilder, TranslationRequestFinder;

    /** @var SmartcatProjectFactory */
    private $projectCreator;

    /** @var HubClientInterface */
    private $hubClient;

    /** @var SmartcatClientInterface */
    private $smartcatClient;

    /** @var ContentService */
    private $contentService;

    /** @var DocumentsService */
    private $documentsService;

    public function __construct(
        ContentService          $contentService,
        SmartcatProjectFactory  $projectCreator,
        HubClientInterface      $hubClient,
        SmartcatClientInterface $smartcatClient,
        DocumentsService        $documentsService
    )
    {
        $this->projectCreator = $projectCreator;
        $this->hubClient = $hubClient;
        $this->smartcatClient = $smartcatClient;
        $this->documentsService = $documentsService;
        $this->contentService = $contentService;
    }

    /**
     * @throws \Exception
     */
    public function create(): string
    {
        sc_log()->info('Started creating a translation request', [
            'posts' => $this->getPostIds(),
            'sourceLocale' => $this->sourceLocale,
            'targetLocales' => $this->targetLocales,
            'smartcatProjectId' => $this->smartcatProjectId,
            'smartcatProjectDeadline' => $this->deadline,
            'smartcatDocumentsComment' => $this->comment,
            'smartcatWorkflowStage' => $this->workflowStage
        ]);

        if (!$this->hasProject()) {
            sc_log()->info('Will created new Smartcat project');
            $this->createSmartcatProject();
        } else {
            $this->fetchProject();
        }

        smartcat_hub_client()
            ->sendMixpanelEvent('WPAppCreatedTranslationRequest', true, [
                'is_multi_posts' => count($this->getPostIds()) > 1,
                'translation_request_id' => $this->uuid(),
                'deadline' => $this->hasDeadline() ? $this->deadline : 'none',
                'comment' => $this->hasComment() ? $this->comment : 'none',
                'source_locale' => $this->sourceLocale,
                'target_locales' => $this->targetLocales,
                'workflow_stages' => $this->workflowStage,
                'smartcat_project_id' => $this->smartcatProjectId,
                'is_new_smartcat_project' => !$this->hasProject(),
                'posts' => $this->getPostIds()
            ]);

        $this->updateDeadline();

        $this->sendContentToSmartcat();

        $this->addComment();

        return $this->uuid();
    }

    public function exportTranslations($postIds, $translationRequestId = NULL)
    {
        $this->setPostIds($postIds);
        $this->setTranslationRequestId($translationRequestId);

        $this->tryInitTranslationRequest();

        $this->checkTranslationRequestProject();

        $postIds = !$this->hasPostIds()
            ? $this->tr->postIds()
            : $this->getPostIds();

        sc_log()->info('Started downloading translations from Smartcat', [
            'postIds' => $postIds,
            'translationRequestId' => $translationRequestId,
        ]);

        foreach ($postIds as $postId) {
            foreach ($this->tr->getPostTargetLocales($postId) as $locale) {
                $this->exportContentFromSmartcat($postId, $locale, $this->tr->smartcatProjectId());
            }
        }

        sc_log()->info('Export translations is completed');
    }

    public function updateSourceContent($postId, $locale, $translationRequestId = NULL)
    {
        $this->setTranslationRequestId($translationRequestId);

        $this->tryInitTranslationRequest();

        $this->checkTranslationRequestProject();

        $sourceLocale = $this->tr->sourceLocale();

        sc_log()->info("Started updating post ($postId) source content", [
            'postId' => $postId,
            'translationRequestId' => $translationRequestId,
            'sourceLocale' => $sourceLocale
        ]);

        $this->sendPostToSmartcat(
            $postId, $sourceLocale, $locale,
            $this->tr->smartcatProjectId(),
            $this->tr->id(), true
        );

        sc_log()->info('Updating source content is completed');
    }

    public function addLanguage($postId, $translationRequestId, $locale)
    {
        $this->setTranslationRequestId($translationRequestId);

        $this->tryInitTranslationRequest();

        $this->checkTranslationRequestProject();

        sc_log()->info("Adding new language ($locale) to post ($postId)", [
            'postId' => $postId,
            'translationRequestId' => $translationRequestId,
            'locale' => $locale
        ]);

        smartcat_hub_client()
            ->sendMixpanelEvent('WPAppAddedNewLanguageToPost', true, [
                'translation_request_id' => $translationRequestId,
                'post_id' => $postId,
                'target_locale' => $locale
            ]);

        $this->sendPostToSmartcat(
            $postId, $this->tr->sourceLocale(), $locale,
            $this->tr->smartcatProjectId(), $this->tr->id()
        );
    }

    public function removeLanguage($postId, $locale)
    {
        $this->documentsService->removePostLanguage($postId, $locale);

        sc_log()->info("Removed language ($locale) from post ($postId)");

        smartcat_hub_client()
            ->sendMixpanelEvent('WPAppRemovedLanguageFromPost', true, [
                'post_id' => $postId,
                'target_locale' => $locale
            ]);
    }

    public function remove($translationRequestId)
    {
        $this->documentsService->removeAll($translationRequestId);

        sc_log()->info("Removed translation request ($translationRequestId)");

        smartcat_hub_client()
            ->sendMixpanelEvent('WPAppRemovedTranslationRequest', true, [
                'translation_request_id' => $translationRequestId,
            ]);
    }

    public function removePost($postId, $translationRequestId)
    {
        $this->documentsService->removePost($postId, $translationRequestId);

        sc_log()->info("Removed post ($postId) from translation request ($translationRequestId)");

        smartcat_hub_client()
            ->sendMixpanelEvent('WPAppRemovedPostFromTranslationRequest', true, [
                'translation_request_id' => $translationRequestId,
                'post_id' => $postId,
            ]);
    }

    public function find($translationRequestId)
    {
        $this->setTranslationRequestId($translationRequestId);

        $this->tryInitTranslationRequest();

        return $this->translationRequest();
    }

    public function list($limit = 20, $offset = 0, $search = NULL, $orderBy = NULL, $order = 'DESC', $filters = [])
    {
        $translationRequests = [];

        $ids = $this->db()->get_col(
            $this->buildSqlQuery($limit, $offset, $search, $orderBy, $order, $filters)
        );

        foreach ($ids as $index => $translationRequestId) {
            $this->setTranslationRequestId($translationRequestId);
            $this->tryInitTranslationRequest();

            // $projectId = $this->tr->smartcatProjectId();

            // $smartcatProject = $this->hubClient->getProject($projectId, $this->tr->sourceLocale());
            $smartcatProject = [];

            foreach ($this->tr->postIds() as $postId) {
                $postExists = get_post($postId);

                if (empty($postExists)) {
                    $this->removePost($postId, $this->tr->id());
                }
            }

            if (!is_wp_error($smartcatProject)) {
                // $this->updateDocumentsProgress();
            }

            $translationRequests[] = [
                'index' => $index + 1,
                'id' => $this->tr->id(),
                'name' => $this->tr->name(),
                'status' => [
                    'type' => $this->tr->status()->getType(),
                    'progress' => $this->tr->status()->getProgress()
                ],
                'posts' => $this->tr->postsMinifiedList(),
                'comment' => $this->tr->comment(),
                'project' => [
                    'isError' => false,
                    'id' => $this->tr->smartcatProjectId(),
                    'name' => '---',
                    'deadline' => null
                ],
                'languages' => [
                    'source' => $this->tr->sourceLanguageName(),
                    'target' => $this->tr->targetLanguageNames()
                ]
            ];
        }

        return [
            'items' => $translationRequests,
        ];
    }

    public function totalRequests($search = NULL, $orderBy = NULL, $order = 'DESC', $filters = []): int
    {
        return count(
            $this->db()->get_col(
                $this->buildSqlQuery(null, null, $search, $orderBy, $order, $filters)
            )
        );
    }

    /**
     * @param $translationRequestId
     * @param SmartcatWpError|null|Project $smartcatProject
     * @return void
     */
    public function updateDocumentsProgress($translationRequestId = null, $smartcatProject = null)
    {
        if (is_null($translationRequestId) && !isset($this->tr)) {
            return;
        }

        if (!is_null($translationRequestId)) {
            $this->setTranslationRequestId($translationRequestId);
            $this->tryInitTranslationRequest();
        }

        $project = $smartcatProject ?? $this->hubClient->getProject(
            $this->tr->smartcatProjectId(),
            $this->tr->sourceLocale()
        );

        if (is_wp_error($project)) {
            return;
        }

        foreach ($this->tr->documents() as $document) {
            $smartcatDocument = array_filter($project->getDocuments(), function ($d) use ($document) {
                return $d['id'] === $document->getSmartcatDocumentId();
            });

            if (empty($smartcatDocument)) {
                continue;
            }

            $smartcatDocument = array_shift($smartcatDocument);

            $workflowStages = $smartcatDocument['workflowStages'];

            $maxProgress = count($workflowStages) * 100;
            $totalProgress = 0;

            foreach ($workflowStages as $workflowStage) {
                $totalProgress += $workflowStage['progress'];
            }

            $progress = ($totalProgress / $maxProgress) * 100;
            $document->setTranslationProgress($progress);
            $this->documentsService->save($document);
        }
    }

    /**
     * @throws \Exception
     */
    public function info($translationRequestId): array
    {
        $this->find($translationRequestId);

        $this->fetchProject(
            $this->tr->smartcatProjectId(),
            $this->tr->sourceLocale()
        );

        if (is_wp_error($this->smartcatProject)) {
            throw new \Exception($this->smartcatProject->get_error_message());
        }

        $this->updateDocumentsProgress(null, $this->smartcatProject);

        // refresh translation request information
        $this->find($translationRequestId);

        return [
            'status' => [
                'type' => $this->tr->status()->getType(),
                'progress' => $this->tr->status()->getProgress()
            ],
            'project' => [
                'id' => $this->smartcatProject->getId(),
                'name' => $this->smartcatProject->getName(),
                'deadline' => $this->smartcatProject->getDeadline()
            ]
        ];
    }

    public function getTranslationsByPostAndLocale($translationRequestId, $postId, $locale)
    {
        $this->setTranslationRequestId($translationRequestId);

        $this->tryInitTranslationRequest();

        $this->checkTranslationRequestProject();

        $this->exportContentFromSmartcat($postId, $locale, $this->tr->smartcatProjectId());
    }

    /**
     * @throws \Exception
     */
    private function createSmartcatProject()
    {
        $this->smartcatProjectId = $this->projectCreator
            ->setPostIds($this->getPostIds())
            ->setSourceLocale($this->sourceLocale)
            ->setTargetLocales($this->targetLocales)
            ->setDeadline($this->deadline)
            ->setWorkflowStage($this->workflowStage)
            ->create();

        $this->fetchProject();
    }

    private function updateDeadline()
    {
        if ($this->hasDeadline()) {
            sc_log()->info('The Smartcat project will have its deadline updated');
            $this->smartcatClient->updateProjectDeadline(
                $this->smartcatProject->getId(),
                $this->smartcatProject->getName(),
                $this->deadline
            );
        }
    }

    private function fetchProject($smartcatProjectId = null, $sourceLocale = null)
    {
        $this->setSmartcatProject(
            $this->hubClient->getProject(
                $smartcatProjectId ?? $this->smartcatProjectId,
                $sourceLocale ?? $this->sourceLocale
            )
        );
    }

    /**
     * @throws \Exception
     */
    private function sendContentToSmartcat()
    {
        sc_log()->info('Started sending posts to Smartcat');

        foreach ($this->getPostIds() as $postId) {
            foreach ($this->targetLocales as $targetLocale) {
                $response = $this->sendPostToSmartcat(
                    $postId,
                    $this->sourceLocale,
                    $targetLocale,
                    $this->smartcatProject->getId()
                );

                if (is_wp_error($response)) {
                    throw new \Exception("An error occurred while importing post with id $postId (with $targetLocale locale) into Smartcat");
                }
            }
        }
    }

    /**
     * @param int $postID
     * @param string $sourceLocale
     * @param string $targetLocale
     * @param string $smartcatProjectId
     * @param null $translationRequestId
     * @param bool $isUpdating
     * @return SmartcatWpError|array
     */
    private function sendPostToSmartcat(int $postID, string $sourceLocale, string $targetLocale, string $smartcatProjectId, $translationRequestId = null, bool $isUpdating = false)
    {
        $post = get_post($postID);

        if (!$isUpdating) {
            sc_log()->info("Submitting a post to a Smartcat project. $post->post_title ($postID) ($sourceLocale -> $targetLocale)");
        } else {
            sc_log()->info("Updating the post in the Smartcat project. $post->post_title ($postID) ($sourceLocale -> $targetLocale)");
        }

        $document = $this->documentsService
            ->findDocumentByPostAndLocale($postID, $targetLocale);

        $apiVersion = !is_null($document) ? $document->getApiVersion() : 'v2';

        $response = $this->hubClient->importTranslations(
            $post->post_title,
            $post->ID,
            $smartcatProjectId,
            $sourceLocale,
            $targetLocale,
            $this->contentService->parse($post, $apiVersion)
        );

        if (!is_wp_error($response) && !$isUpdating) {
            $document = (new Document())
                ->setTranslatedPostId(sc_wpml()->getTranslationId($postID, $targetLocale))
                ->setPostId($postID)
                ->setSmartcatDocumentId($response['documentId'])
                ->setSmartcatProjectId($smartcatProjectId)
                ->setLang($targetLocale)
                ->setTranslationProgress(0)
                ->setCreatedAt($this->createdAt())
                ->setTranslationRequestId($translationRequestId ?? $this->uuid())
                ->setComment($this->comment ?? null)
                ->setApiVersion('v2');

            $this->documentsService->create($document);

            $this->documents[] = $document;
        }

        $mixpanelData = [
            'translation_request_id' => $translationRequestId ?? $this->uuid(),
            'is_updating' => $isUpdating,
            'post_id' => $postID,
            'smartcat_project_id' => $smartcatProjectId,
            'source_locale' => $sourceLocale,
            'target_locale' => $targetLocale,
            'api_version' => $apiVersion,
        ];

        $mixpanelEventId = $isUpdating ? 'WPAppUpdateSourceContent' : 'WPAppSendToSmartcat';

        if (!is_wp_error($response)) {
            smartcat_hub_client()
                ->sendMixpanelEvent($mixpanelEventId, true, $mixpanelData);
        } else {
            smartcat_hub_client()
                ->sendMixpanelEvent($mixpanelEventId, false, $mixpanelData);
        }

        return $response;
    }

    private function addComment()
    {
        if ($this->hasComment()) {
            $ignore = [];

            sc_log()->info('A comment will be added to the projects Smartcat documents.');
            foreach ($this->documents as $document) {
                if (!in_array($document->getCleanSmartcatDocumentId(), $ignore)) {
                    $this->hubClient->addCommentToDocument(
                        $document->getCleanSmartcatDocumentId(), $this->comment
                    );

                    $ignore[] = $document->getCleanSmartcatDocumentId();
                }
            }
        }
    }

    private function tryInitTranslationRequest()
    {
        $documents = [];

        if (!empty($this->translationRequestId)) {
            $documents = $this->documentsService
                ->whereTranslationRequestEquals($this->translationRequestId)
                ->fetch();
        }

        $this->setTr((new TranslationRequest())
            ->setId($this->translationRequestId)
            ->setDocuments($documents));

        // $this->updateDocumentsProgress();
    }

    private function exportContentFromSmartcat($postId, $locale, $projectId)
    {
        sc_log()->info("Export translated content for post - $postId ($locale)", [
            'postId' => $postId,
            'locale' => $locale,
            'smartcatProjectId' => $projectId
        ]);

        $document = $this->documentsService->findDocumentByPostAndLocale($postId, $locale);
        $apiVersion = !is_null($document) ? $document->getApiVersion() : 'v1';

        /** @var array|SmartcatWpError $exportBegin */
        $exportBegin = $this->hubClient->exportBegin($postId, $projectId, $locale);

        if (is_wp_error($exportBegin)) {
            throw new \Exception($exportBegin->get_error_message());
        }

        if ($exportBegin['exportInfo']['exportStatus'] === 4) {
            sc_log()->error("An error occurred during export begin ($locale)", $exportBegin);
            return;
        }

        $hasExportingErrors = false;

        while (true) {
            /** @var array|SmartcatWpError $exportResult */
            $exportResult = $this->hubClient->exportResult($exportBegin['exportInfo']);

            if (is_wp_error($exportResult)) {
                // TODO: add notification about problems of exporting
                $hasExportingErrors = true;
                sc_log()->error("Error getting translated content for post $postId ($locale)");
                break;
            }

            if (!empty($exportResult['items'])) {
                $translatedPostId = $this->contentService->import(
                    $postId,
                    $locale,
                    $exportResult['items'],
                    $exportResult['meta'],
                    $apiVersion
                );
                $documentId = $exportResult['smartcatDocumentId'];
                $this->documentsService->updateTranslatedPostId($documentId, $translatedPostId);
                break;
            }
        }

        smartcat_hub_client()
            ->sendMixpanelEvent('WPAppGetTranslation', !$hasExportingErrors, [
                'translation_request_id' => $this->getTranslationRequestId(),
                'post_id' => $postId,
                'smartcat_project_id' => $projectId,
                'target_locale' => $locale,
                'api_version' => $apiVersion
            ]);
    }

    private function getTranslationRequestIds(): array
    {
        return $this->distinct('translation_request_id');
    }

    /**
     * @throws \Exception
     */
    private function checkTranslationRequestProject(): void
    {
        if($this->tr->isInvalidProject()){
            throw new \Exception('Project translation request is invalid');
        } else {
            $project = $this->hubClient->getProject($this->tr->smartcatProjectId(), $this->tr->sourceLocale());

            if (is_wp_error($project) ) {
                foreach ($this->tr->documents() as $document){
                    $document->setIsInvalidProject(true);

                    $this->documentsService->save($document);
                }

                throw new \Exception('Project translation request is invalid');
            }
        }
    }
}
