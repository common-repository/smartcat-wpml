<?php

namespace Smartcat\Includes\Services\App\Includes;

use Smartcat\Includes\Services\API\Models\Project;
use Smartcat\Includes\Services\App\Models\Document;
use Smartcat\Includes\Services\App\Models\TranslationRequest;

trait TranslationRequestState
{
    /** @var array */
    protected $postIds;

    /** @var string */
    protected $sourceLocale;

    /** @var array */
    protected $targetLocales;

    /** @var string|null */
    protected $smartcatProjectId;

    /** @var string|null */
    protected $deadline;

    /** @var string|null */
    protected $comment;

    /** @var string */
    protected $workflowStage = 'mt';

    /** @var Project */
    protected $smartcatProject;

    /** @var Document[] */
    protected $documents = [];

    /** @var string|null */
    protected $translationRequestId;

    /** @var null|TranslationRequest */
    protected $tr;

    /**
     * @param array|string $postIds
     * @return static
     */
    public function setPostIds($postIds)
    {
        $this->postIds = is_numeric($postIds)
            ? [$postIds]
            : sc_maybe_decode($postIds);

        return $this;
    }

    /**
     * @param string $sourceLocale
     * @return static
     */
    public function setSourceLocale(string $sourceLocale)
    {
        $this->sourceLocale = $sourceLocale;
        return $this;
    }

    /**
     * @param array $targetLocales
     * @return static
     */
    public function setTargetLocales(array $targetLocales)
    {
        $this->targetLocales = $targetLocales;
        return $this;
    }

    /**
     * @param string|null $smartcatProjectId
     * @return static
     */
    public function setSmartcatProjectId($smartcatProjectId)
    {
        $this->smartcatProjectId = $smartcatProjectId;
        return $this;
    }

    /**
     * @param string|null $deadline
     * @return static
     */
    public function setDeadline($deadline)
    {
        $this->deadline = $deadline;
        return $this;
    }

    /**
     * @param string|null $comment
     * @return static
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * @param string $workflowStage
     * @return static
     */
    public function setWorkflowStage(string $workflowStage)
    {
        $this->workflowStage = $workflowStage;
        return $this;
    }

    /**
     * @return array
     */
    public function getPostIds(): array
    {
        return $this->postIds;
    }

    public function getFirstPostId()
    {
        return $this->postIds[0];
    }

    public function totalPosts(): int
    {
        return count($this->postIds);
    }

    public function hasDeadline(): bool
    {
        return isset($this->deadline) && !empty($this->deadline);
    }

    /**
     * @param mixed $smartcatProject
     * @return static
     */
    public function setSmartcatProject($smartcatProject)
    {
        $this->smartcatProject = $smartcatProject;
        return $this;
    }

    public function hasPostIds(): bool
    {
        return isset($this->postIds) && !empty($this->postIds);
    }

    /**
     * @param string|null $translationRequestId
     * @return static
     */
    public function setTranslationRequestId($translationRequestId)
    {
        $this->translationRequestId = $translationRequestId;
        return $this;
    }

    /**
     * @param TranslationRequest|null $tr
     * @return static
     */
    public function setTr(TranslationRequest $tr)
    {
        $this->tr = $tr;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTranslationRequestId(): string
    {
        return $this->translationRequestId;
    }

    /**
     * @return TranslationRequest|null
     */
    public function translationRequest(): TranslationRequest
    {
        return $this->tr;
    }

    protected function hasProject(): bool
    {
        return isset($this->smartcatProjectId)
            && is_string($this->smartcatProjectId)
            && !empty($this->smartcatProjectId);
    }

    protected function hasComment(): bool
    {
        return isset($this->comment)
            && is_string($this->comment)
            && !empty($this->comment);
    }
}