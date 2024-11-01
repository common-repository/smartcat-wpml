<?php

namespace Smartcat\Includes\Services\App\Models;

class Document
{
    /** @var int|null */
    private $translatedPostId;

    /** @var int */
    private $postId;

    /** @var string */
    private $smartcatDocumentId;

    /** @var string */
    private $smartcatProjectId;

    /** @var string */
    private $lang;

    /** @var int|float */
    private $translationProgress;

    /** @var string|null */
    private $createdAt;

    /** @var string */
    private $translationRequestId;

    /** @var string|null */
    private $comment;

    /** @var string */
    private $apiVersion;

    private $isExported;

    private $isImported;

    private $isInvalidProject;

    /**
     * @param int|null $translatedPostId
     * @return Document
     */
    public function setTranslatedPostId($translatedPostId): Document
    {
        $this->translatedPostId = $translatedPostId;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getTranslatedPostId()
    {
        return $this->translatedPostId;
    }

    /**
     * @param string $smartcatDocumentId
     * @return Document
     */
    public function setSmartcatDocumentId(string $smartcatDocumentId): Document
    {
        $this->smartcatDocumentId = $smartcatDocumentId;
        return $this;
    }

    /**
     * @return string
     */
    public function getSmartcatDocumentId(): string
    {
        return $this->smartcatDocumentId;
    }

    public function getCleanSmartcatDocumentId(): string
    {
        return explode('_', $this->smartcatDocumentId)[0];
    }

    /**
     * @param int $postId
     * @return Document
     */
    public function setPostId(int $postId): Document
    {
        $this->postId = $postId;
        return $this;
    }

    /**
     * @return int
     */
    public function getPostId(): int
    {
        return $this->postId;
    }

    /**
     * @param string $smartcatProjectId
     * @return Document
     */
    public function setSmartcatProjectId(string $smartcatProjectId): Document
    {
        $this->smartcatProjectId = $smartcatProjectId;
        return $this;
    }

    /**
     * @return string
     */
    public function getSmartcatProjectId(): string
    {
        return $this->smartcatProjectId;
    }

    /**
     * @param string $lang
     * @return Document
     */
    public function setLang(string $lang): Document
    {
        $this->lang = $lang;
        return $this;
    }

    /**
     * @return string
     */
    public function getLang(): string
    {
        return $this->lang;
    }

    /**
     * @param float|int $translationProgress
     * @return Document
     */
    public function setTranslationProgress($translationProgress)
    {
        $this->translationProgress = $translationProgress;
        return $this;
    }

    /**
     * @return float|int
     */
    public function getTranslationProgress()
    {
        return round($this->translationProgress, 2);
    }

    /**
     * @param string|null $createdAt
     * @return Document
     */
    public function setCreatedAt($createdAt): Document
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param string $translationRequestId
     * @return Document
     */
    public function setTranslationRequestId(string $translationRequestId): Document
    {
        $this->translationRequestId = $translationRequestId;
        return $this;
    }

    /**
     * @return string
     */
    public function getTranslationRequestId(): string
    {
        return $this->translationRequestId;
    }

    /**
     * @param string|null $comment
     * @return Document
     */
    public function setComment($comment): Document
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getComment()
    {
        return $this->comment;
    }

    public function getStoredData(): array
    {
        return [
            'translated_post_id' => $this->getTranslatedPostId(),
            'post_id' => $this->getPostId(),
            'smartcat_document_id' => $this->getSmartcatDocumentId(),
            'smartcat_project_id' => $this->getSmartcatProjectId(),
            'lang' => $this->getLang(),
            'translation_progress' => $this->getTranslationProgress(),
            'created_at' => $this->getCreatedAt(),
            'translation_request_id' => $this->getTranslationRequestId(),
            'comment' => $this->getComment(),
            'api_version' => $this->getApiVersion(),
            'is_exported' => $this->isExported ?? 0,
            'is_imported' => $this->isImported ?? 0,
            'is_invalid_project' => $this->isInvalidProject ?? 0,
        ];
    }

    /**
     * @param string $apiVersion
     * @return Document
     */
    public function setApiVersion(string $apiVersion = 'v1'): Document
    {
        $this->apiVersion = $apiVersion;
        return $this;
    }

    /**
     * @return string
     */
    public function getApiVersion(): string
    {
        return $this->apiVersion;
    }

    /**
     * @return bool
     */
    public function isExported(): bool
    {
        return $this->isExported == 1;
    }

    /**
     * @param mixed $isExported
     * @return Document
     */
    public function setIsExported($isExported): Document
    {
        $this->isExported = (int)$isExported;
        return $this;
    }

    /**
     * @return bool
     */
    public function isImported(): bool
    {
        return $this->isImported == 1;
    }

    /**
     * @param mixed $isImported
     * @return Document
     */
    public function setIsImported($isImported): Document
    {
        $this->isImported = (int)$isImported;
        return $this;
    }

    public function isInvalidProject(): bool
    {
        return $this->isInvalidProject === 1;
    }

    public function setIsInvalidProject($isInvalidProject): Document {
        $this->isInvalidProject = (int)$isInvalidProject;

        return $this;
    }
}
