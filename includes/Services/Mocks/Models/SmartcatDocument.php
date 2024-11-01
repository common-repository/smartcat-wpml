<?php

namespace Smartcat\Includes\Services\Mocks\Models;

class SmartcatDocument
{
    private string $id;

    private string $postName;

    private int $postId;

    private string $projectId;

    private string $sourceLocale;

    private string $targetLocale;

    /** @var SmartcatDocumentItem[] */
    private array $items = [];

    /**
     * @param string $postName
     * @return SmartcatDocument
     */
    public function setPostName(string $postName): SmartcatDocument
    {
        $this->postName = $postName;
        return $this;
    }

    /**
     * @param int $postId
     * @return SmartcatDocument
     */
    public function setPostId(int $postId): SmartcatDocument
    {
        $this->postId = $postId;
        return $this;
    }

    /**
     * @param string $projectId
     * @return SmartcatDocument
     */
    public function setProjectId(string $projectId): SmartcatDocument
    {
        $this->projectId = $projectId;
        return $this;
    }

    /**
     * @param string $sourceLocale
     * @return SmartcatDocument
     */
    public function setSourceLocale(string $sourceLocale): SmartcatDocument
    {
        $this->sourceLocale = $sourceLocale;
        return $this;
    }

    /**
     * @param string $targetLocale
     * @return SmartcatDocument
     */
    public function setTargetLocale(string $targetLocale): SmartcatDocument
    {
        $this->targetLocale = $targetLocale;
        return $this;
    }

    /**
     * @param array $items
     * @return SmartcatDocument
     */
    public function setItems(array $items): SmartcatDocument
    {
        foreach ($items as $item) {
            $this->items[] = (new SmartcatDocumentItem())
                ->setId($item['id'])
                ->setSourceText($item['sourceText'])
                ->setFormat($item['format'])
                ->setProperties($item['properties'])
                ->setExistingTranslation($item['existingTranslation']);
        }
        return $this;
    }

    /**
     * @param string $id
     * @return SmartcatDocument
     */
    public function setId(string $id): SmartcatDocument
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getPostName(): string
    {
        return $this->postName;
    }

    /**
     * @return int
     */
    public function getPostId(): int
    {
        return $this->postId;
    }

    /**
     * @return string
     */
    public function getProjectId(): string
    {
        return $this->projectId;
    }

    /**
     * @return string
     */
    public function getSourceLocale(): string
    {
        return $this->sourceLocale;
    }

    /**
     * @return string
     */
    public function getTargetLocale(): string
    {
        return $this->targetLocale;
    }

    /**
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }
}