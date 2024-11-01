<?php

namespace Smartcat\Includes\Services\Mocks\Models;

class SmartcatDocumentItem
{
    private string $id;

    private string $sourceText;

    private string $format;

    private array $properties;

    private string $existingTranslation;

    /**
     * @param string $id
     * @return SmartcatDocumentItem
     */
    public function setId(string $id): SmartcatDocumentItem
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @param string $sourceText
     * @return SmartcatDocumentItem
     */
    public function setSourceText(string $sourceText): SmartcatDocumentItem
    {
        $this->sourceText = $sourceText;
        return $this;
    }

    /**
     * @param string $format
     * @return SmartcatDocumentItem
     */
    public function setFormat(string $format): SmartcatDocumentItem
    {
        $this->format = $format;
        return $this;
    }

    /**
     * @param array $properties
     * @return SmartcatDocumentItem
     */
    public function setProperties(array $properties): SmartcatDocumentItem
    {
        $this->properties = $properties;
        return $this;
    }

    /**
     * @param string $existingTranslation
     * @return SmartcatDocumentItem
     */
    public function setExistingTranslation(string $existingTranslation): SmartcatDocumentItem
    {
        $this->existingTranslation = $existingTranslation;
        return $this;
    }

    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getSourceText(): string
    {
        return $this->sourceText;
    }

    /**
     * @return string
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * @return array
     */
    public function getProperties(): array
    {
        return $this->properties;
    }

    /**
     * @return string
     */
    public function getExistingTranslation(): string
    {
        return $this->existingTranslation;
    }
}